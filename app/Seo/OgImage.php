<?php

namespace App\Seo;

use Illuminate\Support\Facades\Storage;

/**
 * Deterministic OG image per (title, kicker, template version), rendered once on the public disk.
 */
class OgImage
{
    public function __construct(private readonly OgImageRenderer $renderer) {}

    public function url(string $title, ?string $kicker = null): string
    {
        $path = 'og/'.sha1(OgImageRenderer::VERSION.'|'.$title.'|'.$kicker).'.jpg';
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, $this->renderer->render($title, $kicker));
        }

        // Absolute even when the disk URL is relative (WhatsApp/Facebook need absolute og:image).
        return url($disk->url($path));
    }
}
