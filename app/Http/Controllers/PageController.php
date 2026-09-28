<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Seo\Schema\SchemaGraph;
use App\Seo\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /**
     * Resolved per call: controller instances outlive a request in long-lived workers and tests.
     */
    private function seo(): Seo
    {
        return app(Seo::class);
    }

    public function home(): View
    {
        $page = $this->resolve('home');

        $this->seo()
            ->title('النخيل كوول | بيع وتركيب وصيانة التكييفات في مصر', false)
            ->description('النخيل كوول لبيع وتركيب وصيانة وتأسيس التكييفات للبيوت والشركات، بفنيين متخصصين ومعاينة قبل التنفيذ. اتصل 01055207525 أو كلمنا واتساب.')
            ->canonical(route('home'))
            ->pageType('WebPage', SchemaGraph::id('organization'))
            ->ogKicker('بيع وتركيب وصيانة التكييفات')
            ->dates($page->published_at, $page->lastModified())
            ->fromModel($page);

        return view('pages.home', ['page' => $page]);
    }

    public function about(): View
    {
        $page = $this->resolve('about');

        $this->seo()
            ->title('من نحن: النخيل كوول لتكييفات البيوت والشركات')
            ->description('تعرّف على النخيل كوول: شركة مصرية لبيع وتركيب وصيانة وتأسيس التكييفات للبيوت والشركات، بفريق فني متخصص ومعاينة قبل أي شغل. اتصل بينا على 01055207525.')
            ->canonical(route('about'))
            ->pageType('AboutPage', SchemaGraph::id('organization'))
            ->breadcrumbs([['name' => 'من نحن', 'url' => route('about')]])
            ->dates($page->published_at, $page->lastModified())
            ->fromModel($page);

        return view('pages.about', ['page' => $page]);
    }

    public function contact(): View
    {
        $page = $this->resolve('contact');

        $this->seo()
            ->title('تواصل مع النخيل كوول: اتصال وواتساب')
            ->description('محتاج تركيب أو صيانة أو تأسيس تكييف؟ كلّم النخيل كوول على 01055207525 أو ابعت رسالة واتساب، وهنرد عليك ونحدد معاد المعاينة المناسب ليك.')
            ->canonical(route('contact'))
            ->pageType('ContactPage', SchemaGraph::id('organization'))
            ->breadcrumbs([['name' => 'تواصل معنا', 'url' => route('contact')]])
            ->dates($page->published_at, $page->lastModified())
            ->fromModel($page);

        return view('pages.contact', ['page' => $page]);
    }

    /** Policy pages: route slug → [title, fallback description]. */
    public const POLICIES = [
        'warranty' => ['سياسة الضمان', 'سياسة الضمان في النخيل كوول: الضمان على التكييفات والتركيب والصيانة وإزاي تطلب خدمة الضمان. عندك سؤال؟ اتصل بينا على 01055207525.'],
        'shipping-returns' => ['التوصيل والاسترجاع', 'سياسة التوصيل والاسترجاع في النخيل كوول: مناطق وتكلفة ومواعيد التوصيل، وشروط الاسترجاع والاستبدال. للاستفسار اتصل على 01055207525.'],
        'privacy' => ['سياسة الخصوصية', 'إزاي النخيل كوول بتتعامل مع بياناتك: البيانات اللي بنجمعها من الفورمات والطلبات، بنستخدمها في إيه، وحقوقك فيها. للاستفسار اتصل 01055207525.'],
        'terms' => ['الشروط والأحكام', 'الشروط والأحكام الخاصة باستخدام موقع النخيل كوول وطلب المنتجات والخدمات، والدفع والتوصيل والضمان. لو عندك أي سؤال اتصل بينا على 01055207525.'],
    ];

    public function policy(string $slug): View
    {
        $page = $this->resolve($slug);
        [$title, $description] = self::POLICIES[$slug];

        $this->seo()
            ->title($title)
            ->description($description)
            ->canonical(route($slug))
            ->breadcrumbs([['name' => $page->title, 'url' => route($slug)]])
            ->dates($page->published_at, $page->lastModified())
            ->fromModel($page);

        return view('pages.default', ['page' => $page]);
    }

    /**
     * Unpublished pages are 404 for visitors; signed-in admins see a noindex draft preview.
     */
    private function resolve(string $slug): Page
    {
        $page = Page::query()->with(['seoMeta', 'faqs'])->where('slug', $slug)->first();

        abort_if($page === null, 404);

        if (! $page->isPublished()) {
            abort_unless(Auth::check(), 404);
            $this->seo()->preview();
        }

        return $page;
    }
}
