<?php

use App\Models\Concerns\HasSlugHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SlugHistoryTestItem extends Model
{
    use HasSlugHistory;

    protected $table = 'slug_history_test_items';

    protected $guarded = [];
}

beforeEach(function () {
    Schema::create('slug_history_test_items', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->timestamps();
    });
});

it('remembers old slugs so old URLs can 301 to the current record', function () {
    $item = SlugHistoryTestItem::query()->create(['slug' => 'sharp-1-5-hp']);
    $item->update(['slug' => 'sharp-ah-a12-1-5-hp']);
    $item->update(['slug' => 'sharp-ah-a12-inverter']);

    expect(SlugHistoryTestItem::findBySlugHistory('sharp-1-5-hp')?->is($item))->toBeTrue()
        ->and(SlugHistoryTestItem::findBySlugHistory('sharp-ah-a12-1-5-hp')?->is($item))->toBeTrue()
        ->and(SlugHistoryTestItem::findBySlugHistory('unknown'))->toBeNull();
});

it('forgets a history entry when that slug becomes live again', function () {
    $item = SlugHistoryTestItem::query()->create(['slug' => 'first']);
    $item->update(['slug' => 'second']);
    $item->update(['slug' => 'first']);

    expect(SlugHistoryTestItem::findBySlugHistory('first'))->toBeNull()
        ->and(SlugHistoryTestItem::findBySlugHistory('second')?->is($item))->toBeTrue();
});
