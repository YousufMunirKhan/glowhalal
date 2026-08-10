<?php

namespace App\Settings;

use App\Support\Money;
use Spatie\LaravelSettings\Settings;

/**
 * Everything on the storefront that is a business fact rather than a design
 * decision: who to contact, who the founder is, what the delivery promise is.
 *
 * Nothing here ships with an invented value. Contact details, the founder story
 * and the address all default to null so the owner fills them in — a blank field
 * in the admin is honest, a plausible-looking placeholder that reaches production
 * is not.
 *
 * Read it anywhere with `app(StoreSettings::class)`, or in Blade via the
 * `$store` variable shared by AppServiceProvider.
 */
class StoreSettings extends Settings
{
    // ---- Contact -------------------------------------------------------------
    public ?string $contact_email;

    public ?string $contact_phone;

    public ?string $whatsapp_number;

    public ?string $address_line;

    public ?string $city;

    public ?string $postal_code;

    public ?string $opening_hours;

    // ---- Brand / about -------------------------------------------------------
    public ?string $founder_name;

    public ?string $founder_title;

    public ?string $founder_bio;

    public ?string $founder_photo_path;

    public ?string $brand_story;

    // ---- Storefront copy -----------------------------------------------------
    public bool $announcement_enabled;

    public ?string $announcement_text;

    public ?string $announcement_url;

    public ?string $hero_heading;

    public ?string $hero_subheading;

    public ?string $hero_cta_label;

    public ?string $hero_cta_url;

    /** Integer paisa, or null when there is no free-delivery offer. */
    public ?int $free_delivery_threshold_amount;

    // ---- Delivery ------------------------------------------------------------
    /**
     * Courier names, e.g. ["TCS", "Leopards"].
     *
     * Deliberately left as a bare `array` with no generic annotation. Spatie
     * builds a SettingsCast from each property's docblock type; `array{...}`
     * shapes crash phpdocumentor's TypeResolver, and nested generics such as
     * `array<int, array<string, string>>` resolve to a null inner cast and throw.
     * Plain `array` is the only form that round-trips safely here.
     */
    public array $couriers;

    /** Rows of ['zone' => ..., 'estimate' => ...]. See the note on $couriers. */
    public array $delivery_estimates;

    public bool $cod_enabled;

    /** Integer paisa. */
    public ?int $cod_fee_amount;

    public ?string $delivery_note;

    public ?string $returns_policy_summary;

    // ---- Social --------------------------------------------------------------
    public ?string $instagram_url;

    public ?string $facebook_url;

    public ?string $whatsapp_url;

    public ?string $tiktok_url;

    public ?string $youtube_url;

    public static function group(): string
    {
        return 'store';
    }

    /**
     * Money columns are stored as integer paisa so they never round.
     * These accessors hand callers a Money rather than a bare int.
     */
    public function freeDeliveryThreshold(): ?Money
    {
        return $this->free_delivery_threshold_amount === null
            ? null
            : new Money($this->free_delivery_threshold_amount);
    }

    public function codFee(): ?Money
    {
        return $this->cod_fee_amount === null
            ? null
            : new Money($this->cod_fee_amount);
    }

    public function hasFreeDeliveryOffer(): bool
    {
        return $this->free_delivery_threshold_amount !== null
            && $this->free_delivery_threshold_amount > 0;
    }

    /** True only when the bar is switched on AND actually has copy in it. */
    public function showsAnnouncement(): bool
    {
        return $this->announcement_enabled && filled($this->announcement_text);
    }

    /** True only when enough of the founder block is filled in to render it. */
    public function hasFounder(): bool
    {
        return filled($this->founder_name) && filled($this->founder_bio);
    }

    /** `wa.me` link built from the number, unless an explicit URL was set. */
    public function whatsappLink(?string $message = null): ?string
    {
        if (filled($this->whatsapp_url)) {
            return $this->whatsapp_url;
        }

        if (blank($this->whatsapp_number)) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $this->whatsapp_number);

        return 'https://wa.me/'.$number
            .($message ? '?text='.urlencode($message) : '');
    }

    /** Single-line postal address, skipping whatever has not been filled in. */
    public function formattedAddress(): ?string
    {
        $line = collect([$this->address_line, $this->city, $this->postal_code])
            ->filter()
            ->implode(', ');

        return $line ?: null;
    }

    /** @return array<string, string> Only the social links that are actually set. */
    public function socialLinks(): array
    {
        return collect([
            'instagram' => $this->instagram_url,
            'facebook' => $this->facebook_url,
            'tiktok' => $this->tiktok_url,
            'youtube' => $this->youtube_url,
            'whatsapp' => $this->whatsappLink(),
        ])->filter()->all();
    }
}
