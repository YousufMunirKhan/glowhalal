<?php

namespace App\Support;

use Livewire\Wireable;

/**
 * Implements Wireable because money-cast attributes end up in Filament's form
 * state, and Livewire refuses to serialise an unknown object. Declaring the
 * round trip once here is better than stripping Money out of every form payload.
 */
final readonly class Money implements \JsonSerializable, \Stringable, Wireable
{
    public function __construct(public int $minorUnits, public string $currency = 'PKR') {}

    public function toLivewire(): array
    {
        return ['minorUnits' => $this->minorUnits, 'currency' => $this->currency];
    }

    public static function fromLivewire($value): self
    {
        return new self((int) ($value['minorUnits'] ?? 0), (string) ($value['currency'] ?? 'PKR'));
    }

    public static function fromRupees(int|float|string $rupees, string $currency = 'PKR'): self
    {
        return new self((int) round(((float) $rupees) * 100), $currency);
    }

    public static function zero(string $currency = 'PKR'): self
    {
        return new self(0, $currency);
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(max(0, $this->minorUnits - $other->minorUnits), $this->currency);
    }

    public function times(int $factor): self
    {
        return new self($this->minorUnits * $factor, $this->currency);
    }

    /** Basis points: 1500 = 15.00%. Rounds half-up at the single point where precision is lost. */
    public function percentage(int $basisPoints): self
    {
        return new self((int) round($this->minorUnits * $basisPoints / 10000), $this->currency);
    }

    public function isZero(): bool
    {
        return $this->minorUnits === 0;
    }

    public function toRupees(): float
    {
        return $this->minorUnits / 100;
    }

    public function format(bool $withSymbol = true): string
    {
        $formatted = number_format($this->minorUnits / 100, 0, '.', ',');   // PKR is quoted in whole rupees

        return $withSymbol ? "Rs {$formatted}" : $formatted;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function jsonSerialize(): array
    {
        return ['minor' => $this->minorUnits, 'currency' => $this->currency, 'formatted' => $this->format()];
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("Currency mismatch: {$this->currency} vs {$other->currency}");
        }
    }
}
