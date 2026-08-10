<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\CouponType;
use App\Filament\Forms\MoneyInput;
use App\Models\Coupon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->copyable()->weight('bold'),
                TextColumn::make('name')->searchable()->wrap(),
                TextColumn::make('type')->badge(),
                TextColumn::make('value')
                    ->label('Value')
                    ->state(fn (Coupon $record) => match ($record->type) {
                        CouponType::Percentage => $record->percentage_label,
                        CouponType::FixedAmount => MoneyInput::format($record->fixed_amount),
                        CouponType::FreeShipping => 'Free shipping',
                        default => '—',
                    }),
                TextColumn::make('used_count')
                    ->label('Used')
                    ->badge()
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->usage_limit
                        ? "{$state} / {$record->usage_limit}"
                        : (string) $state)
                    ->color(fn (Coupon $record) => $record->isExhausted() ? 'danger' : 'gray'),
                TextColumn::make('ends_at')->label('Expires')->dateTime('d M Y')->placeholder('Never')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')->options(CouponType::class),
                TernaryFilter::make('is_active'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make(), DeleteAction::make()]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
