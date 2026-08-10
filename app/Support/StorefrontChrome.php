<?php

namespace App\Support;

use App\Models\Product;
use App\Settings\StoreSettings;
use Illuminate\Support\Facades\Storage;

/**
 * Shared chrome data for every content page.
 *
 * `<x-layouts.app>` feeds the site footer a `$company`, `$founder` and
 * `$products` array. Those are business facts, so they come from
 * StoreSettings — the admin-editable singleton — and NOT from a literal in a
 * controller or a Blade file. Anything the owner has not filled in yet
 * resolves to an empty string and renders as a blank field.
 *
 * That is deliberate. A blank address is a visible prompt to go and enter one;
 * a plausible-looking invented address is a lie that ships. An earlier pass on
 * this project invented a founder and a Lahore street address, and both had to
 * be stripped out. Nothing in this file may reintroduce them: there is no
 * fallback string here for a name, an address, a phone number, a registration
 * identifier or a certificate, because there is no honest one.
 *
 * Lives in Support rather than in a controller trait so the error views
 * (resources/views/errors/*), which have no controller, can call it too.
 */
final class StorefrontChrome
{
    /**
     * @param  string|null  $current  Header nav key: shop | never-use | ingredient-index | about | journal
     * @return array<string, mixed>
     */
    public static function layout(?string $current = null): array
    {
        $store = app(StoreSettings::class);

        return [
            'current' => $current,
            'products' => self::footerProducts(),
            'company' => [
                'name' => 'Glow Halal',
                'address' => $store->formattedAddress() ?? '',
                'landline' => $store->contact_phone ?? '',
                'mobile' => $store->whatsapp_number ?? '',
                'email' => $store->contact_email ?? '',
            ],
            'founder' => [
                'photo' => self::founderPhoto(),
                // A statement about the company, not a quotation attributed to a
                // named person. There is no founder record yet and one must not
                // be invented to fill this slot.
                'footer_quote' => 'Every ingredient we use is named on the page.',
                'name' => $store->founder_name ?? '',
                'city' => $store->city ?? '',
            ],
        ];
    }

    public static function founderPhoto(): string
    {
        $path = app(StoreSettings::class)->founder_photo_path;

        return filled($path) ? Storage::url($path) : '/images/placeholder/founder.svg';
    }

    /**
     * The footer's Shop column. Query-driven, so it is correct at 5 products
     * and at 5,000.
     *
     * @return array<int, array{name: string, slug: string}>
     */
    public static function footerProducts(): array
    {
        return Product::query()
            ->published()
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(fn (Product $p) => ['name' => $p->name, 'slug' => $p->slug])
            ->all();
    }
}
