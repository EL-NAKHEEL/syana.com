<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Fillable(['from_path', 'to_url', 'status_code', 'source', 'is_active', 'note'])]
class Redirect extends Model
{
    public const CACHE_KEY = 'redirects.map';

    public const STATUS_CODES = [301 => '301 نقل دائم', 302 => '302 نقل مؤقت', 410 => '410 محذوفة نهائيًا'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $redirect): void {
            $redirect->from_path = self::normalizePath($redirect->from_path);
        });

        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Lowercase, decoded, single leading slash, no trailing slash, no query string.
     */
    public static function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $path = mb_strtolower(rawurldecode($path));
        $path = '/'.trim((string) preg_replace('#/{2,}#', '/', $path), '/');

        return $path;
    }

    /**
     * @return array{id: int, to: ?string, status: int}|null
     */
    public static function lookup(string $path): ?array
    {
        return self::map()[self::normalizePath($path)] ?? null;
    }

    /**
     * @return array<string, array{id: int, to: ?string, status: int}>
     */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => self::query()
            ->where('is_active', true)
            ->get(['id', 'from_path', 'to_url', 'status_code'])
            ->mapWithKeys(fn (self $r) => [$r->from_path => ['id' => $r->id, 'to' => $r->to_url, 'status' => $r->status_code]])
            ->all());
    }

    public static function recordHit(int $id): void
    {
        DB::table('redirects')->where('id', $id)->update([
            'hits' => DB::raw('hits + 1'),
            'last_hit_at' => now(),
        ]);
    }
}
