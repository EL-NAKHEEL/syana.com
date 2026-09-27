<?php

namespace App\Filament\Components;

use App\Models\SeoMeta;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Per-record SEO panel (title/description with live length counters, keyword, robots, canonical, OG image).
 * Empty fields fall back to the page-type templates. The RTL SERP preview lands in P4.
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
}
