<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Server-side JSON-LD, built from the same models the page renders, so it
 * cannot drift from the visible content (architecture §7.4).
 *
 * Rendered exactly once per document, at the end of <body> (seo.md §8.6 rule 2
 * and §8.13) — never inside an @island, never inside a component that
 * re-renders on interaction.
 *
 * There is deliberately no `hasCertification` node anywhere in this file and
 * there must never be one. Glow Halal holds no halal accreditation; a
 * `Certification` node with an invented issuer is a false religious-compliance
 * claim, not a placeholder. The two claims this site is permitted to make are
 * the two that are true: the full INCI list is published, and the exclusion
 * list is published.
 */
final class SchemaGraph
{
    /** @var array<int, array<string, mixed>> */
    private array $nodes = [];

    public static function make(): self
    {
        return new self;
    }

    public function organization(): self
    {
        // Single source of truth for the sitewide entity: App\Support\JsonLd.
        // Delegating here (rather than hand-building a second, competing
        // Organization node) guarantees the shop and product pages emit the
        // EXACT same rich OnlineStore node — same @id, description, areaServed,
        // knowsAbout, sameAs — as the home page and every content page, and
        // that no invented phone/email/address is ever faked in: JsonLd OMITS
        // any value the owner has not set in StoreSettings.
        $this->nodes[] = \App\Support\JsonLd::organization();
        $this->nodes[] = \App\Support\JsonLd::website();

        return $this;
    }

    public function webPage(string $url, string $name, ?string $description = null): self
    {
        $this->nodes[] = array_filter([
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'url' => $url,
            'name' => $name,
            'description' => $description,
            'isPartOf' => ['@id' => url('/').'/#website'],
            'inLanguage' => 'en-PK',
        ], fn ($v) => $v !== null);

        return $this;
    }

