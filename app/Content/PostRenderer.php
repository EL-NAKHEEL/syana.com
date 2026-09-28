<?php

namespace App\Content;

use App\Models\Product;
use Illuminate\Support\HtmlString;

/**
 * Prepares a sanitized post body for display: anchors on H2/H3 for the auto table of contents, and
 * [product:slug] shortcodes → live product cards (dropped silently when the product is not live).
 */
class PostRenderer
{
    /**
     * @return array{html: HtmlString, toc: array<int, array{id: string, text: string, level: int}>}
     */
    public function render(string $body): array
    {
        $toc = [];
        $i = 0;

        $html = (string) preg_replace_callback('#<(h[23])([^>]*)>(.*?)</\1>#su', function (array $m) use (&$toc, &$i) {
            $id = 'section-'.(++$i);
            $toc[] = ['id' => $id, 'text' => trim(strip_tags($m[3])), 'level' => (int) substr($m[1], 1)];

            return '<'.$m[1].' id="'.$id.'">'.$m[3].'</'.$m[1].'>';
        }, $body);

        $html = (string) preg_replace_callback('#(?:<p>\s*)?\[product:([a-z0-9-]+)\](?:\s*</p>)?#u', function (array $m) {
            $product = Product::query()->live()->with(['brand', 'media'])->where('slug', $m[1])->first();

            return $product ? '<ul class="product-grid product-embed">'.view('components.store.product-card', ['product' => $product])->render().'</ul>' : '';
        }, $html);

        return ['html' => new HtmlString($html), 'toc' => $toc];
    }
}
