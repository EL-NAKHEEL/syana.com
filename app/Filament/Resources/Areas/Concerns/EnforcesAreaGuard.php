<?php

namespace App\Filament\Resources\Areas\Concerns;

use App\Models\Area;
use Filament\Notifications\Notification;

/**
 * Relations (services, FAQs, neighbors) are saved after the record, so the publish guard runs afterwards:
 * an area that fails it is switched back to draft and the admin is told what is missing.
 */
trait EnforcesAreaGuard
{
    protected function enforceGuard(): void
    {
        /** @var Area $area */
        $area = $this->getRecord()->refresh();

        if (! $area->is_published || ($failures = $area->guardFailures()) === []) {
            return;
        }

        $area->forceFill(['is_published' => false])->saveQuietly();
        if (method_exists($this, 'refreshFormData')) {
            $this->refreshFormData(['is_published']);
        }

        Notification::make()
            ->title('الصفحة اتحفظت كمسودة: شروط النشر مش مكتملة')
            ->body(implode("\n", $failures))
            ->warning()
            ->persistent()
            ->send();
    }
}
