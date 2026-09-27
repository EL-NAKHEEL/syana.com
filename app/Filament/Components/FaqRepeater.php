<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class FaqRepeater
{
    public static function make(): Section
    {
        return Section::make('الأسئلة الشائعة')
            ->collapsible()
            ->schema([
                Repeater::make('faqs')
                    ->hiddenLabel()
                    ->relationship()
                    ->orderColumn('sort')
                    ->schema([
                        TextInput::make('question')->label('السؤال')->required()->maxLength(250),
                        Textarea::make('answer')->label('الإجابة')->required()->rows(3),
                    ])
                    ->itemLabel(fn (array $state) => $state['question'] ?? null)
                    ->collapsed()
                    ->addActionLabel('أضف سؤال'),
            ]);
    }
}
