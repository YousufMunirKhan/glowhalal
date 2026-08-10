<?php

namespace App\Models;

use App\Enums\AlcoholStatus;
use App\Enums\HalalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductHalalProfile extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'overall_status' => HalalStatus::class,
            'alcohol_status' => AlcoholStatus::class,
            'is_certified' => 'boolean',
            'is_self_declared' => 'boolean',
            'is_wudu_friendly' => 'boolean',
            'is_vegan' => 'boolean',
            'is_cruelty_free' => 'boolean',
            'shared_facility_warning' => 'boolean',
            'last_reviewed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
