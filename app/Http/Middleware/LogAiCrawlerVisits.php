<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * First-party telemetry for AI crawlers — the ONLY measurement we have.
 *
 * Hostinger shared hosting exposes no web-server access logs over SSH (CageFS
 * hides them; hPanel shows only aggregates), so "is GPTBot actually crawling
 * us?" is unanswerable without recording it ourselves. Every AEO asset on this
 * site — llms.txt, answer boxes, schema — is aimed at these crawlers, and this
 * log is how we find out whether any of them ever arrives, which URLs it
 * reads, and whether that changes after each piece of AEO work ships.
 *
 * Matching is one pre-built regex against the User-Agent, so the cost on the
 * 99.9% of requests that are not AI bots is a single preg_match. Hits go to
 * the dedicated `ai_crawlers` daily channel (60-day retention) as one
 * structured line each: bot, method, path, status, and the verbatim UA
 * (variants matter — "GPTBot/1.2" vs "ChatGPT-User/1.0" are different
 * behaviours: crawl-for-training vs live-answer fetch).
 *
 * Read it with:  grep '"bot":"GPTBot"' storage/logs/ai-crawlers-*.log
 * Count a week:  cat storage/logs/ai-crawlers-2026-08-*.log | wc -l
 *
 * The list covers answer engines (crawl + live-fetch UAs), the search bots
 * that feed them, and the big training crawlers. Ordinary Googlebot is
 * EXCLUDED on purpose: it hits constantly, Search Console already reports it,
 * and logging it would bury the signal this file exists to expose.
 */
class LogAiCrawlerVisits
{
    /**
     * Bot name (capture group used for the log label) => matched anywhere in
     * the UA, case-insensitive. Kept as alternation in one regex below.
     */
    private const BOT_PATTERN = '/(GPTBot|OAI-SearchBot|ChatGPT-User|ClaudeBot|Claude-Web|Claude-User|Claude-SearchBot|anthropic-ai|PerplexityBot|Perplexity-User|Google-Extended|GoogleOther|bingbot|DuckDuckBot|DuckAssistBot|Amazonbot|meta-externalagent|meta-externalfetcher|FacebookBot|Bytespider|CCBot|cohere-ai|YouBot|MistralAI|Applebot-Extended|Applebot)/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $ua = (string) $request->userAgent();

        if ($ua !== '' && preg_match(self::BOT_PATTERN, $ua, $m)) {
            // Never let telemetry break a page for the very bot it measures.
            try {
                Log::channel('ai_crawlers')->info('ai-crawler', [
                    'bot' => $m[1],
                    'method' => $request->getMethod(),
                    'path' => '/'.ltrim($request->path(), '/'),
                    'status' => $response->getStatusCode(),
                    'ua' => mb_substr($ua, 0, 300),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $response;
    }
}
