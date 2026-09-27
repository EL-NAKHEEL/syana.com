<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicRecords;
use App\Models\Area;
use App\Models\Service;
use App\Seo\Schema\SchemaGraph;
use App\Seo\Seo;
use App\Seo\TitleBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ServiceController extends Controller
{
    use ResolvesPublicRecords;

    /**
     * Resolved per call: controller instances outlive a request in long-lived workers and tests.
     */
    private function seo(): Seo
    {
        return app(Seo::class);
    }

    public function index(): View
    {
        $services = Service::query()->live()->get();

        abort_if($services->isEmpty() && ! auth()->check(), 404);

        $this->seo()
            ->title('خدمات التكييف: صيانة وتركيب وتأسيس وتنظيف')
            ->description('كل خدمات التكييف من النخيل كوول: صيانة وتصليح، تركيب، تأسيس مواسير، تنظيف، شحن فريون، فك ونقل وعقود صيانة للشركات. اتصل 01055207525 واحجز معاينة.')
            ->canonical(route('services.index'))
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'خدماتنا', 'url' => route('services.index')]])
            ->ogKicker('خدماتنا');

        return view('services.index', ['services' => $services]);
    }

    public function show(string $slug): View
    {
        /** @var Service $service */
        $service = $this->resolveRecord(Service::class, $slug, ['seoMeta', 'faqs'], fn (Service $s) => $s->isLive(), fn (Service $s) => $s->url());

        $areas = Area::query()->published()->whereHas('services', fn ($q) => $q->whereKey($service->id))->orderBy('sort')->get();
        $others = Service::query()->live()->whereKeyNot($service->id)->get();
        $url = route('services.show', $service);

        $this->seo()
            ->title($service->h1)
            ->description(TitleBuilder::limit($service->summary.' احجز معاينة مع النخيل كوول أو اتصل 01055207525.', 160))
            ->canonical($url)
            ->breadcrumbs([
                ['name' => 'خدماتنا', 'url' => route('services.index')],
                ['name' => $service->name, 'url' => $url],
            ])
            ->ogKicker('خدماتنا')
            ->dates($service->published_at, $service->lastModified())
            ->addNode($this->serviceNode($service, $url, $areas))
            ->fromModel($service);

        return view('services.show', ['service' => $service, 'areas' => $areas, 'others' => $others]);
    }

    /**
     * @param  Collection<int, Area>  $areas
     * @return array<string, mixed>
     */
    private function serviceNode(Service $service, string $url, $areas): array
    {
        $node = [
            '@type' => 'Service',
            '@id' => SchemaGraph::pageId($url, 'service'),
            'name' => $service->name,
            'serviceType' => $service->schema_service_type ?: $service->name,
            'description' => $service->summary,
            'url' => $url,
            'provider' => SchemaGraph::ref(SchemaGraph::id('organization')),
            'mainEntityOfPage' => SchemaGraph::ref(SchemaGraph::pageId($url, 'webpage')),
        ];

        if ($areas->isNotEmpty()) {
            $node['areaServed'] = $areas->map(fn (Area $area) => ['@type' => 'Place', 'name' => $area->name_ar])->values()->all();
        }

        if ($service->starting_price !== null) {
            $node['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'EGP',
                'price' => $service->starting_price,
                'priceSpecification' => [
                    '@type' => 'PriceSpecification',
                    'minPrice' => $service->starting_price,
                    'priceCurrency' => 'EGP',
                ],
            ];
        }

        return $node;
    }
}
