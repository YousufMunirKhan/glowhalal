<?php

namespace App\Filament\Forms;

use App\Support\Money;
use Filament\Forms\Components\TextInput;

/**
 * Money columns are integer paisa. Admins think in rupees. This is the single
 * place that translation happens, so no form has to remember to do it.
 *
 * Deliberately NOT using `->numeric()`: that installs Filament's
 * `NumberStateCast`, which calls `floatval()` on the raw state before any
 * `formatStateUsing` callback runs — and `floatval(Money)` is a fatal error.
 * `rule('numeric')` + `inputMode('decimal')` gives identical validation and
 * keyboard behaviour without touching the state.
 */
final class MoneyInput
{
    public static function make(string $name, ?string $label = null): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->prefix('Rs')
            ->inputMode('decimal')
            ->step('any')
            ->rule('numeric')
            ->rule('min:0')
            ->formatStateUsing(fn ($state) => self::toRupees($state))
            ->dehydrateStateUsing(fn ($state) => $state === null || $state === ''
                ? null
                : Money::fromRupees($state));
    }

    public static function toRupees(mixed $state): ?float
    {
        return match (true) {
            $state instanceof Money => $state->toRupees(),
            $state === null || $state === '' => null,
            default => ((float) $state) / 100,
        };
    }

    public static function format(mixed $state): string
    {
        return $state instanceof Money ? $state->format() : '—';
    }
}
