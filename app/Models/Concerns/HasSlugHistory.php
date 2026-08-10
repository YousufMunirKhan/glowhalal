<?php

namespace App\Models\Concerns;

use App\Models\SlugHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSlugHistory
{
    public function slugHistories(): MorphMany
    {
        return $this->morphMany(SlugHistory::class, 'sluggable');
    }

    /**
     * Record the superseded slug so §7.2's redirect layer can 301 the old URL.
     * Called from the model observers on a deliberate slug change.
     */
    public function recordSlugHistory(?string $previousSlug = null): void
    {
        $previousSlug ??= $this->getOriginal('slug');

        if (blank($previousSlug) || $previousSlug === $this->slug) {
            return;
        }

        $this->slugHistories()->firstOrCreate(
            ['slug' => $previousSlug],
            ['model_type_key' => static::slugHistoryTypeKey()],
        );
    }

    /** Stable, short key used by the redirect resolver to route a historical slug. */
    public static function slugHistoryTypeKey(): string
    {
        return str(class_basename(static::class))->snake()->toString();
    }
}
