<?php

namespace App\Http\Controllers\Concerns;

use App\Support\JsonLd;
use App\Support\StorefrontChrome;

/**
 * Thin controller-side sugar over App\Support\StorefrontChrome and
 * App\Support\JsonLd. See those classes for the rules that govern what may and
 * may not appear in the chrome and in the structured data.
 */
trait BuildsStorefrontLayout
{
    /** @return array<string, mixed> Props for <x-layouts.app>. */
    protected function layoutData(?string $current = null): array
    {
        return StorefrontChrome::layout($current);
    }

    /** The absolute, parameter-free canonical for the current request (SEO §2.5). */
    protected function canonical(): string
    {
        return url()->current();
    }

    /**
     * Self-referencing canonical that keeps ?page=n and drops everything else.
     * Page 1 is always the bare URL — /blog?page=1 must never be a canonical.
     */
    protected function paginatedCanonical(int $page): string
    {
        return $page > 1
            ? url()->current().'?page='.$page
            : url()->current();
    }

    /**
     * Every content page emits ONE ld+json script holding ONE @graph, with the
     * two sitewide nodes always present (SEO §4.1).
     *
     * @param  array<int, array<string, mixed>|null>  $nodes
     */
    protected function schema(array $nodes): string
    {
        return JsonLd::render(array_merge(
            [JsonLd::organization(), JsonLd::website()],
            array_values(array_filter($nodes)),
        ));
    }
}
