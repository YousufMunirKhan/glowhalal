<?php

namespace App\Models;

use App\Observers\ProductReviewObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProductReviewObserver::class])]
class ProductReview extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'image_paths' => 'array',
            'rating' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    #[Scope]
    protected function approved(Builder $q): void
    {
        $q->where('status', 'approved');
    }
}
