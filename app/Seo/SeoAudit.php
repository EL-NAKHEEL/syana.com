<?php

namespace App\Seo;

use App\Models\Area;
use App\Models\NotFoundLog;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Admin SEO health report (PLAN.md §9): duplicate titles/descriptions/primary keywords, manual noindex,
 * published areas failing the guard, images without Arabic alt, and unresolved 404s.
 */
class SeoAudit
{
    /**
     * @return array<string, array{label: string, rows: Collection<int, array{label: string, detail: string}>}>
     */
    public function report(): array
    {
        return [
            'titles' => ['label' => 'عناوين مكررة', 'rows' => $this->duplicates('title')],
            'descriptions' => ['label' => 'أوصاف مكررة', 'rows' => $this->duplicates('description')],
            'keywords' => ['label' => 'كلمة مفتاحية أساسية لأكتر من صفحة', 'rows' => $this->duplicates('primary_keyword')],
            'noindex' => ['label' => 'صفحات متعلّم عليها noindex يدويًا', 'rows' => $this->noindexed()],
            'areas' => ['label' => 'مناطق منشورة ومش مكتملة (مخفية عن الموقع)', 'rows' => $this->failingAreas()],
            'alt' => ['label' => 'صور من غير وصف (alt)', 'rows' => $this->mediaWithoutAlt()],
            'not_found' => ['label' => 'أكتر روابط بتدي 404 (من غير تحويل)', 'rows' => $this->topNotFound()],
        ];
    }

    /**
     * @return Collection<int, array{label: string, detail: string}>
     */
    private function duplicates(string $column): Collection
    {
        return SeoMeta::query()->with('seoable')->whereNotNull($column)->where($column, '!=', '')->get()
            ->groupBy(fn (SeoMeta $meta) => mb_strtolower(trim((string) $meta->getAttribute($column))))
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(fn (Collection $group, string $value) => [
                'label' => $value,
                'detail' => $group->map(fn (SeoMeta $meta) => $this->describe($meta->seoable))->implode('، '),
            ])->values();
    }

    /**
     * @return Collection<int, array{label: string, detail: string}>
     */
    private function noindexed(): Collection
    {
        return SeoMeta::query()->with('seoable')->where('robots', 'like', 'noindex%')->get()
            ->map(fn (SeoMeta $meta) => $this->row($this->describe($meta->seoable), (string) $meta->robots))->values();
    }

    /**
     * @return Collection<int, array{label: string, detail: string}>
     */
    private function failingAreas(): Collection
    {
        return Area::query()->published()->withCount(['services', 'faqs', 'neighbors'])->get()
            ->reject->isLive()
            ->map(fn (Area $area) => $this->row($area->name_ar, implode('، ', $area->guardFailures())))->values();
    }

    /**
     * @return Collection<int, array{label: string, detail: string}>
     */
    private function mediaWithoutAlt(): Collection
    {
        return Media::query()->with('model')->get()
            ->filter(fn (Media $media) => trim((string) $media->getCustomProperty('alt')) === '')
            ->map(fn (Media $media) => $this->row($media->file_name, $this->describe($media->model)))->values();
    }

    /**
     * @return Collection<int, array{label: string, detail: string}>
     */
    private function topNotFound(): Collection
    {
        return NotFoundLog::query()->whereNull('resolved_redirect_id')->where('is_bot', false)
            ->orderByDesc('hits')->limit(20)->get()
            ->map(fn (NotFoundLog $log) => $this->row($log->path, $log->hits.' مرة'))->values();
    }

    /**
     * @return array{label: string, detail: string}
     */
    private function row(string $label, string $detail): array
    {
        return ['label' => $label, 'detail' => $detail];
    }

    private function describe(?Model $model): string
    {
        if ($model === null) {
            return '—';
        }

        $name = $model->getAttribute('title') ?? $model->getAttribute('name') ?? $model->getAttribute('name_ar') ?? '#'.$model->getKey();

        return $model->getMorphClass().': '.$name;
    }
}
