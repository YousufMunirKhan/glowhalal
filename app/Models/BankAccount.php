<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'position' => 'integer'];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('position');
    }
}
