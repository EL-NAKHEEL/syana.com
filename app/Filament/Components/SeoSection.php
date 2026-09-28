<?php

namespace App\Filament\Components;

use App\Models\SeoMeta;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

/**
 * Per-record SEO panel (title/description with live length counters, keyword, robots, canonical, OG image).
 * Empty fields fall back to the page-type templates. Includes a live RTL Google result preview.
 */
class SeoSection
{
    public static function make(): Section
    {
        return Section::make('SEO')
            ->description('اتركها فاضية لاستخدام القالب التلقائي.')
            ->relationship('seoMeta')
            ->collapsible()
            ->columns(2)
            ->schema([
                Html::make(fn (Get $get) => self::preview($get('title'), $get('description')))->columnSpanFull(),
                TextInput::make('title')
                    ->label('عنوان الصفحة (title)')
                    ->maxLength(70)
                    ->live(debounce: 300)
                    ->helperText(fn (?string $state) => self::counter($state, 30, 60))
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('الوصف (meta description)')
                    ->rows(3)
                    ->maxLength(200)
                    ->live(debounce: 300)
                    ->helperText(fn (?string $state) => self::counter($state, 120, 160))
                    ->columnSpanFull(),
                TextInput::make('primary_keyword')
                    ->label('الكلمة المفتاحية الأساسية')
                    ->helperText('كلمة واحدة لكل صفحة — لوحة SEO هتنبهك لو اتكررت.')
                    ->maxLength(120),
                Select::make('robots')
                    ->label('الفهرسة')
                    ->options(SeoMeta::ROBOTS_OPTIONS)
                    ->placeholder('تلقائي (index, follow)'),
                TextInput::make('canonical_override')
                    ->label('Canonical مخصص')
                    ->helperText('نادرًا ما تحتاجه. لازم يكون رابط كامل على نفس الدومين.')
                    ->url()
                    ->maxLength(250)
                    ->extraInputAttributes(['dir' => 'ltr']),
                TextInput::make('og_image')
                    ->label('صورة المشاركة (OG) مخصصة')
                    ->helperText('رابط صورة 1200×630 أقل من 300KB. فاضي = صورة تلقائية بالعنوان.')
                    ->url()
                    ->maxLength(250)
                    ->extraInputAttributes(['dir' => 'ltr']),
            ]);
    }

    public static function counter(?string $state, int $min, int $max): string
    {
        $length = mb_strlen((string) $state);

        if ($length === 0) {
            return "فاضي — هيتستخدم القالب ({$min}–{$max} حرف).";
        }

        $status = match (true) {
            $length < $min => 'قصير',
            $length > $max => 'طويل — ممكن يتقص في جوجل',
            default => 'مناسب',
        };

        return "{$length} حرف — {$status} (المثالي {$min}–{$max}).";
    }

    /**
     * Approximate Google result snippet (RTL). Truncation mirrors the ~60 / ~160 character display limits.
     */
    private static function preview(?string $title, ?string $description): HtmlString
    {
        $title = $title ? e(mb_strimwidth($title.' | '.config('site.brand.name'), 0, 64, '…')) : '<em>العنوان التلقائي للصفحة</em>';
        $description = $description ? e(mb_strimwidth($description, 0, 163, '…')) : '<em>الوصف التلقائي للصفحة</em>';
        $host = e((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        return new HtmlString(<<<HTML
            <div dir="rtl" aria-label="معاينة نتيجة جوجل" style="max-inline-size:600px;padding:12px 16px;border:1px solid #dadce0;border-radius:8px;background:#fff;font-family:arial,sans-serif">
                <div style="font-size:12px;color:#4d5156">{$host}</div>
                <div style="font-size:20px;line-height:1.3;color:#1a0dab">{$title}</div>
                <div style="font-size:14px;line-height:1.58;color:#4d5156">{$description}</div>
            </div>
            HTML);
    }
}
