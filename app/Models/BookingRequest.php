<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property int|null $area_id
 * @property string|null $area_text
 * @property int|null $service_id
 * @property string|null $ac_type
 * @property Carbon|null $preferred_date
 * @property string|null $notes
 * @property string|null $source_url
 * @property array<string, string>|null $utm
 * @property string $status
 * @property Carbon|null $created_at
 */
#[Fillable(['name', 'phone', 'area_id', 'area_text', 'service_id', 'ac_type', 'preferred_date', 'notes', 'source_url', 'utm', 'status'])]
class BookingRequest extends Model
{
    public const STATUSES = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'scheduled' => 'اتحدد معاد', 'done' => 'اتنفذ', 'cancelled' => 'اتلغى'];

    public const AC_TYPES = [
        'split' => 'سبليت',
        'window' => 'شباك',
        'concealed' => 'كونسيلد',
        'cassette' => 'كاسيت',
        'floor-standing' => 'دولابي',
        'central' => 'مركزي',
        'unknown' => 'مش متأكد',
    ];

    protected function casts(): array
    {
        return ['preferred_date' => 'date', 'utm' => 'array'];
    }

    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
