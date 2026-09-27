<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * NAP and business facts. Anything unconfirmed stays empty/false and is not rendered.
 */
class BusinessSettings extends Settings
{
    public ?string $email;

    /** The email is rendered only once the owner confirms it. */
    public bool $email_confirmed;

    /** Only true when the owner confirms round-the-clock service. */
    public bool $is_24_7;

    // Rows of {days: string[], opens: "HH:MM", closes: "HH:MM"}. No docblock type on purpose:
    // spatie/laravel-settings derives casts from @var/@phpstan-var tags and cannot parse array shapes.
    public array $opening_hours;

    /** Service-area business until the owner confirms a public location. */
    public bool $has_public_address;

    public ?string $street_address;

    public ?string $locality;

    public ?string $region;

    public ?string $postal_code;

    public ?float $latitude;

    public ?float $longitude;

    public ?string $gbp_url;

    public ?string $gbp_review_url;

    // Real, owned social profile URLs only (list of strings).
    public array $same_as;

    public ?int $founding_year;

    public ?string $price_range;

    public static function group(): string
    {
        return 'business';
    }

    public function publicEmail(): ?string
    {
        return $this->email_confirmed && filled($this->email) ? $this->email : null;
    }

    public function publicAddressIsKnown(): bool
    {
        return $this->has_public_address && filled($this->street_address) && filled($this->locality);
    }
}
