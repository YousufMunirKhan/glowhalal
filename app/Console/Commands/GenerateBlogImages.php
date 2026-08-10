<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Alignment;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Generates a cover image for every published blog post that has none, using
 * the FREE Pollinations image API (no key, no quota), then stamps the Glow
 * Halal wordmark watermark (public/images/logo-watermark.png) on every image
 * before saving — per the owner's rule that every generated image must carry
 * the brand logo.
 *
 * HONESTY GUARD: prompts are generic, aesthetic and thematic (herbs, oils,
 * flat-lays). They never render a fake branded product bottle, medical
 * imagery, wounds, or any text/claims inside the image.
 */
class GenerateBlogImages extends Command
{
    protected $signature = 'blog:generate-images
                            {--force : Regenerate even when a cover already exists}
                            {--only= : Limit to a single post slug}';

    protected $description = 'Generate watermarked AI cover images for blog posts (free Pollinations API + brand logo)';

    public function handle(): int
    {
        $query = BlogPost::query()->whereNotNull('published_at');

        if ($slug = $this->option('only')) {
            $query->where('slug', $slug);
        }

        if (! $this->option('force')) {
            $query->whereNull('cover_image_path');
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->info('Nothing to do — every published post already has a cover image.');

            return self::SUCCESS;
        }

        $logoPath = public_path('images/logo-watermark.png');

        if (! is_file($logoPath)) {
            $this->error("Watermark logo missing: {$logoPath} — refusing to generate unbranded images.");

            return self::FAILURE;
        }

        $manager = new ImageManager(new GdDriver());
        $done = 0;

        foreach ($posts as $post) {
            $prompt = $this->promptFor($post);
            $this->line("• {$post->slug}");

            try {
                // Gemini first (better quality; free tier ~100/day covers the
                // store's ~2 posts/day many times over), Pollinations fallback
                // (keyless, uncapped) so generation never simply stops.
                $binary = $this->generateViaGemini($prompt)
                    ?? $this->generateViaPollinations($prompt, $post->slug);

                if ($binary === null) {
                    $this->warn('  skipped — generation failed on both providers');

                    continue;
                }

                $image = $manager->decodeBinary($binary);
                $logo = $manager->decodePath($logoPath)->scaleDown(width: 200);
                // Brand watermark bottom-right on EVERY image — non-negotiable.
                $image->insert($logo, x: 28, y: 24, alignment: Alignment::BOTTOM_RIGHT, transparency: 0.9);

                $relative = 'blog/'.$post->slug.'.jpg';
                Storage::disk('public')->put($relative, (string) $image->encode(new JpegEncoder(quality: 82)));

                $post->cover_image_path = $relative;
                $post->cover_image_alt = $post->cover_image_alt ?: $this->altFor($post);
                $post->save();

                $this->info('  saved → storage/'.$relative);
                $done++;
            } catch (\Throwable $e) {
                $this->warn('  skipped — '.$e->getMessage());
            }
        }

        $this->info("Done: {$done}/{$posts->count()} cover image(s) generated & watermarked.");

        return self::SUCCESS;
    }

    /**
     * Thematic, honest prompt per post. Generic editorial imagery only —
     * no fake branded bottles, no wounds/medical scenes, no in-image text.
     */
    private function promptFor(BlogPost $post): string
    {
        $slug = $post->slug;

        $theme = match (true) {
            str_contains($slug, 'jodon') || str_contains($slug, 'joint')
                => 'warm herbal massage oil being poured into an open palm, spa towels, soft focus',
            str_contains($slug, 'baalon') || str_contains($slug, 'hair') || str_contains($slug, 'champi')
                => 'traditional hair oiling flat lay, wooden comb, small plain amber glass oil bottle, jasmine flowers',
            str_contains($slug, 'jalne') || str_contains($slug, 'cuts') || str_contains($slug, 'burns')
                => 'gentle skincare still life, aloe vera leaves, soft cotton pads, calm neutral tones',
            str_contains($slug, 'price') || str_contains($slug, 'kahan')
                => 'plain unlabeled amber glass bottle of golden herbal oil, sesame seeds scattered, flat lay',
            str_contains($slug, 'behtareen') || str_contains($slug, 'best-halal')
                => 'assorted plain glass bottles of natural carrier oils with fresh herbs, elegant flat lay',
            default
                => 'golden herbal oil in a plain glass bottle with sesame seeds and green herbs, editorial still life',
        };

        return $theme.', luxury minimal editorial product photography, warm golden light,'
            .' cream ivory background, high detail, no text, no labels, no logos, no people, no watermark';
    }

    private function altFor(BlogPost $post): string
    {
        return $post->title.' — Glow Halal';
    }

    /**
     * Google AI Studio (Gemini) image generation. Returns raw image bytes, or
     * null when no API key is configured / the call fails — the caller then
     * falls back to Pollinations.
     */
    private function generateViaGemini(string $prompt): ?string
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            return null;
        }

        $model = config('services.gemini.image_model', 'gemini-2.5-flash-image');

        try {
            $response = Http::timeout(120)
                ->withHeaders(['x-goog-api-key' => $key])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['parts' => [['text' => $prompt.' Landscape 3:2 aspect ratio.']]]],
                ]);

            if (! $response->successful()) {
                $this->warn('  gemini: HTTP '.$response->status().' — falling back');

                return null;
            }

            foreach (($response->json('candidates.0.content.parts') ?? []) as $part) {
                $data = $part['inlineData']['data'] ?? $part['inline_data']['data'] ?? null;

                if ($data !== null) {
                    $binary = base64_decode($data, true);

                    if ($binary !== false && strlen($binary) > 10_000) {
                        $this->line('  via Gemini');

                        return $binary;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->warn('  gemini: '.$e->getMessage().' — falling back');
        }

        return null;
    }

    /** Keyless, uncapped fallback provider. */
    private function generateViaPollinations(string $prompt, string $slug): ?string
    {
        try {
            // Deterministic seed per slug so re-runs stay stable.
            $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($prompt)
                .'?width=1200&height=800&nologo=true&seed='.(crc32($slug) % 99999);

            $response = Http::timeout(120)->retry(2, 3000)->get($url);

            if ($response->successful() && strlen($response->body()) > 10_000) {
                $this->line('  via Pollinations');

                return $response->body();
            }
        } catch (\Throwable) {
            // fall through
        }

        return null;
    }
}
