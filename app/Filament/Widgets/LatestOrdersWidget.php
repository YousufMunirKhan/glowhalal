<?php

namespace App\Filament\Widgets;

use App\Filament\Forms\MoneyInput;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest orders')
            ->query(Order::query()->latest('created_at')->limit(10))
            ->emptyStateHeading('No orders yet')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->columns([
                TextColumn::make('order_number')->label('Order')->copyable()->weight('bold'),
                TextColumn::make('customer_name')->wrap()
                    ->description(fn (Order $record) => $record->email),
                TextColumn::make('grand_total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => MoneyInput::format($state)),
                TextColumn::make('payment_status')->label('Payment')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->label('Placed')->since(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
