<?php

namespace App\Filament\Resources\Areas\Pages;

use App\Filament\Resources\Areas\AreaResource;
use App\Filament\Resources\Areas\Concerns\EnforcesAreaGuard;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArea extends EditRecord
{
    use EnforcesAreaGuard;

    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->enforceGuard();
    }
}
