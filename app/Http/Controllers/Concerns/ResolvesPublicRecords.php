<?php

namespace App\Http\Controllers\Concerns;

use App\Seo\Seo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

trait ResolvesPublicRecords
{
    /**
     * Finds a record by slug for a public page:
     * live → shown; draft → 404 for visitors, noindex preview for signed-in staff;
     * unknown slug that used to exist → 301 to the current URL (slug history); else 404.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $class
     * @param  array<int|string, string|\Closure>  $with
     * @param  \Closure(TModel): bool  $isLive
     * @param  \Closure(TModel): string  $url
     * @return TModel
     */
    protected function resolveRecord(string $class, string $slug, array $with, \Closure $isLive, \Closure $url): Model
    {
        $record = $class::query()->with($with)->where('slug', $slug)->first();

        if ($record === null) {
            $moved = method_exists($class, 'findBySlugHistory') ? $class::findBySlugHistory($slug) : null;

            if ($moved !== null && $isLive($moved)) {
                throw new HttpResponseException(redirect()->to($url($moved), 301));
            }

            abort(404);
        }

        if (! $isLive($record)) {
            abort_unless(Auth::check(), 404);
            app(Seo::class)->preview();
        }

        return $record;
    }
}
