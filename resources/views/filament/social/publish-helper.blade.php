@php
    /** @var \App\Models\SocialPost $record */
    $baseCaption = trim(($record->caption_base ?? '')
        . (($sig = app(\App\Settings\SocialSettings::class)->brand_signature) ? "\n\n".$sig : ''));
    $hashtags = collect($record->hashtags ?? [])
        ->map(fn ($t) => \Illuminate\Support\Str::startsWith($t, '#') ? $t : '#'.$t)
        ->implode(' ');
@endphp

<div class="space-y-4 text-sm" x-data>
    <p class="text-gray-500 dark:text-gray-400">
        Phase 0 posts by hand: copy the text, open the app, paste, publish — then use
        <strong>Mark as posted</strong>.
    </p>

    @if ($record->contains_sensitive_content && filled($record->safety_note))
        <div class="rounded-lg border border-warning-300 bg-warning-50 p-3 text-warning-800 dark:border-warning-700 dark:bg-warning-900/30 dark:text-warning-200">
            <strong>Safety note (include this):</strong>
            <div class="mt-1 whitespace-pre-line">{{ $record->safety_note }}</div>
        </div>
    @endif

    <div>
        <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Caption</div>
        <textarea readonly rows="6"
            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-mono text-xs dark:border-gray-600 dark:bg-gray-800"
            x-ref="caption">{{ $baseCaption }}</textarea>
        <button type="button"
            class="mt-1 rounded-md bg-primary-600 px-3 py-1 text-xs font-medium text-white hover:bg-primary-500"
            x-on:click="navigator.clipboard.writeText($refs.caption.value); $el.textContent='Copied!'; setTimeout(() => $el.textContent='Copy caption', 1500)">
            Copy caption
        </button>
    </div>

    @if (filled($hashtags))
        <div>
            <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Hashtags</div>
            <textarea readonly rows="2"
                class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2 font-mono text-xs dark:border-gray-600 dark:bg-gray-800"
                x-ref="tags">{{ $hashtags }}</textarea>
            <button type="button"
                class="mt-1 rounded-md bg-primary-600 px-3 py-1 text-xs font-medium text-white hover:bg-primary-500"
                x-on:click="navigator.clipboard.writeText($refs.tags.value); $el.textContent='Copied!'; setTimeout(() => $el.textContent='Copy hashtags', 1500)">
                Copy hashtags
            </button>
        </div>
    @endif

    <div>
        <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Open the app to post</div>
        <div class="flex flex-wrap gap-2">
            @forelse ($record->targets as $target)
                @php
                    $override = filled($target->caption_override) ? $target->caption_override : null;
                @endphp
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 px-2 py-1 dark:border-gray-700">
                    <a href="{{ $target->platform->appUrl() }}" target="_blank" rel="noopener"
                        class="rounded-md bg-gray-800 px-3 py-1 text-xs font-medium text-white hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-900">
                        Open {{ $target->platform->getLabel() }}
                    </a>
                    @if ($override)
                        <button type="button"
                            class="rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-gray-600"
                            x-on:click="navigator.clipboard.writeText(@js($override)); $el.textContent='Copied!'; setTimeout(() => $el.textContent='Copy its caption', 1500)">
                            Copy its caption
                        </button>
                    @endif
                    <span class="text-xs text-gray-500">{{ $target->status->getLabel() }}</span>
                </div>
            @empty
                <p class="text-gray-500">No platforms added yet — edit the post to add some.</p>
            @endforelse
        </div>
    </div>

    @if (filled($record->link_url))
        <div class="text-xs text-gray-500">Link in post: <span class="font-mono">{{ $record->link_url }}</span></div>
    @endif
</div>
