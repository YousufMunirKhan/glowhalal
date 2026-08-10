<?php

namespace App\Casts;

use App\Support\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<Money, Money|int|null> */
final class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        return $value === null ? null : new Money((int) $value, $attributes['currency'] ?? 'PKR');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return match (true) {
            $value === null => null,
            $value instanceof Money => $value->minorUnits,
            is_int($value) => $value,
            // Numeric strings arrive from HTTP form payloads; they are minor units by contract.
            is_string($value) && ctype_digit($value) => (int) $value,
            default => throw new \InvalidArgumentException(
                'Refusing to cast '.get_debug_type($value).' to money. Pass Money or integer paisa.'
            ),
        };
    }
}
