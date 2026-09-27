<?php

namespace App\Filament\Resources\PriceGuides\Pages;

use App\Filament\Resources\PriceGuides\PriceGuideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceGuides extends ListRecords
{
    protected static string $resource = PriceGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
