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
                            {--only= : Limit to a single post slug}
                            {--salt= : Vary the generation seed to get a different image}';

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

                // Content-hashed filename so a regenerated cover always gets a
                // NEW URL — busting the CDN/browser cache of the old image.
                $relative = 'blog/'.$post->slug.'-'.substr(md5($binary), 0, 6).'.jpg';
                Storage::disk('public')->put($relative, (string) $image->encode(new JpegEncoder(quality: 82)));

                // Remove the superseded cover file (different name) if any.
                $old = $post->cover_image_path;
                if ($old && $old !== $relative && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }

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
                => 'plain unlabeled amber glass bottle of golden massage oil beside a stack of smooth dark grey spa stones and a rolled beige towel on light wood',
            str_contains($slug, 'baalon') || str_contains($slug, 'hair') || str_contains($slug, 'champi')
                => 'completely unlabeled plain amber glass bottle of golden hair oil on cream linen, golden oil dripping from a glass rod, fresh white jasmine flowers scattered',
            str_contains($slug, 'jalne') || str_contains($slug, 'cuts') || str_contains($slug, 'burns')
                => 'plain unlabeled amber glass bottle of golden herbal oil on white marble with folded soft white cotton cloths beside it, blurred green leaves far in background',
            str_contains($slug, 'price') || str_contains($slug, 'kahan')
                => 'single plain amber glass apothecary bottle of golden oil, warm sunlight streaming across, sesame seeds in a tiny wooden spoon beside it',
            str_contains($slug, 'behtareen') || str_contains($slug, 'best-halal')
                => 'three small clear glass bottles filled with golden yellow oil in slightly different depths of amber, cork stoppers, fresh rosemary sprigs, bright airy marble surface',
            default
                => 'plain amber glass bottle of golden herbal oil with a wooden bowl of sesame seeds and fresh green herbs, silk cloth beneath',
        };

        return 'Professional commercial product photography, '.$theme
            .', shot on a full-frame camera with 85mm lens at f/2.8, shallow depth of field,'
            .' soft diffused window light from the left, warm golden hour tones,'
            .' cream ivory seamless background, styled like a luxury skincare magazine editorial,'
            .' photorealistic, ultra sharp focus, 8k detail.'
            .' Absolutely no text, no words, no letters, no labels on bottles, no logos, no people, no hands.';
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
            // Deterministic seed per slug (vary with --salt for a fresh take).
            $seed = crc32($slug.(string) $this->option('salt')) % 99999;
            $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($prompt)
                .'?width=1216&height=832&nologo=true&enhance=true&model=flux&seed='.$seed;

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
