<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Seo\Seo;
use App\Support\ArabicNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, Seo $seo): View
    {
        $query = mb_substr(trim((string) $request->query('q')), 0, 80);
        $terms = array_filter(explode(' ', ArabicNormalizer::normalize($query)), fn ($t) => mb_strlen($t) >= 2);

        $products = collect();
        $services = collect();

        if ($terms !== []) {
            $products = Product::query()->live()->with(['brand', 'media'])
                ->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->where('search_text', 'like', '%'.addcslashes($term, '%_\\').'%');
                    }
                })
                ->limit(24)->get();

            $services = Service::query()->live()->get()->filter(function (Service $service) use ($terms) {
                $haystack = ArabicNormalizer::normalize($service->name.' '.$service->summary);

                return collect($terms)->every(fn ($term) => str_contains($haystack, $term));
            })->values();
        }

        $seo->title($query !== '' ? 'نتايج البحث عن: '.$query : 'البحث في النخيل كوول')
            ->description('ابحث في موديلات التكييف وخدمات الصيانة والتركيب عند النخيل كوول. مش لاقي اللي بتدور عليه؟ اتصل بينا على 01055207525 ونساعدك.')
            ->canonical(route('search'))
            ->noindex()
            ->breadcrumbs([['name' => 'البحث', 'url' => route('search')]]);

        return view('pages.search', ['query' => $query, 'products' => $products, 'services' => $services]);
    }
}
