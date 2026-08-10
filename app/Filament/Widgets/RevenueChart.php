<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    /** Note: instance property, not static — §4.9. */
    protected ?string $heading = 'Revenue, last 30 days';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = today()->subDays(29);

        $rows = Order::placed()
            ->whereDate('placed_at', '>=', $start)
            ->selectRaw('DATE(placed_at) AS day, SUM(grand_total_amount) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($date = $start->copy(); $date <= today(); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('d M');
            $values[] = round(((int) ($rows[$key] ?? 0)) / 100, 2);   // paisa → rupees
        }

        return [
            'datasets' => [[
                'label' => 'Revenue (Rs)',
                'data' => $values,
                'borderColor' => 'rgb(16, 185, 129)',
                'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
