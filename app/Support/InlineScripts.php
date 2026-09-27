<?php

namespace App\Support;

/**
 * The only inline scripts on public pages. They are allow-listed in the CSP by hash, so the exact
 * string rendered in the layout must come from here.
 */
class InlineScripts
{
    /**
     * Runs before first paint: marks JS support and plays the louver intro once per session
     * (skipped under prefers-reduced-motion). The H1 is never hidden by it.
     */
    public const HEAD = "document.documentElement.classList.replace('no-js','js');try{if(!sessionStorage.getItem('nk-intro')&&!matchMedia('(prefers-reduced-motion: reduce)').matches){document.documentElement.classList.add('intro');sessionStorage.setItem('nk-intro','1')}}catch(e){}";

    public static function cspHashes(): string
    {
        return "'sha256-".base64_encode(hash('sha256', self::HEAD, true))."'";
    }
}
