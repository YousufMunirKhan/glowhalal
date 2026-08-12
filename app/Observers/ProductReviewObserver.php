<?php

namespace App\Observers;

use App\Models\ProductReview;

/**
 * Keeps the cached aggregates on `products` (reviews_count, reviews_average) in
 * sync with the APPROVED reviews — these feed both the on-page "4.8 · N reviews"
 * line and the Product `aggregateRating` JSON-LD, so they must never drift.
 *
 * Runs on every save (create, and status changes like pending → approved in the
 * admin) and on delete. saveQuietly() writes the product without firing its own
 * model events, so there is no observer loop.
 */
class ProductReviewObserver
{
    public function saved(ProductReview $review): void
    {
        $this->recount($review);
    }

    public function deleted(ProductReview $review): void
    {
        $this->recount($review);
    }

    private function recount(ProductReview $review): void
    {
        $product = $review->product()->first();

        if (! $product) {
            return;
        }

        $approved = $product->reviews()->approved();

        $product->forceFill([
            'reviews_count' => (clone $approved)->count(),
            'reviews_average' => round((float) (clone $approved)->avg('rating'), 2),
        ])->saveQuietly();
    }
}
