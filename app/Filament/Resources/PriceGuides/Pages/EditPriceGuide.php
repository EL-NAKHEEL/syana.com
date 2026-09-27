<?php

namespace App\Filament\Resources\PriceGuides\Pages;

use App\Filament\Resources\PriceGuides\PriceGuideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceGuide extends EditRecord
{
    protected static string $resource = PriceGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
