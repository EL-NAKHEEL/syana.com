<?php

namespace App\Commerce;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cookie;

/**
 * Session cart: product id → quantity. Prices are always recomputed from the database, never stored here.
 * A non-HttpOnly `cart_count` cookie lets cached (user-agnostic) pages show the cart badge client-side.
 */
class Cart
{
    public const MAX_QTY = 10;

    public function __construct(private readonly Session $session) {}

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        return (array) $this->session->get('cart', []);
    }

    public function add(Product $product, int $qty = 1): void
    {
        $items = $this->items();
        $items[$product->id] = min(self::MAX_QTY, ($items[$product->id] ?? 0) + max(1, $qty));
        $this->store($items);
    }

    public function update(int $productId, int $qty): void
    {
        $items = $this->items();

        if ($qty < 1) {
            unset($items[$productId]);
        } else {
            $items[$productId] = min(self::MAX_QTY, $qty);
        }

        $this->store($items);
    }

    public function clear(): void
    {
        $this->store([]);
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    /**
     * Orderable products in the cart with their quantities; items that went offline are dropped.
     *
     * @return array{lines: array<int, array{product: Product, qty: int, line_total: float}>, subtotal: float}
     */
    public function contents(): array
    {
        $items = $this->items();
        /** @var Collection<int, Product> $products */
        $products = Product::query()->live()->with(['brand', 'media'])->whereKey(array_keys($items))->get();

        $lines = [];
        $subtotal = 0.0;

        foreach ($products as $product) {
            if (! $product->canBeOrdered()) {
                continue;
            }
            $qty = $items[$product->id];
            $lineTotal = $product->currentPrice() * $qty;
            $lines[] = ['product' => $product, 'qty' => $qty, 'line_total' => $lineTotal];
            $subtotal += $lineTotal;
        }

        return ['lines' => $lines, 'subtotal' => $subtotal];
    }

    /**
     * @param  array<int, int>  $items
     */
    private function store(array $items): void
    {
        $this->session->put('cart', $items);
        Cookie::queue(Cookie::make('cart_count', (string) array_sum($items), 60 * 24 * 30, httpOnly: false));
    }
}
