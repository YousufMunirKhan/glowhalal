<?php

namespace App\Filament\Pages;

use App\Models\SocialPost;
use App\Settings\SocialSettings;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Server-rendered month grid of scheduled posts. Deliberately no JS calendar
 * library — a plain Livewire page is robust, has nothing to break at build time,
 * and is enough for Phase 0 planning.
 */
class SocialCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Content Calendar';

    protected static ?string $navigationLabel = 'Calendar';

    protected string $view = 'filament.pages.social-calendar';

    public int $year;

    public int $month;

    public function mount(): void
    {
        $now = Carbon::now($this->timezone());
        $this->year = $now->year;
        $this->month = $now->month;
    }

    public function timezone(): string
    {
        return app(SocialSettings::class)->timezoneOrDefault();
    }

    public function previousMonth(): void
    {
        $ref = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $ref->year;
        $this->month = $ref->month;
    }

    public function nextMonth(): void
    {
        $ref = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $ref->year;
        $this->month = $ref->month;
    }

    public function goToday(): void
    {
        $now = Carbon::now($this->timezone());
        $this->year = $now->year;
        $this->month = $now->month;
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Posts of the visible month, keyed by day-of-month (in the display TZ).
     *
     * @return Collection<int, Collection<int, SocialPost>>
     */
    public function getPostsByDayProperty(): Collection
    {
        $tz = $this->timezone();
        $start = Carbon::create($this->year, $this->month, 1, 0, 0, 0, $tz)->utc();
        $end = Carbon::create($this->year, $this->month, 1, 0, 0, 0, $tz)->addMonth()->utc();

        return SocialPost::query()
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$start, $end])
            ->with('targets')
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (SocialPost $p) => $p->scheduled_at->copy()->setTimezone($tz)->day);
    }

    /**
     * Week-by-week grid of Carbon dates (Mon–Sun), including leading/trailing
     * days from adjacent months so the grid is always full rows.
     *
     * @return array<int, array<int, Carbon>>
     */
    public function getWeeksProperty(): array
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $gridStart = $first->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $first->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $days = collect(CarbonPeriod::create($gridStart, $gridEnd))->all();

        return array_chunk($days, 7);
    }

    public function overdueCount(): int
    {
        return SocialPost::query()
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now())
            ->whereNotIn('status', ['posted', 'archived'])
            ->count();
    }
}
