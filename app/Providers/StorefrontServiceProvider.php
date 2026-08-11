<?php

namespace App\Providers;

use App\Models\Product;
use App\Services\Cart\CartManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Chrome data for the shared layout.
 *
 * `components/layouts/app.blade.php` hands `company`, `founder` and `products`
 * to the footer and the mobile menu, and the homepage passes them from
 * routes/web.php. Commerce pages are rendered by controllers that have no
 * business knowing the founder's biography, so this composer fills the gap —
 * and only ever fills it, never overrides a value a page supplied itself.
 *
 * Lives in its own provider rather than AppServiceProvider because three
 * agents are editing this codebase concurrently and a file nobody else touches
 * cannot be clobbered.
 */
class StorefrontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view): void {
            $data = $view->getData();

            if (! array_key_exists('company', $data) || $data['company'] === []) {
                $view->with('company', $this->company());
            }

            if (! array_key_exists('founder', $data) || $data['founder'] === []) {
                $view->with('founder', $this->founder());
            }

            if (! array_key_exists('products', $data) || $data['products'] === []) {
                $view->with('products', $this->products());
            }

            if (! array_key_exists('cartCount', $data) || ! $data['cartCount']) {
                $view->with('cartCount', $this->cartCount());
            }
        });
    }

    /**
     * The header/bottom-nav badge.
     *
     * `createIfMissing: false` matters here: the layout renders on every page
     * including 404s and crawler hits, and creating a cart row for each of
     * those would fill the table with empty baskets and make abandoned-cart
     * reporting meaningless (architecture §5.5).
     */
    private function cartCount(): int
    {
        try {
            return (int) (app(CartManager::class)->current(createIfMissing: false)?->items_count ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    /** @return array<int, array{name: string, slug: string}> */
    private function products(): array
    {
        try {
            return Product::published()
                ->orderByDesc('is_featured')
                ->orderBy('position')
                ->limit(8)
                ->get(['id', 'name', 'slug'])
                ->map(fn (Product $p) => ['name' => $p->name, 'slug' => $p->slug])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Contact details only. Company registration identifiers (registered legal
     * name, tax numbers, incorporation number) are deliberately absent at the
     * owner's instruction, and there is no third-party halal approval line
     * because Glow Halal holds no accreditation from any body.
     */
    private function company(): array
    {
        $store = $this->settings();

        // Settings-only, no invented fallbacks: a fake phone/email shown to a
        // customer is worse than an empty slot, and every consuming view
        // already renders these conditionally.
        return [
            'name' => 'Glow Halal',
            'address' => $store?->formattedAddress(),
            'mobile' => $store?->contact_phone,
            'whatsapp' => $store?->whatsappLink('Hi, I have a question about Glow Halal'),
            'email' => $store?->contact_email,
        ];
    }

    /**
     * REAL founder data only — from Store settings. No invented name, city,
     * quotes or origin story: a fabricated persona is a lie the honesty rules
     * forbid. Blank settings mean an empty array, and every consuming view
     * already renders nothing for an absent founder.
     */
    private function founder(): array
    {
        $store = $this->settings();

        if (blank($store?->founder_name)) {
            return [];
        }

        return [
            'name' => $store->founder_name,
            'city' => $store->city,
            'photo' => $store->founder_photo_path
                ? asset('storage/'.ltrim($store->founder_photo_path, '/'))
                : null,
            'photo_large' => null,
            'photo_alt' => $store->founder_name.', founder of Glow Halal',
            'pull_quote' => '',
            'footer_quote' => '',
            'origin' => $store->brand_story ?? '',
        ];
    }

    private function settings(): ?object
    {
        try {
            return app(\App\Settings\StoreSettings::class);
        } catch (\Throwable) {
            return null;
        }
    }
}
