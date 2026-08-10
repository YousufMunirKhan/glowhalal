<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartOnLogin;
use App\Models\Category;
use App\Models\Product;
use App\Models\SlugHistory;
use App\Services\Cart\CartManager;
use App\Services\Payments\PaymentManager;
use App\Settings\SeoSettings;
use App\Settings\StoreSettings;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Architecture §6.4.
        $this->app->singleton(
            PaymentManager::class,
            fn ($app) => new PaymentManager($app, config('payments.drivers', [])),
        );

        // Resolved once per request so no component ever re-queries the cart,
        // and so a cart created mid-request is visible to everything after it.
        $this->app->scoped(CartManager::class);
    }

    public function boot(): void
    {
        Event::listen(Login::class, MergeGuestCartOnLogin::class);

        $this->bindCatalogueSlugs();
        $this->shareSettingsWithViews();
        $this->applyIntegrationSettings();
    }

    /**
     * Lets the owner manage the Google keys from Admin → SEO & Integrations
     * without touching code: any value saved there overrides the fallback in
     * config/services.php. Every consumer keeps reading config('services.google.*'),
     * so the controller, the One Tap partial and the analytics tag need no
     * changes. Swallowed on failure (e.g. settings table not migrated yet) so a
     * fresh install still boots on the hard-coded fallbacks.
     */
    private function applyIntegrationSettings(): void
    {
        // Wrapped whole: on an un-migrated database, spatie loads settings
        // lazily and throws MissingSettings the moment a not-yet-added property
        // is read — which would otherwise abort `php artisan migrate` itself,
        // the very command that adds those properties. Fail silently onto the
        // config/services.php fallbacks instead.
        try {
            $seo = app(SeoSettings::class);

            config([
                'services.google.client_id' => $seo->google_oauth_client_id ?: config('services.google.client_id'),
                'services.google.client_secret' => $seo->google_oauth_client_secret ?: config('services.google.client_secret'),
                'services.google.redirect' => $seo->google_oauth_redirect ?: config('services.google.redirect'),
                'services.google.analytics_id' => $seo->google_analytics_id ?: config('services.google.analytics_id'),
            ]);
        } catch (\Throwable) {
            // Keep booting on the hard-coded fallbacks.
        }
    }

    /**
     * Makes `$store` and `$seo` available in every Blade view, so storefront
     * templates never hardcode a phone number, address or founder bio again.
     *
     * Uses a view composer rather than `View::share()` so the settings are
     * resolved lazily — admin requests and console commands never pay for a
     * settings query they do not use.
     *
     * Failures are swallowed on purpose: if the settings migrations have not
     * run yet, a missing row must not take down every page in the application.
     * Views already treat null as "not configured, render nothing".
     */
    private function shareSettingsWithViews(): void
    {
        View::composer('*', function ($view): void {
            $data = $view->getData();

            if (! array_key_exists('store', $data)) {
                $view->with('store', $this->resolveSettings(StoreSettings::class));
            }

            if (! array_key_exists('seo', $data)) {
                $view->with('seo', $this->resolveSettings(SeoSettings::class));
            }
        });
    }

    private function resolveSettings(string $class): ?object
    {
        static $cache = [];

        if (array_key_exists($class, $cache)) {
            return $cache[$class];
        }

        try {
            return $cache[$class] = app($class);
        } catch (\Throwable) {
            return $cache[$class] = null;
        }
    }

    /**
     * Architecture §7.2, layer 1 — slug history resolution.
     *
     * Route-model binding tries the live slug first; on a miss it consults
     * `slug_histories` and 301s to the current URL. The old URL must never
     * render 200 with duplicate content, which is why the redirect is thrown
     * out of the binder rather than returned from the controller.
     */
    private function bindCatalogueSlugs(): void
    {
        Route::bind('product', function (string $slug) {
            $product = Product::query()
                ->published()
                ->where('slug', $slug)
                ->first();

            if ($product) {
                return $product;
            }

            $historical = SlugHistory::where('sluggable_type', Product::class)
                ->where('slug', $slug)
                ->first();

            abort_unless($historical?->sluggable, 404);

            abort(redirect()->to(route('products.show', $historical->sluggable->slug), 301));
        });

        Route::bind('category', function (string $slug) {
            $category = Category::query()
                ->where('is_active', true)
                ->where('slug', $slug)
                ->first();

            if ($category) {
                return $category;
            }

            $historical = SlugHistory::where('sluggable_type', Category::class)
                ->where('slug', $slug)
                ->first();

            abort_unless($historical?->sluggable, 404);

            abort(redirect()->to(route('shop.category', $historical->sluggable->slug), 301));
        });
    }
}
