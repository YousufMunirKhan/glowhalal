@props(['rows' => [], 'total' => 8])

{{--
  Design system §3.7 — homepage condensed variant of the "What We Never Use"
  table: six rows, two columns (Ingredient · INCI / label name), then a
  tertiary link to the full page.

  Authored ONCE as a semantic <table> and restyled to stacked cards below 768px
  via the `inci-table` utility (display:block plus ::before { content:
  attr(data-label) }). One markup, two layouts, and the screen-reader
  experience stays tabular. A horizontally scrolling data table at 375px is
  unusable and — worse — un-screenshot-able.

  No accordion, no "read more", no lazy loading. The rows are in the initial
  HTML. INCI names are selectable mono text and are never inside an image: an
  ingredient list as a flat image defeats the entire purpose.
--}}

<table class="inci-table w-full text-start">
    <caption class="sr-only">
        Six of the {{ $total }} ingredients Glow Halal never uses, with the names printed on product labels.
    </caption>
    <thead>
        <tr>
            {{-- Fixed palette values, so the header row needs an explicit
                 `on-dark:` pair. #C9A961 on #1C1C1C = 7.57:1 PASS AAA. --}}
            <th scope="col" class="w-2/5 bg-ink-100 px-4 py-3 text-start text-meta font-semibold
                uppercase tracking-caps text-ink-800
                on-dark:bg-dark-surface on-dark:text-gold-on-dark">Ingredient</th>
            <th scope="col" class="bg-ink-100 px-4 py-3 text-start text-meta font-semibold
                uppercase tracking-caps text-ink-800
                on-dark:bg-dark-surface on-dark:text-gold-on-dark">On the label (INCI)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr id="{{ \Illuminate\Support\Str::slug($row['ingredient']) }}" class="border-t border-border-subtle">
                <td class="px-4 py-4 align-top text-title-sm text-text-primary">{{ $row['ingredient'] }}</td>
                <td class="px-4 py-4 align-top" data-label="On the label">
                    @foreach ($row['inci'] as $name)
                        <span class="block font-mono text-inci text-text-primary select-all">{{ $name }}</span>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
