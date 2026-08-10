<?php

namespace App\Jobs;

use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Re-materialises `path` and `depth` for every descendant of a moved category.
 * Queued because a deep move can touch many rows.
 */
class RebuildCategorySubtreePaths implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $categoryId) {}

    public function handle(): void
    {
        $root = Category::withTrashed()->find($this->categoryId);

        if (! $root) {
            return;
        }

        $this->rebuildChildrenOf($root);
    }

    private function rebuildChildrenOf(Category $parent): void
    {
        $path = trim(($parent->path ? $parent->path.'/' : '').$parent->id, '/');

        Category::withTrashed()
            ->where('parent_id', $parent->id)
            ->cursor()
            ->each(function (Category $child) use ($path, $parent) {
                $child->forceFill([
                    'path' => $path,
                    'depth' => (int) $parent->depth + 1,
                ])->saveQuietly();

                $this->rebuildChildrenOf($child);
            });
    }
}
