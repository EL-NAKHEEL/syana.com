<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotFoundLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
            'hits' => 'integer',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Redirect, $this>
     */
    public function resolvedRedirect(): BelongsTo
    {
        return $this->belongsTo(Redirect::class, 'resolved_redirect_id');
    }

    public static function record(Request $request): void
    {
        $path = Str::limit(Redirect::normalizePath($request->getPathInfo()), 250, '');
        $userAgent = (string) $request->userAgent();
        $now = now();

        DB::table('not_found_logs')->upsert(
            [[
                'path' => $path,
                'hits' => 1,
                'is_bot' => self::isBot($userAgent),
                'last_referrer' => Str::limit((string) $request->headers->get('referer'), 250, ''),
                'last_user_agent' => Str::limit($userAgent, 250, ''),
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['path'],
            [
                'hits' => DB::raw('hits + 1'),
                'last_referrer' => DB::raw('excluded.last_referrer'),
                'last_user_agent' => DB::raw('excluded.last_user_agent'),
                'last_seen_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public static function isBot(string $userAgent): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|preview|curl|wget|python|headless/i', $userAgent);
    }
}
