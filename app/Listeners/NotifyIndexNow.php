<?php

namespace App\Listeners;

use App\Events\PublicContentChanged;
use App\Jobs\PingIndexNow;

class NotifyIndexNow
{
    public function handle(PublicContentChanged $event): void
    {
        if (! config('site.indexnow.enabled') || ! app()->isProduction() || $event->model === null || ! method_exists($event->model, 'url')) {
            return;
        }

        // Drafts that never went live are not announced; unpublishing is (the URL now 404s).
        $model = $event->model;
        if (! ($model->getAttribute('is_published') || $model->getOriginal('is_published'))) {
            return;
        }

        PingIndexNow::dispatch((string) $model->url())->afterCommit();
    }
}
