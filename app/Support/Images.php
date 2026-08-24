<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Blade-side helper for the WebP siblings created by `images:webp`.
 *
 * Returns the sibling's public URL only when the file really exists, so
 * templates can offer `<picture><source type="image/webp" …>` and fall back
 * to the original transparently — a product uploaded five minutes ago (no
 * .webp yet) simply serves its JPEG until the next conversion run.
 */
class Images
{
    /** Cache per request: hero + grid ask about the same handful of files. */
    private static array $memo = [];

    /**
     * "/storage/products/x.jpg" (or a full URL to it) -> the .webp sibling's
     * URL, or null when none exists.
     */
    public static function webp(?string $publicUrl): ?string
    {
        if (blank($publicUrl) || ! preg_match('/\.(jpe?g|png)$/i', $publicUrl)) {
            return null;
        }

        if (array_key_exists($publicUrl, self::$memo)) {
            return self::$memo[$publicUrl];
        }

        // Strip origin + the public-disk prefix to get the disk-relative path.
        $path = parse_url($publicUrl, PHP_URL_PATH) ?: '';

        if (! str_starts_with($path, '/storage/')) {
            return self::$memo[$publicUrl] = null;
        }

        $relative = preg_replace('/\.(jpe?g|png)$/i', '.webp', substr($path, strlen('/storage/')));

        return self::$memo[$publicUrl] = Storage::disk('public')->exists($relative)
            ? Storage::disk('public')->url($relative)
            : null;
    }
}
