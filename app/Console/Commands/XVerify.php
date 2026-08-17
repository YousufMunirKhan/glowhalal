<?php

namespace App\Console\Commands;

use App\Services\XApiClient;
use Illuminate\Console\Command;

/**
 * On-demand X credentials check: signs a GET /2/users/me and prints who we
 * are. Run it after pasting new keys into .env. Never scheduled — the free
 * tier rate-limits this endpoint to a handful of calls per day, so it is a
 * hand tool, not a health check.
 */
class XVerify extends Command
{
    protected $signature = 'social:x-verify';

    protected $description = 'Verify the X API credentials in .env by fetching the authenticated account';

    public function handle(): int
    {
        $x = XApiClient::fromConfig();

        if (! $x) {
            $this->error('X API keys are not set. Add X_API_KEY, X_API_SECRET, X_ACCESS_TOKEN, X_ACCESS_TOKEN_SECRET to .env, then: php artisan config:clear');

            return self::FAILURE;
        }

        try {
            $me = $x->me();
        } catch (\Throwable $e) {
            $this->error('X API check failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Connected as @%s (%s, id %s).', $me['username'], $me['name'], $me['id']));
        $this->line('Note: this proves the keys sign correctly. Write permission is only proven by the first real post.');

        return self::SUCCESS;
    }
}
