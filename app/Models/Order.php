<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $number
 * @property string $name
 * @property string $phone
 * @property int|null $area_id
 * @property string|null $area_text
 * @property string $address
 * @property string|null $notes
 * @property string $subtotal
 * @property string $shipping_fee
 * @property string $total
 * @property string $payment_method
 * @property string $payment_status
 * @property string $status
 * @property array<string, string>|null $utm
 * @property Carbon|null $created_at
 * @property-read Area|null $area
 * @property-read Collection<int, OrderItem> $items
 */
#[Fillable(['number', 'name', 'phone', 'area_id', 'area_text', 'address', 'notes', 'subtotal', 'shipping_fee', 'total', 'payment_method', 'payment_status', 'status', 'utm'])]
class Order extends Model
{
    public const STATUSES = ['new' => 'جديد', 'confirmed' => 'اتأكد', 'delivered' => 'اتسلّم', 'cancelled' => 'اتلغى'];

    public const PAYMENT_STATUSES = ['pending' => 'لم يُدفع', 'paid' => 'مدفوع', 'failed' => 'فشل'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'shipping_fee' => 'decimal:2', 'total' => 'decimal:2', 'utm' => 'array'];
    }

    public static function nextNumber(): string
    {
        do {
            $number = 'NK-'.now()->format('ymd').'-'.random_int(1000, 9999);
        } while (self::query()->where('number', $number)->exists());

        return $number;
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
