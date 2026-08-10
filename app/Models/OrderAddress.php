<?php

namespace App\Models;

use App\Enums\PakistanProvince;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['province' => PakistanProvince::class];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function singleLine(): string
    {
        return collect([$this->line_1, $this->line_2, $this->area, $this->city, $this->province?->getLabel()])
            ->filter()
            ->implode(', ');
    }
}
