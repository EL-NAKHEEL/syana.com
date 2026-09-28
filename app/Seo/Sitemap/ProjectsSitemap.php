<?php

namespace App\Seo\Sitemap;

use App\Models\Project;
use Spatie\Sitemap\Tags\Url;

class ProjectsSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'projects';
    }

    public function urls(): iterable
    {
        $projects = Project::query()->published()->with(['seoMeta', 'media'])->get()->filter->isIndexable();

        if ($projects->count() >= (int) config('site.seo.hub_min_items')) {
            yield Url::create(route('projects.index'))->setLastModificationDate($projects->max(fn (Project $p) => $p->lastModified()));
        }

        foreach ($projects as $project) {
            $url = Url::create($project->url());
            if ($modified = $project->lastModified()) {
                $url->setLastModificationDate($modified);
            }
            foreach ($project->getMedia('photos') as $photo) {
                $url->addImage($photo->getFullUrl('large'), (string) ($photo->getCustomProperty('alt') ?: $project->title));
            }
            yield $url;
        }
    }
}
