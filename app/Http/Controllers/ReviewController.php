<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UsesHubPage;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Area;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Seo\Seo;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use UsesHubPage;

    public function index(Request $request, Seo $seo): View
    {
        $service = $request->filled('service') ? Service::query()->live()->where('slug', $request->query('service'))->first() : null;

        $reviews = Review::query()->approved()->with(['area', 'reviewable'])
            ->when($service, fn ($q) => $q->where('reviewable_type', 'service')->where('reviewable_id', $service?->id))
            ->paginate(20)->withQueryString();

        abort_if($reviews->currentPage() > max(1, $reviews->lastPage()), 404);

        $seo->title('آراء عملاء النخيل كوول في التكييف')
            ->description('آراء حقيقية من عملاء النخيل كوول بعد التركيب والصيانة، بتتنشر بعد المراجعة. جربت خدمتنا؟ شاركنا رأيك، أو اتصل 01055207525 لو محتاج فني.')
            ->canonical(route('reviews.index'))
            ->page($reviews->currentPage())
            ->breadcrumbs([['name' => 'آراء العملاء', 'url' => route('reviews.index')]])
            ->ogKicker('آراء العملاء')
            // No aggregateRating here: reviews of the business itself are never marked up (self-serving).
            ->hub(Review::query()->approved()->count());

        return view('reviews.index', [
            'reviews' => $reviews,
            'services' => Service::query()->live()->get(['id', 'slug', 'name']),
            'areas' => Area::live(),
            'products' => Product::query()->live()->with('brand')->get(),
            'currentService' => $service,
            'hub' => $this->hubPage($seo, 'reviews'),
        ]);
    }

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $data = $request->validated();
        [$type, $id] = match (true) {
            ! empty($data['product_id']) => ['product', $data['product_id']],
            ! empty($data['service_id']) => ['service', $data['service_id']],
            default => [null, null],
        };

        $review = Review::query()->create([
            'name' => $data['name'],
            'rating' => $data['rating'],
            'body' => $data['body'],
            'area_id' => $data['area_id'] ?? null,
            'reviewable_type' => $type,
            'reviewable_id' => $id,
            'temp_before' => $data['temp_before'] ?? null,
            'temp_after' => $data['temp_after'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'status' => 'pending',
            'ip_hash' => hash('sha256', (string) $request->ip().config('app.key')),
        ]);

        FilamentNotification::make()->title('تقييم جديد محتاج مراجعة')->body($review->name.' — '.$review->rating.'/5')->sendToDatabase(User::all());

        return redirect()->route('reviews.index')->with('status', 'شكرًا! رأيك وصلنا وهيظهر بعد المراجعة.');
    }
}
