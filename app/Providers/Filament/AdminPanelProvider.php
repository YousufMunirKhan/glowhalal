<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile()
            ->brandName('Glow Halal')
            // SPA navigation. Filament renders every panel page through Livewire's
            // wire:navigate instead of a full document load, so moving between
            // resources swaps only the changed markup and keeps scroll position.
            // Safe here because the panel is behind auth and never indexed —
            // the storefront deliberately does NOT do this (see docs/seo.md §8).
            ->spa()
            ->colors([
                'primary' => Color::Emerald,   // halal / clean-beauty register
            ])
            // Groups deliberately carry NO icons. Filament 5 throws a hard exception if a
            // navigation group and any of its items both have icons, and every resource
            // sets its own $navigationIcon — so icons live on the items, not the groups.
            ->navigationGroups([
                NavigationGroup::make('Catalogue'),
                NavigationGroup::make('Halal'),
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Social'),
                NavigationGroup::make('Settings')->collapsed(),
            ])
            // Powers the bell menu that the social:due-digest reminder writes to.
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\SalesStatsOverview::class,
                \App\Filament\Widgets\RevenueChart::class,
                // ExpiringCertificationsWidget is deliberately NOT registered.
                // Glow Halal holds no halal certification, so a dashboard panel
                // counting down certificate expiry dates only ever displays demo
                // rows and implies a credential the business does not have.
                // The resource still exists — re-register this if real
                // certification is ever obtained.
                \App\Filament\Widgets\LowStockWidget::class,
                \App\Filament\Widgets\LatestOrdersWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
