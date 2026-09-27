<?php

namespace App\Support;

/**
 * The only inline scripts on public pages. They are allow-listed in the CSP by hash, so the exact
 * string rendered in the layout must come from here.
 */
class InlineScripts
{
    /**
     * Runs before first paint: marks JS support so progressive enhancements (the mobile menu) can hide
     * content only when JS is there to reveal it.
     */
    public const HEAD = "document.documentElement.classList.replace('no-js','js')";

    public static function cspHashes(): string
    {
        return "'sha256-".base64_encode(hash('sha256', self::HEAD, true))."'";
    }
}
