<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicRecords;
use App\Models\Area;
use App\Seo\Schema\SchemaGraph;
use App\Seo\Seo;
use App\Seo\TitleBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    use ResolvesPublicRecords;

    public function index(Seo $seo): View
    {
        $areas = Area::live();

        abort_if($areas->isEmpty() && ! Auth::check(), 404);

        $seo->title('مناطق خدمة النخيل كوول: صيانة وتركيب التكييفات')
            ->description('المناطق اللي فريق النخيل كوول بيخدمها في صيانة وتركيب وتأسيس التكييفات. اختار منطقتك واعرف الخدمات المتاحة ووقت الاستجابة، أو اتصل 01055207525.')
            ->canonical(route('areas.index'))
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'مناطق الخدمة', 'url' => route('areas.index')]])
            ->ogKicker('مناطق الخدمة');

        // Hub rule (owner decision C1): noindex until it lists at least N live areas.
        if ($areas->count() < (int) config('site.seo.hub_min_items')) {
            $seo->noindex();
        }

        return view('areas.index', ['areas' => $areas]);
    }

    public function show(Seo $seo, string $slug): View
    {
        /** @var Area $area */
        $area = $this->resolveRecord(
            Area::class,
            $slug,
            ['seoMeta', 'faqs', 'services' => fn ($q) => $q->live()],
            fn (Area $a) => $a->isLive(),
            fn (Area $a) => $a->url(),
        );

        $neighbors = $area->neighbors()->published()->get()->filter->isLive()->values();
        $url = $area->url();
        $services = $area->services->filter->isLive()->values();

        $seo->title('صيانة وتركيب تكييفات في '.$area->name_ar)
            ->description(TitleBuilder::limit(
                'صيانة وتركيب وتأسيس تكييفات في '.$area->name_ar.' مع النخيل كوول. '
                .($area->response_time_note ? $area->response_time_note.'. ' : '')
                .'احجز معاينة أو اتصل 01055207525.',
                160,
            ))
            ->canonical($url)
            ->breadcrumbs([
                ['name' => 'مناطق الخدمة', 'url' => route('areas.index')],
                ['name' => $area->name_ar, 'url' => $url],
            ])
            ->ogKicker('مناطق الخدمة')
            ->dates($area->published_at, $area->lastModified())
            ->addNode([
                '@type' => 'Service',
                '@id' => SchemaGraph::pageId($url, 'service'),
                'name' => 'صيانة وتركيب التكييفات في '.$area->name_ar,
                'serviceType' => 'Air conditioner installation and maintenance',
                'provider' => SchemaGraph::ref(SchemaGraph::id('organization')),
                'areaServed' => ['@type' => 'Place', 'name' => $area->name_ar],
                'url' => $url,
            ])
            ->fromModel($area);

        return view('areas.show', ['area' => $area, 'services' => $services, 'neighbors' => $neighbors]);
    }
}
