<?php

namespace App\Seo\Schema;

use App\Catalog\Catalog;
use App\Models\Product;

/**
 * Product + Offer JSON-LD (PLAN.md §6.2). On sale: price is the current price and the original price is a
 * StrikethroughPrice UnitPriceSpecification. No aggregateRating until genuine product reviews show on the page
 * (P4). shippingDetails / hasMerchantReturnPolicy are added only once the owner confirms them (Q8).
 */
class ProductSchema
{
    /**
     * @return array<string, mixed>
     */
    public function node(Product $product, string $url): array
    {
        $images = $product->media->where('collection_name', 'images')->map(fn ($m) => $m->getFullUrl('large'))->values()->all();

        $properties = array_values(array_filter([
            ['@type' => 'PropertyValue', 'name' => 'القدرة', 'value' => Catalog::hpLabel($product->hp), 'unitText' => 'حصان'],
            $product->btu ? ['@type' => 'PropertyValue', 'name' => 'BTU', 'value' => $product->btu] : null,
            ['@type' => 'PropertyValue', 'name' => 'إنفرتر', 'value' => $product->is_inverter ? 'نعم' : 'لا'],
            ['@type' => 'PropertyValue', 'name' => 'التبريد', 'value' => Catalog::COOLING[$product->cooling] ?? $product->cooling],
            ['@type' => 'PropertyValue', 'name' => 'النوع', 'value' => Catalog::TYPES[$product->type] ?? $product->type],
            $product->energy_class ? ['@type' => 'PropertyValue', 'name' => 'كفاءة الطاقة', 'value' => $product->energy_class] : null,
        ]));

        $offer = [
            '@type' => 'Offer',
            'url' => $url,
            'priceCurrency' => 'EGP',
            'price' => number_format($product->currentPrice(), 2, '.', ''),
            'availability' => Catalog::AVAILABILITY[$product->stock_status] ?? 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => SchemaGraph::ref(SchemaGraph::id('organization')),
        ];

        if ($product->onSale()) {
            $offer['priceSpecification'] = [
                ['@type' => 'UnitPriceSpecification', 'price' => number_format($product->currentPrice(), 2, '.', ''), 'priceCurrency' => 'EGP'],
                ['@type' => 'UnitPriceSpecification', 'priceType' => 'https://schema.org/StrikethroughPrice', 'price' => number_format((float) $product->price, 2, '.', ''), 'priceCurrency' => 'EGP'],
            ];
            if ($product->sale_ends_at) {
                $offer['priceValidUntil'] = $product->sale_ends_at->toDateString();
            }
        }

        return array_filter([
            '@type' => 'Product',
            '@id' => SchemaGraph::pageId($url, 'product'),
            'name' => $product->descriptiveName(),
            'description' => $product->short_description ?: $product->descriptiveName(),
            'brand' => ['@type' => 'Brand', 'name' => $product->brand->name_en ?: $product->brand->name_ar],
            'sku' => $product->sku ?: $product->model_number,
            'mpn' => $product->model_number,
            'image' => $images ?: null,
            'additionalProperty' => $properties,
            'offers' => $offer,
            'mainEntityOfPage' => SchemaGraph::ref(SchemaGraph::pageId($url, 'webpage')),
        ], fn ($value) => $value !== null);
    }
}
