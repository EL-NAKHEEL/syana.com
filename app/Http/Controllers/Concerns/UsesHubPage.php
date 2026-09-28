<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Page;
use App\Seo\Seo;

/**
 * Applies the owner's edits for a section page (admin › الصفحات › صفحة قسم): SEO overrides here,
 * heading/intro/text/FAQs/photo in the view through <x-hub-header> and <x-hub-extra>.
 */
trait UsesHubPage
{
    protected function hubPage(Seo $seo, string $slug): ?Page
    {
        $hub = Page::hub($slug);

        if ($hub !== null) {
            $seo->fromModel($hub);
        }

        return $hub;
    }
}
