<?php

namespace App\Seo\Sitemap;

use App\Models\Person;
use App\Models\Post;
use App\Models\PostCategory;
use Spatie\Sitemap\Tags\Url;

class PostsSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'posts';
    }

    public function urls(): iterable
    {
        $posts = Post::query()->published()->whereHas('author', fn ($q) => $q->published())
            ->with(['seoMeta', 'media', 'category'])->latest('published_at')->get()
            ->filter->isIndexable();
        $min = (int) config('site.seo.hub_min_items');

        if ($posts->count() >= $min) {
            yield Url::create(route('blog.index'))->setLastModificationDate($posts->max(fn (Post $p) => $p->lastModified()));
        }

        foreach ($posts->groupBy('post_category_id') as $categoryPosts) {
            /** @var PostCategory|null $category */
            $category = $categoryPosts->first()?->category;
            if ($category && $category->isPublished() && $category->isIndexable() && $categoryPosts->count() >= $min) {
                yield Url::create($category->url())->setLastModificationDate($categoryPosts->max(fn (Post $p) => $p->lastModified()));
            }
        }

        $authors = Person::query()->published()->where('is_author', true)->with('seoMeta')
            ->whereIn('id', $posts->pluck('author_id'))->get();
        foreach ($authors as $author) {
            if ($author->isIndexable()) {
                $url = Url::create($author->url());
                if ($modified = $author->lastModified()) {
                    $url->setLastModificationDate($modified);
                }
                yield $url;
            }
        }

        foreach ($posts as $post) {
            $url = Url::create($post->url());
            if ($modified = $post->lastModified()) {
                $url->setLastModificationDate($modified);
            }
            if ($image = $post->getFirstMedia('featured')) {
                $url->addImage($image->getFullUrl('large'), (string) ($image->getCustomProperty('alt') ?: $post->title));
            }
            yield $url;
        }
    }
}
