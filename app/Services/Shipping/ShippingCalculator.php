<?php

namespace App\Services\Shipping;

use App\Enums\ShippingRateType;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Zone/rate resolution for the storefront.
 *
 * The cart page has no destination yet, so it quotes the fallback zone and
 * flags the quote as an estimate. Checkout re-quotes the moment a city is
 * chosen, and PlaceOrderAction re-quotes again server-side before the order is
 * written — the browser's number is display state, never an input to what is
 * charged.
 */
final class ShippingCalculator
{
    private ?Collection $zones = null;

    public function quote(Money $subtotal, ?string $city = null, ?string $province = null): ShippingQuote
    {
        $zone = $this->zoneFor($city, $province);
        $rate = $zone
            ? $zone->rates->firstWhere('is_active', true)
            : null;

        if (! $rate) {
            return new ShippingQuote(
                amount: Money::zero(),
                codSurcharge: Money::zero(),
                label: 'Delivery charges confirmed by SMS',
                isEstimate: true,
            );
        }

        return new ShippingQuote(
            amount: $this->amountFor($rate, $subtotal),
            codSurcharge: $rate->cod_surcharge_amount ?? Money::zero(),
            label: $rate->name,
            rate: $rate,
            minDays: $rate->min_delivery_days,
            maxDays: $rate->max_delivery_days,
            isEstimate: $city === null && $province === null,
        );
    }

    private function amountFor(ShippingRate $rate, Money $subtotal): Money
    {
        $base = $rate->amount ?? Money::zero();

        return match ($rate->type) {
            ShippingRateType::FreeOver => $rate->free_over_subtotal_amount !== null
                && $subtotal->minorUnits >= $rate->free_over_subtotal_amount->minorUnits
                    ? Money::zero()
                    : $base,
            default => $base,
        };
    }

    public function zoneFor(?string $city = null, ?string $province = null): ?ShippingZone
    {
        $zones = $this->zones();

        if ($city !== null && trim($city) !== '') {
            $needle = mb_strtolower(trim($city));

            $match = $zones->first(fn (ShippingZone $z) => collect($z->cities ?? [])
                ->contains(fn ($c) => mb_strtolower(trim((string) $c)) === $needle));

            if ($match) {
                return $match;
            }
        }

        if ($province !== null && trim($province) !== '') {
            $needle = mb_strtolower(trim($province));

            $match = $zones->first(fn (ShippingZone $z) => collect($z->provinces ?? [])
                ->contains(fn ($p) => mb_strtolower(trim((string) $p)) === $needle));

            if ($match) {
                return $match;
            }
        }

        return $zones->firstWhere('is_fallback', true) ?? $zones->last();
    }

    private function zones(): Collection
    {
        return $this->zones ??= ShippingZone::query()
            ->where('is_active', true)
            ->with(['rates' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('is_fallback')          // non-fallback zones first
            ->orderBy('position')
            ->get();
    }
}
