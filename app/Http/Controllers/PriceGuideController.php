<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicRecords;
use App\Models\PriceGuide;
use App\Seo\Seo;
use App\Seo\TitleBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PriceGuideController extends Controller
{
    use ResolvesPublicRecords;

    public function index(Seo $seo): View
    {
        $guides = PriceGuide::query()->published()->with(['services', 'seoMeta'])->orderBy('sort')->get()->filter->isLive()->values();

        abort_if($guides->isEmpty() && ! Auth::check(), 404);

        $seo->title('أسعار خدمات التكييف: التركيب والصيانة والتأسيس')
            ->description('أسعار خدمات التكييف من النخيل كوول: تكلفة التركيب والصيانة والتأسيس وإيه اللي بيأثر على السعر، من جدول أسعارنا الحالي. اتصل 01055207525 لسعر دقيق.')
            ->canonical(route('prices.index'))
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'الأسعار', 'url' => route('prices.index')]])
            ->ogKicker('الأسعار');

        return view('prices.index', ['guides' => $guides]);
    }

    public function show(Seo $seo, string $slug): View
    {
        /** @var PriceGuide $guide */
        $guide = $this->resolveRecord(
            PriceGuide::class,
            $slug,
            ['seoMeta', 'faqs', 'services'],
            fn (PriceGuide $g) => $g->isLive(),
            fn (PriceGuide $g) => $g->url(),
        );

        $url = $guide->url();
        $updated = $guide->lastPriceChange();

        $seo->title($guide->render($guide->title))
            ->description(TitleBuilder::limit(
                $guide->render($guide->h1).': '.($guide->intro ? strip_tags($guide->intro).' ' : '')
                .'اتصل 01055207525 لسعر دقيق بعد المعاينة.',
                160,
            ))
            ->canonical($url)
            ->breadcrumbs([
                ['name' => 'الأسعار', 'url' => route('prices.index')],
                ['name' => $guide->render($guide->h1), 'url' => $url],
            ])
            ->ogKicker('الأسعار')
            // dateModified follows real price changes only.
            ->dates($guide->published_at, $updated ?? $guide->lastModified())
            ->fromModel($guide);

        return view('prices.show', ['guide' => $guide, 'rows' => $guide->rows(), 'updated' => $updated]);
    }
}