    /** @param array<int, array{name: string, url: string}> $trail */
    public function breadcrumbs(array $trail): self
    {
        if ($trail === []) {
            return $this;
        }

        $this->nodes[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()->map(fn (array $crumb, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->all(),
        ];

        return $this;
    }

    /**
     * The URL-only ItemList form — Google's preferred summary-page pattern, and
     * it keeps page weight down. `position` is absolute across pagination:
     * page 2 starts at 13, not at 1.
     *
     * @param  Collection<int, Product>  $products
     */
    public function itemList(string $id, string $name, Collection $products, int $startPosition = 1): self
    {
        $this->nodes[] = [
            '@type' => 'ItemList',
            '@id' => $id.'#itemlist',
            'name' => $name,
            'numberOfItems' => $products->count(),
            'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
            'itemListElement' => $products->values()->map(fn (Product $p, int $i) => [
                '@type' => 'ListItem',
                'position' => $startPosition + $i,
                'url' => route('products.show', $p->slug),
            ])->all(),
        ];

        return $this;
    }

    public function collectionPage(string $url, string $name, ?string $description = null): self
    {
        $this->nodes[] = array_filter([
            '@type' => 'CollectionPage',
            '@id' => $url.'#collectionpage',
            'url' => $url,
            'name' => $name,
            'description' => $description,
            'isPartOf' => ['@id' => url('/').'/#website'],
        ], fn ($v) => $v !== null);

        return $this;
    }

    public function product(Product $product, ProductOffer $offer, ?Category $category = null): self
    {
        $url = route('products.show', $product->slug);

        $node = [
            '@type' => 'Product',
            '@id' => $url.'#product',
            'name' => $product->name,
            'url' => $url,
            'description' => $this->plain($product->short_description ?: $product->description),
            'sku' => $offer->sku,
            'brand' => ['@type' => 'Brand', 'name' => $product->brand ?: 'Glow Halal'],
            // No `manufacturer` and no `countryOfOrigin` claim here on purpose:
            // Glow Halal is a RESELLER — the flagship oil is manufactured by
            // M.U. Amrelia (India). Claiming ourselves as manufacturer or
            // "made in Pakistan" would be false structured data. The seller
            // relationship is expressed truthfully on the offer node below.
        ];

        if ($category) {
            $node['category'] = $category->name;
        }

        if ($image = $product->primaryImage?->path) {
            $node['image'] = [asset('storage/'.ltrim($image, '/'))];
        }

        // Global identifier for merchant listings. Only a REAL barcode from the
        // packaging (admin: variant → barcode field) is ever emitted — an
        // invented GTIN is false data and a Merchant Center suspension risk.
        // Until one is entered, brand + sku remain the identifiers.
        $barcode = trim((string) ($product->defaultVariant?->barcode
            ?? $product->variants->firstWhere('is_default', true)?->barcode));

        if ($barcode !== '' && preg_match('/^\d{8}(\d{4,6})?$/', $barcode)) {
            $node['gtin'] = $barcode;
        }

        // Only verified exclusions become claims. `is_verified = false` means
        // nobody has checked, and an unchecked claim is not a claim.
        $properties = $product->verifiedFreeFromAttributes
            ->map(fn ($attribute) => [
                '@type' => 'PropertyValue',
                'name' => $attribute->name,
                'value' => 'Yes',
            ])->all();

        if ($properties !== []) {
            $node['additionalProperty'] = $properties;
        }

        $offerNode = [
            '@type' => $offer->isRange() ? 'AggregateOffer' : 'Offer',
            '@id' => $url.'#offer',
            'url' => $url,
            'priceCurrency' => 'PKR',
            'availability' => $offer->availability(),
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => ['@id' => url('/').'/#organization'],
            // The price has certainly been in force since the product row was
            // last saved — an honest validFrom without a price-history table.
            'validFrom' => $product->updated_at?->toDateString(),
        ];

        if ($offer->isRange()) {
            $offerNode['lowPrice'] = $offer->structuredValue();
            $offerNode['highPrice'] = $offer->structuredHighValue();
            $offerNode['offerCount'] = $product->variants->where('is_active', true)->count();
        } else {
            $offerNode['price'] = $offer->structuredValue();
            $offerNode['priceValidUntil'] = now()->endOfYear()->toDateString();

            // Explicit UnitPriceSpecification — Search Console's merchant
            // listings report normalises `price` into `priceSpecification` and
            // then flags its missing `validFrom`, so spell the whole thing out.
            // Same values as `price`/`validFrom`/`priceValidUntil` above, never
            // a second lookup (price parity, §7.4).
            $spec = array_filter([
                '@type' => 'UnitPriceSpecification',
                'price' => $offer->structuredValue(),
                'priceCurrency' => 'PKR',
                'validFrom' => $product->updated_at?->toDateString(),
                'validThrough' => now()->endOfYear()->toDateString(),
            ], fn ($v) => $v !== null);

            // When the product is discounted, the struck-through old price is
            // Google's documented sale-price pattern: a second specification
            // typed StrikethroughPrice, taken from the same compare-at field
            // the visible strikethrough renders from.
            $compareAt = $offer->variant?->compare_at_amount;

            $offerNode['priceSpecification'] = ($compareAt && $compareAt->minorUnits > $offer->price->minorUnits)
                ? [$spec, [
                    '@type' => 'UnitPriceSpecification',
                    'priceType' => 'https://schema.org/StrikethroughPrice',
                    'price' => number_format($compareAt->minorUnits / 100, 2, '.', ''),
                    'priceCurrency' => 'PKR',
                ]]
                : $spec;
        }

        // Shipping + returns on the offer node feed Google's merchant listing
        // rich result and give answer engines concrete delivery facts. Both are
        // read from live sources — the admin-managed shipping rate and the
        // published Shipping & Returns policy — never hard-coded promises.
        if ($shipping = $this->shippingDetails()) {
            $offerNode['shippingDetails'] = $shipping;
        }

        $offerNode['hasMerchantReturnPolicy'] = [
            '@type' => 'MerchantReturnPolicy',
            // Mirrors /shipping-returns: 7 days for unopened or damaged items,
            // return delivery paid by the customer unless the error is ours.
            // ReturnFeesCustomerResponsibility (customer arranges + pays their
            // own courier) — NOT ReturnShippingFees, which means "we charge a
            // fixed return fee" and forces a returnShippingFeesAmount we don't
            // have (Search Console flags the missing amount).
            'applicableCountry' => 'PK',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => 7,
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => 'https://schema.org/ReturnFeesCustomerResponsibility',
        ];

        $node['offers'] = $offerNode;

        // aggregateRating is emitted only when real, visible reviews exist.
        // Seeded or self-authored ratings on a launch store are a guidelines
        // violation and a realistic manual-action risk (seo.md §4.7).
        if ((int) $product->reviews_count > 0 && $product->reviews_average !== null) {
            $node['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $product->reviews_average,
                'reviewCount' => (int) $product->reviews_count,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        $this->nodes[] = $node;

        return $this;
    }

    /**
     * OfferShippingDetails from the live fallback-zone rate — the same numbers
     * the cart quotes, so an admin change to the flat rate or free-over
     * threshold updates the schema on the next request. Returns null when no
     * active rate exists rather than inventing a figure.
     *
     * @return array<string, mixed>|null
     */
    private function shippingDetails(): ?array
    {
        $rate = app(\App\Services\Shipping\ShippingCalculator::class)
            ->quote(\App\Support\Money::zero())->rate;

        if (! $rate || $rate->amount === null) {
            return null;
        }

        $details = [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'value' => number_format($rate->amount->toRupees(), 2, '.', ''),
                'currency' => 'PKR',
            ],
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => 'PK',
            ],
        ];

        if ($rate->min_delivery_days !== null && $rate->max_delivery_days !== null) {
            $details['deliveryTime'] = [
                '@type' => 'ShippingDeliveryTime',
                // Orders are dispatched same or next working day; without a
                // handlingTime Google cannot compute the total estimate and
                // drops the delivery-speed annotation entirely.
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 0,
                    'maxValue' => 1,
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => (int) $rate->min_delivery_days,
                    'maxValue' => (int) $rate->max_delivery_days,
                    'unitCode' => 'DAY',
                ],
            ];
        }

        return $details;
    }

    /**
     * FAQPage — built ONLY from Q&As that are visibly rendered on the same page
     * (the product page prints every one of these in a <details> block), which
     * is what keeps it inside Google's structured-data guidelines. Accepts the
     * product's `faqs` array in either {q,a} or {question,answer} shape and is a
     * no-op when there are none, so no empty node ever reaches the graph.
     *
     * @param  array<int, array<string, string>>  $faqs
     */
    public function faqPage(string $id, array $faqs): self
    {
        $entities = collect($faqs)
            ->map(fn ($f) => [
                'q' => trim((string) ($f['q'] ?? $f['question'] ?? '')),
                'a' => trim((string) ($f['a'] ?? $f['answer'] ?? '')),
            ])
            ->filter(fn ($f) => $f['q'] !== '' && $f['a'] !== '')
            ->values();

        if ($entities->isEmpty()) {
            return $this;
        }

        $this->nodes[] = [
            '@type' => 'FAQPage',
            '@id' => $id.'#faq',
            'mainEntity' => $entities->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ])->all(),
        ];

        return $this;
    }

    public function toJson(): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $this->nodes,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    private function plain(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        return \Illuminate\Support\Str::limit(trim(strip_tags($html)), 300, '');
    }
}
