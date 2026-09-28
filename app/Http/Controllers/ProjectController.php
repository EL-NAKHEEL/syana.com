<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicRecords;
use App\Models\Project;
use App\Seo\Schema\SchemaGraph;
use App\Seo\Seo;
use App\Seo\TitleBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use ResolvesPublicRecords;

    public function index(Seo $seo): View
    {
        $projects = Project::query()->published()->with(['area', 'service', 'media'])->latest('completed_on')->get();
        abort_if($projects->isEmpty() && ! Auth::check(), 404);

        $seo->title('مشاريع النخيل كوول في التكييف')
            ->description('شغل حقيقي نفذه فريق النخيل كوول: تركيب وصيانة وتأسيس تكييفات لبيوت ومكاتب ومحلات، بالصور والتفاصيل. عندك مشروع؟ اتصل 01055207525.')
            ->canonical(route('projects.index'))
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'مشاريعنا', 'url' => route('projects.index')]])
            ->ogKicker('مشاريعنا')
            ->hub($projects->count());

        return view('projects.index', ['projects' => $projects]);
    }

    public function show(Seo $seo, string $slug): View
    {
        /** @var Project $project */
        $project = $this->resolveRecord(Project::class, $slug, ['area', 'service', 'media', 'seoMeta'], fn (Project $p) => $p->isPublished(), fn (Project $p) => $p->url());
        $url = $project->url();
        $photos = $project->getMedia('photos');

        $seo->title($project->title.($project->area?->isLive() ? ' في '.$project->area->name_ar : ''))
            ->description(TitleBuilder::limit($project->summary.' مشروع من شغل النخيل كوول. عندك مشروع شبهه؟ اتصل 01055207525.', 160))
            ->canonical($url)
            ->breadcrumbs([['name' => 'مشاريعنا', 'url' => route('projects.index')], ['name' => $project->title, 'url' => $url]])
            ->ogKicker('مشاريعنا')
            ->dates($project->published_at, $project->lastModified())
            ->fromModel($project);

        if ($first = $photos->first()) {
            $seo->ogImage($first->getFullUrl('large'), $project->title)
                ->addNode(['@type' => 'ImageObject', '@id' => SchemaGraph::pageId($url, 'primaryimage'), 'url' => $first->getFullUrl('large'), 'caption' => $first->getCustomProperty('alt') ?: $project->title]);
        }

        return view('projects.show', ['project' => $project, 'photos' => $photos]);
    }
}
