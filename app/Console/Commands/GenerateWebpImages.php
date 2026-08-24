<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Creates a .webp sibling for every product image (GD, quality 82).
 *
 * Why: the homepage hero — the mobile LCP element — was serving a 66 KB
 * progressive JPEG to phones on Pakistani 4G. WebP halves that for identical
 * visual quality, and on this store's connections that is 150–300 ms of LCP.
 * The Blade side (<picture> in the hero) only offers the .webp when the
 * sibling actually exists, so this command is safe to run at any time and a
 * missing conversion just means the JPEG keeps serving.
 *
 * Idempotent + cheap: skips any image whose .webp is already newer than the
 * source. Re-run after uploading new product photos (or let the scheduler's
 * nightly run pick them up).
 */
class GenerateWebpImages extends Command
{
    protected $signature = 'images:webp {--dir=products : Directory under the public disk}';

    protected $description = 'Generate .webp siblings for product images (skips up-to-date ones)';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('GD webp support is missing on this PHP build.');

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $dir = trim((string) $this->option('dir'), '/');
        $made = 0;
        $skipped = 0;

        foreach ($disk->files($dir) as $file) {
            if (! preg_match('/\.(jpe?g|png)$/i', $file)) {
                continue;
            }

            $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);

            if ($disk->exists($webp) && $disk->lastModified($webp) >= $disk->lastModified($file)) {
                $skipped++;

                continue;
            }

            $src = $disk->path($file);
            $img = preg_match('/\.png$/i', $file)
                ? imagecreatefrompng($src)
                : imagecreatefromjpeg($src);

            if (! $img) {
                $this->warn("Could not read {$file} — skipped.");

                continue;
            }

            // PNGs may carry an alpha channel; keep it.
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);

            imagewebp($img, $disk->path($webp), 82);
            imagedestroy($img);
            $made++;

            $this->info(sprintf(
                '%s: %s KB -> %s KB',
                $webp,
                (int) round($disk->size($file) / 1024),
                (int) round($disk->size($webp) / 1024),
            ));
        }

        $this->info("Done: {$made} converted, {$skipped} already current.");

        return self::SUCCESS;
    }
}
