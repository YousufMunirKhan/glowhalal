<?php

namespace App\Filament\Resources\InventoryItems\Tables;

use App\Enums\StockMovementReason;
use App\Models\InventoryItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Read-mostly. Quantities change through the adjustment action, which always
 * writes a matching `inventory_movements` ledger row — a stock level with no
 * audit trail is a number nobody can defend.
 */
class InventoryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['variant.product:id,name', 'location:id,name']))
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->searchable()
                    ->wrap()
                    ->description(fn (InventoryItem $record) => $record->variant?->name),

                TextColumn::make('variant.sku')->label('SKU')->searchable()->copyable(),

                TextColumn::make('location.name')->label('Location')->toggleable(),

                TextColumn::make('quantity_on_hand')->label('On hand')->numeric()->sortable(),
                TextColumn::make('quantity_reserved')->label('Reserved')->numeric()->sortable(),

                TextColumn::make('quantity_available')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state, InventoryItem $record) => match (true) {
                        (int) $state <= 0 => 'danger',
                        (int) $state <= (int) $record->reorder_level => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('reorder_level')->label('Reorder at')->numeric()->toggleable(),

                TextColumn::make('last_counted_at')->label('Last counted')
                    ->dateTime('d M Y')->placeholder('Never')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('inventory_location_id')
                    ->label('Location')
                    ->relationship('location', 'name'),
                Filter::make('low_stock')
                    ->label('At or below reorder level')
                    ->query(fn (Builder $q) => $q->whereColumn('quantity_available', '<=', 'reorder_level')),
                Filter::make('out_of_stock')
                    ->label('Out of stock')
                    ->query(fn (Builder $q) => $q->where('quantity_available', '<=', 0)),
            ])
            ->recordActions([
                Action::make('adjust')
                    ->label('Adjust stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('primary')
                    ->schema([
                        TextInput::make('quantity_delta')
                            ->label('Change')
                            ->numeric()
                            ->required()
                            ->helperText('Positive adds stock, negative removes it.'),
                        Select::make('reason')
                            ->options(StockMovementReason::class)
                            ->default(StockMovementReason::Adjustment)
                            ->required()->native(false),
                        TextInput::make('note')->maxLength(255),
                    ])
                    ->action(fn (InventoryItem $record, array $data) => self::adjust($record, $data)),
            ])
            ->defaultSort('quantity_available');
    }

    private static function adjust(InventoryItem $item, array $data): void
    {
        $delta = (int) $data['quantity_delta'];

        DB::transaction(function () use ($item, $data, $delta) {
            $locked = InventoryItem::whereKey($item->getKey())->lockForUpdate()->first();

            $locked->quantity_on_hand = max(0, $locked->quantity_on_hand + $delta);

            if (($data['reason'] ?? null) === StockMovementReason::Recount->value) {
                $locked->last_counted_at = now();
            }

            $locked->save();

            $locked->movements()->create([
                'quantity_delta' => $delta,
                'quantity_after' => $locked->quantity_on_hand,
                'reason' => $data['reason'],
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ]);
        });

        Notification::make()->success()->title('Stock adjusted')->send();
    }
}
