<?php

namespace App\Filament\Pages;

use App\Seo\SeoAudit;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SeoHealth extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static ?string $navigationLabel = 'صحة SEO';

    protected static ?string $title = 'صحة SEO';

    protected static ?string $slug = 'seo-health';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.seo-health';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['report' => app(SeoAudit::class)->report()];
    }
}
