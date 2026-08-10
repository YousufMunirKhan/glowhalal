{{--
  Category overline · date · reading time, for a Journal card or post header.

  The date is a real <time datetime> so the visible date and the ISO value in
  the BlogPosting node cannot drift apart. Reading time falls back to a word
  count when the editor has not set one.
--}}
@php
    $minutes = $post->reading_time_minutes
        ?: max(1, (int) ceil(str_word_count(strip_tags((string) $post->content)) / 200));
    // Keep category links inside the current locale ('' at root, '/ur-roman' under
    // the Roman-Urdu mirror).
    $bp = app()->getLocale() === 'ur-Latn' ? '/ur-roman' : '';
@endphp

<p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-meta text-text-muted">
    @if ($post->category)
        <a href="{{ $bp }}/blog/category/{{ $post->category->slug }}"
            class="relative z-10 text-overline uppercase text-text-gold hover:underline hover:underline-offset-[3px]">{{ $post->category->name }}</a>
        <span aria-hidden="true">·</span>
    @endif

    @if ($post->published_at)
        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
        <span aria-hidden="true">·</span>
    @endif

    <span class="tnum">{{ $minutes }} min read</span>
</p>
