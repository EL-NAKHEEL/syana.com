<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Header, menu, footer and floating buttons — editable from admin › الإعدادات › الهيدر والفوتر.
 * The phone number itself stays in config (approved decision Q4).
 */
class LayoutSettings extends Settings
{
    public string $topbar_label;

    public bool $header_cta_visible;

    public string $header_cta_label;

    /** Prefilled WhatsApp message for the header button. */
    public string $header_cta_message;

    /** Main menu: list of {key, label, visible}, in display order (keys from Navigation::ITEMS). */
    public array $menu;

    public string $footer_about;

    public bool $footer_show_links;

    public bool $footer_show_areas;

    public bool $float_whatsapp;

    public bool $float_call;

    public static function group(): string
    {
        return 'layout';
    }
}
