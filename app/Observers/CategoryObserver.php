<?php

namespace App\Observers;

use App\Exceptions\CategoryHasChildrenException;
use App\Jobs\RebuildCategorySubtreePaths;
use App\Models\Category;

class CategoryObserver
{
    public function saving(Category $category): void
    {
        if (! $category->isDirty('parent_id') && $category->exists) {
            return;
        }

        $parent = $category->parent_id ? Category::find($category->parent_id) : null;
        $category->path = $parent ? trim(($parent->path ? $parent->path.'/' : '').$parent->id, '/') : null;
        $category->depth = $parent ? $parent->depth + 1 : 0;
    }

    public function saved(Category $category): void
    {
        if ($category->wasChanged('path')) {
            // Re-materialise the subtree. Queue it — a deep move can touch many rows.
            RebuildCategorySubtreePaths::dispatch($category->id);
        }
    }

    public function updated(Category $category): void
    {
        if ($category->wasChanged('slug')) {
            $category->recordSlugHistory($category->getOriginal('slug'));
        }
    }

    public function deleting(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new CategoryHasChildrenException(
                "Re-parent or delete the {$category->children()->count()} child categories first."
            );
        }
    }
}
