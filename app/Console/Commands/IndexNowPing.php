<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * IndexNow ping — tells Bing (and everything built on its index, including
 * Copilot and ChatGPT search) that our URLs changed, so a brand-new domain
 * gets crawled in hours instead of weeks.
 *
 * The protocol needs a key file at /{key}.txt on the host; this command
 * creates it on first run. Scheduled weekly, and safe to run after any
 * publish (`php artisan indexnow:ping`).
 */
class IndexNowPing extends Command
{
    protected $signature = 'indexnow:ping';

    protected $description = 'Submit all public URLs to IndexNow (Bing/Copilot fast indexing)';

    public function handle(): int
    {
        // Deterministic per-site key (no secret value — the protocol just
        // requires that the key file be served from our own host).
        $key = substr(hash('sha256', 'glowhalal-indexnow-'.config('app.url')), 0, 32);
        $keyFile = public_path($key.'.txt');

        if (! is_file($keyFile)) {
            file_put_contents($keyFile, $key);
            $this->info("key file created: /{$key}.txt");
        }

        $urls = collect([url('/'), url('/shop'), url('/blog'), url('/ur-roman/blog'),
            url('/halal-ingredients'), url('/what-we-never-use'), url('/about'), url('/contact')]);

        Product::query()->where('status', 'active')->whereNotNull('published_at')
            ->each(fn ($p) => $urls->push(url('/products/'.$p->slug)));

        BlogPost::query()->published()
            ->each(fn ($p) => $urls->push(url(($p->locale === 'ur-Latn' ? '/ur-roman' : '').'/blog/'.$p->slug)));

        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'glowhalal.com';

        $response = Http::timeout(30)->post('https://api.indexnow.org/indexnow', [
            'host' => $host,
            'key' => $key,
            'keyLocation' => url('/'.$key.'.txt'),
            'urlList' => $urls->unique()->values()->all(),
        ]);

        $this->info('IndexNow: HTTP '.$response->status().' — '.$urls->unique()->count().' URLs submitted.');

        return $response->successful() || $response->status() === 202 ? self::SUCCESS : self::FAILURE;
    }
}
