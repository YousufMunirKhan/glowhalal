<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Minimal X (Twitter) API v2 client — OAuth 1.0a user context, hand-signed.
 *
 * Deliberately dependency-free: the server is shared hosting where running
 * composer is its own deployment step, and our whole surface is two endpoints
 * (POST /2/tweets, GET /2/users/me). OAuth 1.0a signing for those is ~40 lines.
 *
 * Signing notes that are easy to get wrong:
 *  - With a JSON body the signature base string contains ONLY the oauth_*
 *    params (and query params, of which we have none) — the body is excluded
 *    because the content type is not application/x-www-form-urlencoded.
 *  - Percent-encoding must be RFC 3986; PHP's rawurlencode() is exactly that.
 *  - The HMAC key is consumerSecret&tokenSecret, both percent-encoded.
 *
 * Scope: single tweets, text only. Media upload and threads are a later phase;
 * captions written for X in this store are single-post length by design.
 */
class XApiClient
{
    private const API = 'https://api.x.com';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly string $accessToken,
        private readonly string $accessTokenSecret,
    ) {}

    public static function fromConfig(): ?self
    {
        $cfg = config('services.x');

        if (blank($cfg['api_key'] ?? null) || blank($cfg['api_secret'] ?? null)
            || blank($cfg['access_token'] ?? null) || blank($cfg['access_token_secret'] ?? null)) {
            return null;   // not configured — callers treat X as manual-only
        }

        return new self($cfg['api_key'], $cfg['api_secret'], $cfg['access_token'], $cfg['access_token_secret']);
    }

    /**
     * Publish a single text tweet. Returns the created tweet id.
     *
     * @throws RuntimeException with X's own error text on any failure — the
     *         caller decides whether that pauses the queue or just this post.
     */
    public function postTweet(string $text): string
    {
        $url = self::API.'/2/tweets';

        $response = Http::withHeaders(['Authorization' => $this->oauthHeader('POST', $url)])
            ->asJson()
            ->timeout(30)
            ->post($url, ['text' => $text]);

        $id = $response->json('data.id');

        if (! $response->successful() || blank($id)) {
            throw new RuntimeException(sprintf(
                'X API tweet failed (HTTP %d): %s',
                $response->status(),
                $response->json('detail') ?? $response->json('errors.0.message') ?? Str::limit($response->body(), 300),
            ));
        }

        return (string) $id;
    }

    /**
     * The authenticated account (id, name, username). Cheap credentials check —
     * X's free tier rate-limits this endpoint hard, so call it on demand
     * (social:x-verify), never on a schedule.
     *
     * @return array{id: string, name: string, username: string}
     */
    public function me(): array
    {
        $url = self::API.'/2/users/me';

        $response = Http::withHeaders(['Authorization' => $this->oauthHeader('GET', $url)])
            ->timeout(30)
            ->get($url);

        $data = $response->json('data');

        if (! $response->successful() || blank($data['id'] ?? null)) {
            throw new RuntimeException(sprintf(
                'X API credentials check failed (HTTP %d): %s',
                $response->status(),
                $response->json('detail') ?? Str::limit($response->body(), 300),
            ));
        }

        return $data;
    }

    private function oauthHeader(string $method, string $url): string
    {
        $oauth = [
            'oauth_consumer_key' => $this->apiKey,
            'oauth_nonce' => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        ];

        ksort($oauth);

        $paramString = implode('&', array_map(
            fn ($k, $v) => rawurlencode($k).'='.rawurlencode($v),
            array_keys($oauth),
            $oauth,
        ));

        $base = strtoupper($method).'&'.rawurlencode($url).'&'.rawurlencode($paramString);
        $key = rawurlencode($this->apiSecret).'&'.rawurlencode($this->accessTokenSecret);

        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base, $key, true));

        return 'OAuth '.implode(', ', array_map(
            fn ($k, $v) => rawurlencode($k).'="'.rawurlencode($v).'"',
            array_keys($oauth),
            $oauth,
        ));
    }
}
