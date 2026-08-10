@php
    use App\Filament\Resources\SocialPosts\SocialPostResource;

    // Status colours are rendered with INLINE styles (not Tailwind utilities) so
    // they survive Filament's CSS purge — the precompiled panel build never scans
    // this custom page view, so utilities like `bg-sky-100` would be dropped.
    // Each entry: light background / light text / dark background / dark text.
    $statusColors = [
        'draft'        => ['#f3f4f6', '#374151', '#374151', '#e5e7eb'],
        'needs_review' => ['#fef3c7', '#92400e', '#78350f', '#fde68a'],
        'scheduled'    => ['#e0f2fe', '#075985', '#0c4a6e', '#bae6fd'],
        'posted'       => ['#d1fae5', '#065f46', '#064e3b', '#a7f3d0'],
        'archived'     => ['#ffe4e6', '#9f1239', '#881337', '#fecdd3'],
    ];

    $pillStyle = function (string $status) use ($statusColors): string {
        [$bg, $fg] = $statusColors[$status] ?? $statusColors['draft'];
        return "background:{$bg};color:{$fg};";
    };

    $postsByDay = $this->postsByDay;
    $tz = $this->timezone();
    $today = \Carbon\Carbon::now($tz);
    $overdue = $this->overdueCount();
@endphp

<x-filament-panels::page>
    {{-- All layout below is driven by inline `style` + this scoped block, never by
         purgeable Tailwind utilities, so the grid can't collapse into a list. --}}
    <style>
        .sc-grid { display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:1px; background:#e5e7eb; }
        .dark .sc-grid { background:#374151; }

        .sc-head { text-align:center; font-size:11px; font-weight:600; text-transform:uppercase;
                   letter-spacing:.05em; padding:8px 0; background:#f9fafb; color:#6b7280; }
        .dark .sc-head { background:#1f2937; color:#9ca3af; }

        .sc-cell { min-height:104px; background:#ffffff; padding:6px; vertical-align:top; }
        .dark .sc-cell { background:#111827; }
        .sc-cell--out { opacity:.4; }

        .sc-daynum { font-size:11px; font-weight:600; color:#9ca3af; }
        .sc-daynum--today { display:inline-flex; align-items:center; justify-content:center;
                            height:20px; min-width:20px; padding:0 5px; border-radius:9999px;
                            background:#059669; color:#ffffff; }

        .sc-pill { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
                   border-radius:5px; padding:3px 6px; font-size:11px; line-height:1.25; margin-bottom:4px;
                   text-decoration:none; }
        .sc-pill:hover { opacity:.82; }
        .sc-pill time { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-weight:600; }

        .sc-legend-dot { display:inline-block; height:12px; width:12px; border-radius:3px; }
        .sc-scroll { overflow-x:auto; }
        .sc-canvas { min-width:720px; }
    </style>

    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <x-filament::button color="gray" size="sm" wire:click="previousMonth" icon="heroicon-o-chevron-left">
                Prev
            </x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="goToday">Today</x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="nextMonth" icon="heroicon-o-chevron-right" icon-position="after">
                Next
            </x-filament::button>
            <h2 class="ms-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $this->monthLabel }}</h2>
        </div>

        <div style="display:flex; align-items:center; gap:12px; font-size:13px;">
            @if ($overdue > 0)
                <span style="border-radius:9999px; padding:4px 12px; font-weight:500; background:#ffe4e6; color:#9f1239;">
                    {{ $overdue }} overdue / unposted
                </span>
            @endif
            <span class="text-gray-500 dark:text-gray-400">Times shown in {{ $tz }}</span>
        </div>
    </div>

    <div class="sc-scroll" style="margin-top:16px;">
        <div class="sc-canvas">
            {{-- Weekday header row (Mon–Sun) --}}
            <div class="sc-grid" style="border-top-left-radius:12px; border-top-right-radius:12px; overflow:hidden;">
                @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow)
                    <div class="sc-head">{{ $dow }}</div>
                @endforeach
            </div>

            {{-- Day cells, one .sc-grid row per week --}}
            @foreach ($this->weeks as $week)
                <div class="sc-grid">
                    @foreach ($week as $day)
                        @php
                            $inMonth = ((int) $day->month === $this->month);
                            $isToday = $day->isSameDay($today);
                            $dayPosts = $inMonth ? ($postsByDay[$day->day] ?? collect()) : collect();
                        @endphp
                        <div class="sc-cell {{ $inMonth ? '' : 'sc-cell--out' }}">
                            <div style="margin-bottom:4px;">
                                <span class="sc-daynum {{ $isToday ? 'sc-daynum--today' : '' }}">{{ $day->day }}</span>
                            </div>

                            @foreach ($dayPosts as $post)
                                <a href="{{ SocialPostResource::getUrl('edit', ['record' => $post]) }}"
                                    class="sc-pill"
                                    style="{{ $pillStyle($post->status->value) }}"
                                    title="{{ $post->title }} — {{ $post->status->getLabel() }}">
                                    <time>{{ $post->scheduled_at->copy()->setTimezone($tz)->format('H:i') }}</time>
                                    {{ \Illuminate\Support\Str::limit($post->title, 22) }}@if ($post->targets->isNotEmpty()) · {{ $post->targets->count() }}p @endif
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Status legend --}}
    <div style="margin-top:16px; display:flex; flex-wrap:wrap; gap:12px; font-size:12px;" class="text-gray-500 dark:text-gray-400">
        @foreach (\App\Enums\SocialPostStatus::cases() as $case)
            @php [$bg] = $statusColors[$case->value] ?? ['#f3f4f6']; @endphp
            <span style="display:inline-flex; align-items:center; gap:6px;">
                <span class="sc-legend-dot" style="background:{{ $bg }};"></span>
                {{ $case->getLabel() }}
            </span>
        @endforeach
    </div>
</x-filament-panels::page>
