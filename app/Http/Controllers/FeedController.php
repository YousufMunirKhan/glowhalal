<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Settings\SeoSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Product feeds for paid shopping — a Google Merchant Center RSS 2.0 feed and a
 * Meta (Facebook) Commerce catalogue CSV. One offer per ACTIVE variant of every
 * PUBLISHED product, with the variant SKU as the stable `id` so the same id ties
 * a product across the store, both feeds and the on-page GA4 / Meta events.
 *
 * Real data only. A field the store does not hold (a GTIN, an MPN) is OMITTED
 * and `g:identifier_exists` is set to `no` rather than a value being invented.
 * There is deliberately no "halal certified" attribute anywhere in these feeds —
 * Glow Halal holds no accreditation and never claims one.
 *
 * These URLs are fetched directly by Google/Meta, not crawled — they are
 * Disallow'd in robots.txt.
 */
class FeedController extends Controller
{
    /**
     * Google Merchant Center — RSS 2.0 with the g: namespace.
     */
    public function google(): Response
    {
        $storeName = $this->storeName();
        $home = url('/');

        $body = '';

        foreach ($this->offers() as $offer) {
            $body .= "    <item>\n";
            $body .= '      <g:id>'.$this->xml($offer['id'])."</g:id>\n";
            $body .= '      <title>'.$this->xml($offer['title'])."</title>\n";
            $body .= '      <description>'.$this->xml($offer['description'])."</description>\n";
            $body .= '      <link>'.$this->xml($offer['link'])."</link>\n";

            if ($offer['image_link']) {
                $body .= '      <g:image_link>'.$this->xml($offer['image_link'])."</g:image_link>\n";
            }

            $body .= '      <g:availability>'.$this->xml($offer['availability_google'])."</g:availability>\n";
            $body .= '      <g:price>'.$this->xml($offer['price'])."</g:price>\n";

            if ($offer['sale_price']) {
                $body .= '      <g:sale_price>'.$this->xml($offer['sale_price'])."</g:sale_price>\n";
            }
            $body .= '      <g:brand>'.$this->xml($offer['brand'])."</g:brand>\n";
            $body .= '      <g:condition>'.$this->xml($offer['condition'])."</g:condition>\n";
            // No GTIN/MPN in the catalogue → declare it honestly rather than fake one.
            $body .= "      <g:identifier_exists>no</g:identifier_exists>\n";

            if ($offer['product_type']) {
                $body .= '      <g:product_type>'.$this->xml($offer['product_type'])."</g:product_type>\n";
            }

            $body .= "    </item>\n";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n"
            ."  <channel>\n"
            .'    <title>'.$this->xml($storeName.' — Product Feed')."</title>\n"
            .'    <link>'.$this->xml($home)."</link>\n"
            .'    <description>'.$this->xml('Google Merchant Center product feed for '.$storeName)."</description>\n"
            .$body
            ."  </channel>\n"
            .'</rss>'."\n";

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * Meta / Facebook Commerce — CSV Meta can schedule-pull. Same ids as the
     * Google feed.
     */
    public function meta(): Response
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['id', 'title', 'description', 'availability', 'condition', 'price', 'sale_price', 'link', 'image_link', 'brand'], escape: '\\');

        foreach ($this->offers() as $offer) {
            fputcsv($handle, [
                $offer['id'],
                $offer['title'],
                $offer['description'],
                $offer['availability_meta'],
                $offer['condition'],
                $offer['price'],
                $offer['sale_price'] ?? '',
                $offer['link'],
                $offer['image_link'],
                $offer['brand'],
            ], escape: '\\');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="meta-catalog.csv"',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * One normalised offer row per active variant of every published product.
     * Both feeds render from this single source, so the two can never disagree.
     *
     * @return Collection<int, array<string, string|null>>
     */
    private function offers(): Collection
    {
        return Product::query()
            ->published()
            ->with([
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
                'variants.inventory',
                'primaryImage',
                'primaryCategory',
            ])
            ->orderBy('position')
            ->get()
            ->flatMap(fn (Product $product) => $product->variants
                ->map(fn (ProductVariant $variant) => $this->offerRow($product, $variant))
                ->filter()
            )
            ->values();
    }

    /**
     * @return array<string, string|null>|null  null when the variant has no price
     */
    private function offerRow(Product $product, ProductVariant $variant): ?array
    {
        $price = $variant->price_amount;

        if ($price === null) {
            return null;
        }

        $inStock = $variant->allow_backorder || ($variant->available_quantity ?? 0) > 0;

        // Discount mapping per Google/Meta spec: when compare-at is set, the
        // regular price goes in `price` and the actual selling price in
        // `sale_price` — that is what renders the strikethrough in listings.
        $compareAt = $variant->compare_at_amount;
        $onSale = $compareAt !== null && $compareAt->minorUnits > $price->minorUnits;

        return [
            'id' => $variant->sku,
            'title' => trim($product->name),
            'description' => $this->plainDescription($product),
            'link' => route('products.show', $product->slug),
            'image_link' => $product->primaryImage?->url(),
            'availability_google' => $inStock ? 'in_stock' : 'out_of_stock',
            'availability_meta' => $inStock ? 'in stock' : 'out of stock',
            // Rupees, two decimals + ISO currency, e.g. "1800.00 PKR".
            'price' => number_format(($onSale ? $compareAt : $price)->toRupees(), 2, '.', '').' PKR',
            'sale_price' => $onSale ? number_format($price->toRupees(), 2, '.', '').' PKR' : null,
            'brand' => $product->brand ?: 'Glow Halal',
            'condition' => 'new',
            'product_type' => $product->primaryCategory?->name,
        ];
    }

    /** Plain text, no HTML — feeds reject markup. */
    private function plainDescription(Product $product): string
    {
        $text = $product->short_description ?: strip_tags((string) $product->description);
        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        return Str::limit($text, 4900, '');
    }

    private function storeName(): string
    {
        return rescue(fn () => app(SeoSettings::class)->site_name, null, false) ?: 'Glow Halal';
    }

    private function xml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
