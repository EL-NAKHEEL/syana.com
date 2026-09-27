<?php

namespace App\Filament\Resources\Redirects\Schemas;

use App\Models\Redirect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('التحويل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('from_path')
                            ->label('من (المسار القديم)')
                            ->helperText('مسار فقط يبدأ بـ / مثل /old-page — بيتحوّل لحروف صغيرة تلقائيًا.')
                            ->required()
                            ->maxLength(250)
                            ->regex('#^/#')
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['dir' => 'ltr']),
                        Select::make('status_code')
                            ->label('النوع')
                            ->options(Redirect::STATUS_CODES)
                            ->default(301)
                            ->required()
                            ->live(),
                        TextInput::make('to_url')
                            ->label('إلى')
                            ->helperText('مسار داخلي مثل /services/ac-maintenance أو رابط كامل.')
                            ->maxLength(250)
                            ->required(fn (Get $get) => (int) $get('status_code') !== 410)
                            ->hidden(fn (Get $get) => (int) $get('status_code') === 410)
                            ->extraInputAttributes(['dir' => 'ltr']),
                        Toggle::make('is_active')->label('مفعّل')->default(true),
                        TextInput::make('note')->label('ملاحظة')->maxLength(250)->columnSpanFull(),
                    ]),
            ]);
    }
}
