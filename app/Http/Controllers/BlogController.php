<?php

namespace App\Http\Controllers;

use App\Content\PostRenderer;
use App\Http\Controllers\Concerns\ResolvesPublicRecords;
use App\Models\Person;
use App\Models\Post;
use App\Models\PostCategory;
use App\Seo\Schema\SchemaGraph;
use App\Seo\Seo;
use App\Seo\TitleBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    use ResolvesPublicRecords;

    private const PER_PAGE = 12;

    /**
     * @return Builder<Post>
     */
    private function livePosts(): Builder
    {
        return Post::query()->published()->whereHas('author', fn ($q) => $q->published())
            ->with(['author', 'category', 'media'])->latest('published_at');
    }

    public function index(Request $request, Seo $seo): View
    {
        $posts = $this->livePosts()->paginate(self::PER_PAGE);
        abort_if(($posts->total() === 0 && ! Auth::check()) || $posts->currentPage() > max(1, $posts->lastPage()), 404);

        $seo->title('مدونة النخيل كوول: نصائح التكييف')
            ->description('نصايح عملية عن التكييف من فنيين النخيل كوول: الأعطال الشائعة وحلها، اختيار القدرة المناسبة، الصيانة وتوفير الكهرباء. عندك مشكلة؟ اتصل 01055207525.')
            ->canonical(route('blog.index'))
            ->page($posts->currentPage())
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'المدونة', 'url' => route('blog.index')]])
            ->ogKicker('المدونة')
            ->hub($posts->total());

        $categories = PostCategory::query()->published()->whereHas('posts', fn ($q) => $q->published())->orderBy('sort')->get();

        return view('blog.index', ['posts' => $posts, 'categories' => $categories, 'heading' => 'المدونة', 'intro' => 'نصايح وحلول لمشاكل التكييف من فريق النخيل كوول.']);
    }

    public function category(Seo $seo, string $slug): View
    {
        /** @var PostCategory $category */
        $category = $this->resolveRecord(PostCategory::class, $slug, ['seoMeta'], fn (PostCategory $c) => $c->isPublished(), fn (PostCategory $c) => $c->url());
        $posts = $this->livePosts()->where('post_category_id', $category->id)->paginate(self::PER_PAGE);
        abort_if(($posts->total() === 0 && ! Auth::check()) || $posts->currentPage() > max(1, $posts->lastPage()), 404);

        $seo->title($category->name.' | مدونة النخيل كوول')
            ->description(TitleBuilder::limit('مقالات '.$category->name.' في مدونة النخيل كوول: '.($category->intro ? $category->intro.' ' : '').'نصايح عملية من فنيين التكييف للبيت والشغل. محتاج فني؟ اتصل 01055207525.', 160))
            ->canonical($category->url())
            ->page($posts->currentPage())
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'المدونة', 'url' => route('blog.index')], ['name' => $category->name, 'url' => $category->url()]])
            ->ogKicker('المدونة')
            ->fromModel($category)
            ->hub($posts->total());

        return view('blog.index', ['posts' => $posts, 'categories' => collect(), 'heading' => $category->name, 'intro' => $category->intro]);
    }

    public function author(Seo $seo, string $slug): View
    {
        /** @var Person $person */
        $person = $this->resolveRecord(Person::class, $slug, ['seoMeta', 'media'], fn (Person $p) => $p->isPublished() && $p->is_author, fn (Person $p) => $p->url());
        $posts = $this->livePosts()->where('author_id', $person->id)->get();
        abort_if($posts->isEmpty() && ! Auth::check(), 404);

        $url = $person->url();
        $seo->title($person->name.($person->job_title ? ': '.$person->job_title : ''))
            ->description(TitleBuilder::limit($person->name.($person->job_title ? '، '.$person->job_title : '').' في فريق النخيل كوول. '.($person->bio ? strip_tags($person->bio).' ' : '').'اقرأ مقالاته ونصايحه العملية عن اختيار التكييف وتركيبه وصيانته.', 160))
            ->canonical($url)
            ->pageType('ProfilePage', SchemaGraph::pageId($url, 'person'))
            ->breadcrumbs([['name' => 'المدونة', 'url' => route('blog.index')], ['name' => $person->name, 'url' => $url]])
            ->addNode($this->personNode($person))
            ->fromModel($person);

        return view('blog.author', ['person' => $person, 'posts' => $posts]);
    }

    public function show(Seo $seo, PostRenderer $renderer, string $slug): View
    {
        /** @var Post $post */
        $post = $this->resolveRecord(Post::class, $slug, ['author.media', 'reviewer', 'category', 'service', 'media', 'seoMeta'], fn (Post $p) => $p->isLive(), fn (Post $p) => $p->url());

        $url = $post->url();
        $rendered = $renderer->render($post->body);
        $related = $this->livePosts()->whereKeyNot($post->id)
            ->when($post->post_category_id, fn ($q) => $q->where('post_category_id', $post->post_category_id))
            ->limit(3)->get();
        $image = $post->getFirstMedia('featured');

        $breadcrumbs = [['name' => 'المدونة', 'url' => route('blog.index')]];
        if ($post->category?->isPublished()) {
            $breadcrumbs[] = ['name' => $post->category->name, 'url' => $post->category->url()];
        }
        $breadcrumbs[] = ['name' => $post->title, 'url' => $url];

        $seo->title($post->title)
            ->description(TitleBuilder::limit(mb_strlen($post->excerpt) >= 110 ? $post->excerpt : $post->excerpt.' مقال من مدونة النخيل كوول بنصايح عملية من فنيين التكييف. محتاج فني؟ اتصل 01055207525.', 160))
            ->canonical($url)
            ->ogType('article')
            ->ogKicker('مدونة النخيل كوول')
            ->breadcrumbs($breadcrumbs)
            ->dates($post->published_at, $post->lastModified())
            ->addNode(array_filter([
                '@type' => 'BlogPosting',
                '@id' => SchemaGraph::pageId($url, 'article'),
                'headline' => $post->title,
                'description' => $post->excerpt,
                'url' => $url,
                'datePublished' => $post->published_at?->toAtomString(),
                'dateModified' => $post->lastModified()?->toAtomString(),
                'author' => SchemaGraph::ref(SchemaGraph::pageId($post->author->url(), 'person')),
                'publisher' => SchemaGraph::ref(SchemaGraph::id('organization')),
                'image' => $image?->getFullUrl('large'),
                'inLanguage' => 'ar',
                'mainEntityOfPage' => SchemaGraph::ref(SchemaGraph::pageId($url, 'webpage')),
            ]))
            ->addNode($this->personNode($post->author))
            ->fromModel($post);

        if ($image) {
            $seo->ogImage($image->getFullUrl('large'), $post->title);
        }

        return view('blog.show', ['post' => $post, 'body' => $rendered['html'], 'toc' => $rendered['toc'], 'related' => $related]);
    }

    /**
     * @return array<string, mixed>
     */
    private function personNode(Person $person): array
    {
        return array_filter([
            '@type' => 'Person',
            '@id' => SchemaGraph::pageId($person->url(), 'person'),
            'name' => $person->name,
            'jobTitle' => $person->job_title,
            'description' => $person->bio ? strip_tags($person->bio) : null,
            'url' => $person->url(),
            'image' => $person->getFirstMediaUrl('photo', 'avatar') ?: null,
            'sameAs' => $person->same_as ?: null,
            'worksFor' => SchemaGraph::ref(SchemaGraph::id('organization')),
        ]);
    }
}
