<?php

namespace App\Filament\Widgets;

use App\Models\ProductCertification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * The halal-specific widget, and the one that earns its dashboard slot.
 *
 * An expired certificate on a live product page is the worst failure this brand
 * can have, and it fails *silently* — nothing breaks, the claim just quietly
 * becomes false. Put it where someone sees it every morning.
 */
class ExpiringCertificationsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    /**
     * Disabled. Glow Halal holds no halal certification, so a dashboard panel
     * counting down certificate expiry dates implies a credential the business
     * does not have.
     *
     * Removing it from AdminPanelProvider::widgets() is NOT sufficient —
     * `discoverWidgets()` scans this directory and re-registers the class
     * regardless. This is the switch that actually takes it off the dashboard.
     *
     * Flip to `true` only if real third-party certification is ever obtained.
     */
    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Halal certificates expiring within 60 days')
            ->description('An expired certificate keeps rendering as a live claim until someone acts on it.')
            ->query(
                ProductCertification::query()
                    ->with(['product:id,name,slug', 'body:id,name,short_name'])
                    ->expiringWithin(60)
                    ->orderBy('expires_at')
            )
            ->emptyStateHeading('No certificates expiring soon')
            ->emptyStateDescription('Nothing needs renewal in the next 60 days.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->columns([
                TextColumn::make('product.name')->label('Product')->wrap()->searchable(),
                TextColumn::make('body.name')->label('Certifier')->wrap(),
                TextColumn::make('certificate_number')->label('Certificate')->copyable(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->expires_at?->diffInDays(now(), absolute: true) <= 30 ? 'danger' : 'warning')
                    ->description(fn ($record) => $record->expires_at
                        ? $record->expires_at->diffForHumans()
                        : null),
                TextColumn::make('status')->badge(),
            ])
            ->paginated([5, 10]);
    }
}
