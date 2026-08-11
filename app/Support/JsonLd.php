<?php

namespace App\Support;

use App\Settings\SeoSettings;
use App\Settings\StoreSettings;
use Illuminate\Support\Arr;

/**
 * JSON-LD graph builder — SEO §4.1.
 *
 * One `<script type="application/ld+json">` per document containing a single
 * `@graph`, nodes cross-referenced by `@id`. Blade never assembles JSON by
 * hand; it calls `JsonLd::render(...)` with an array of node arrays.
 *
 * ---------------------------------------------------------------------------
 * TWO HARD RULES, both of which have already been broken once on this project.
 * ---------------------------------------------------------------------------
 *
 * 1. NO `aggregateRating`, and no `review`, anywhere. There are no customer
 *    reviews. Emitting a rating without visible reviews is a structured-data
 *    guidelines violation and a manual-action risk (SEO §4.7). There is
 *    deliberately no method on this class that can produce one.
 *
 * 2. NO third-party halal approval of any kind — no certifier name, no
 *    `hasCertification`, no standard number, no seal, no issuing body. Glow
 *    Halal holds no halal accreditation. `knowsAbout` describes subject
 *    expertise, which is a different claim, and is the only place the word
 *    "halal" appears in the Organization node.
 *
 * Values that are business facts (address, phone, founder) come from
 * StoreSettings and are simply OMITTED when the owner has not filled them in.
 * A node with a missing property is a weak signal; a node with an invented one
 * is a lie. Nothing here invents.
 */
final class JsonLd
{
    public static function siteUrl(): string
    {
        return rtrim(url('/'), '/').'/';
    }

    private static function id(string $fragment): string
    {
        return rtrim(url('/'), '/').$fragment;
    }

    /** Strip nulls and empty arrays so no placeholder ever reaches the output. */
    private static function clean(array $node): array
    {
        return array_filter(
            $node,
            fn ($v) => $v !== null && $v !== '' && $v !== [],
        );
    }

    // ---------------------------------------------------------------------
    // Sitewide nodes
    // ---------------------------------------------------------------------

    public static function organization(): array
    {
        $store = app(StoreSettings::class);

        $address = self::clean([
            '@type' => 'PostalAddress',
            'streetAddress' => $store->address_line,
            'addressLocality' => $store->city,
            'postalCode' => $store->postal_code,
            'addressCountry' => 'PK',
        ]);

        // Only emit an address node once the owner has entered a real one.
        $hasAddress = isset($address['streetAddress']) || isset($address['addressLocality']);

        $founder = $store->founder_name
            ? self::clean([
                '@type' => 'Person',
                '@id' => self::id('/about#founder'),
                'name' => $store->founder_name,
                'jobTitle' => $store->founder_title,
            ])
            : null;

        $sameAs = array_values(array_filter([
            $store->instagram_url ?? null,
            $store->facebook_url ?? null,
            $store->tiktok_url ?? null,
            $store->youtube_url ?? null,
        ]));

        return self::clean([
            '@type' => 'OnlineStore',
            '@id' => self::id('/#organization'),
            'name' => 'Glow Halal',
            'url' => self::siteUrl(),
            'description' => 'Glow Halal is a Pakistani cosmetics brand that publishes the full INCI list for every product it sells, and the full list of the ingredients it will not formulate with.',
            'areaServed' => ['@type' => 'Country', 'name' => 'Pakistan'],
            'currenciesAccepted' => 'PKR',
            'email' => $store->contact_email,
            'telephone' => $store->contact_phone,
            'address' => $hasAddress ? $address : null,
            'founder' => $founder,
            // Entity-resolution helpers for answer engines: the alternate
            // spelling, a sales contact point (real number from settings) and
            // the subjects this store actually publishes about. knowsAbout is
            // subject expertise — never a certification claim.
            'alternateName' => 'GlowHalal',
            'contactPoint' => $store->contact_phone ? self::clean([
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => preg_replace('/[^+\d]/', '', $store->contact_phone),
                'availableLanguage' => ['en', 'ur'],
                'areaServed' => 'PK',
            ]) : null,
            'knowsAbout' => [
                'halal cosmetics',
                'herbal oils',
                'Lookman-e-Hayat oil',
                'champi (hair oiling)',
                'sesame (til) oil skincare',
                'cosmetic ingredient sourcing',
                'INCI nomenclature',
                'cash on delivery shopping in Pakistan',
            ],
            'sameAs' => $sameAs,
        ]);
    }

