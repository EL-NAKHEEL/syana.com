<?php

namespace App\Filament\Resources\FacetPages\Pages;

use App\Filament\Resources\FacetPages\FacetPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFacetPage extends EditRecord
{
    protected static string $resource = FacetPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
