<?php

namespace App\Filament\Resources\Areas\Pages;

use App\Filament\Resources\Areas\AreaResource;
use App\Filament\Resources\Areas\Concerns\EnforcesAreaGuard;
use Filament\Resources\Pages\CreateRecord;

class CreateArea extends CreateRecord
{
    use EnforcesAreaGuard;

    protected static string $resource = AreaResource::class;

    protected function afterCreate(): void
    {
        $this->enforceGuard();
    }
}
