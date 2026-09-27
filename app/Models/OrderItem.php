<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Snapshot of what was ordered (name, model and price at order time).
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $name
 * @property string $model_number
 * @property string $unit_price
 * @property int $qty
 * @property string $line_total
 */
#[Fillable(['product_id', 'name', 'model_number', 'unit_price', 'qty', 'line_total'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'line_total' => 'decimal:2', 'qty' => 'integer'];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
