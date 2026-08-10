<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Order::placed()->whereDate('placed_at', today());

        return [
            Stat::make('Orders today', (clone $today)->count())
                ->description('Awaiting confirmation: '.Order::where('status', OrderStatus::Pending)->count())
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Revenue today', (new Money((int) (clone $today)->sum('grand_total_amount')))->format())
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Bank transfers to verify', Payment::where('driver', 'bank_transfer')
                ->where('status', PaymentAttemptStatus::AwaitingVerification)->count())
                ->description('Customers are waiting on these')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning'),
        ];
    }
}