    public static function website(): array
    {
        // No `potentialAction`/SearchAction: there is no /search route (it is
        // Disallow'd in robots.txt and 404s), and advertising a site-search
        // endpoint that does not exist is a broken structured-data claim.
        return [
            '@type' => 'WebSite',
            '@id' => self::id('/#website'),
            'url' => self::siteUrl(),
            'name' => app(SeoSettings::class)->site_name ?: 'Glow Halal',
            'description' => 'Cosmetics from Pakistan that publish every ingredient in full.',
            'publisher' => ['@id' => self::id('/#organization')],
            'inLanguage' => 'en-PK',
        ];
    }

    // ---------------------------------------------------------------------
    // Per-page nodes
    // ---------------------------------------------------------------------

    /**
     * @param  string  $type  WebPage | CollectionPage | AboutPage | ContactPage | Blog | ItemPage
     */
    public static function webPage(string $canonical, string $title, ?string $description, string $type = 'WebPage', array $extra = []): array
    {
        return self::clean(array_merge([
            '@type' => $type,
            '@id' => $canonical.'#webpage',
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'isPartOf' => ['@id' => self::id('/#website')],
            'about' => ['@id' => self::id('/#organization')],
            'inLanguage' => 'en-PK',
        ], $extra));
    }

    /**
     * @param  array<int, array{name: string, url?: string|null}>  $trail
     *                                                                    The final item MUST omit `url` — it is the current page (SEO §4.5).
     */
    public static function breadcrumbs(string $canonical, array $trail): array
    {
        $items = [];

        foreach (array_values($trail) as $i => $crumb) {
            $items[] = self::clean([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'] ?? null,
            ]);
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical.'#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /** URL-only ItemList — Google's preferred summary-page form (SEO §4.8). */
    public static function itemList(string $canonical, string $name, array $urls, int $startPosition = 1): array
    {
        $elements = [];

        foreach (array_values($urls) as $i => $url) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $startPosition + $i,
                'url' => $url,
            ];
        }

        return [
            '@type' => 'ItemList',
            '@id' => $canonical.'#itemlist',
            'name' => $name,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ];
    }

    public static function blogPosting(string $canonical, array $attributes): array
    {
        return self::clean(array_merge([
            '@type' => 'BlogPosting',
            '@id' => $canonical.'#article',
            'isPartOf' => ['@id' => $canonical.'#webpage'],
            'mainEntityOfPage' => ['@id' => $canonical.'#webpage'],
            'publisher' => ['@id' => self::id('/#organization')],
            'inLanguage' => 'en-PK',
        ], $attributes));
    }

    public static function article(string $canonical, array $attributes): array
    {
        return self::clean(array_merge([
            '@type' => 'Article',
            '@id' => $canonical.'#article',
            'mainEntityOfPage' => ['@id' => $canonical.'#webpage'],
            'publisher' => ['@id' => self::id('/#organization')],
            'inLanguage' => 'en-PK',
        ], $attributes));
    }

    public static function definedTerm(string $canonical, array $attributes): array
    {
        return self::clean(array_merge([
            '@type' => 'DefinedTerm',
            '@id' => $canonical.'#term',
            'inDefinedTermSet' => [
                '@type' => 'DefinedTermSet',
                '@id' => self::id('/halal-ingredients#termset'),
                'name' => 'Glow Halal Ingredient Index',
                'url' => rtrim(url('/'), '/').'/halal-ingredients',
            ],
        ], $attributes));
    }

    public static function definedTermSet(string $canonical, int $count): array
    {
        return [
            '@type' => 'DefinedTermSet',
            '@id' => $canonical.'#termset',
            'name' => 'Glow Halal Ingredient Index',
            'url' => $canonical,
            'description' => 'A published index of cosmetic ingredients, what they are made from, and whether Glow Halal will formulate with them.',
            'hasDefinedTerm' => $count,
        ];
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faqs
     *                                                                     Only ever built from Q&As that are visibly rendered on the page.
     */
    public static function faqPage(string $canonical, array $faqs): ?array
    {
        $faqs = array_values(array_filter($faqs, fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null)));

        if ($faqs === []) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $canonical.'#faq',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
            ], $faqs),
        ];
    }

    // ---------------------------------------------------------------------
    // Output
    // ---------------------------------------------------------------------

    /** Returns the complete <script> element, ready to echo unescaped. */
    public static function render(array $nodes): string
    {
        $graph = array_values(array_filter(Arr::wrap($nodes)));

        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        );

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}
