<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * Category and tag listings differ only in which taxonomy they filter by.
 */
class ArchiveController extends Controller
{
    public function category(string $slug): View
    {
        $category = Category::whereHas('term', fn ($term) => $term->where('slug', $slug))->firstOrFail();

        return $this->render($category, Post::query()->inCategory($slug), 'Санаттар');
    }

    public function tag(string $slug): View
    {
        $tag = Tag::whereHas('term', fn ($term) => $term->where('slug', $slug))->firstOrFail();

        return $this->render($tag, Post::query()->inTag($slug), 'Тегтер');
    }

    /**
     * @param  Builder<Post>  $posts
     */
    protected function render(mixed $term, $posts, string $breadcrumb): View
    {
        return view('site.archive', [
            'term' => $term,
            'breadcrumb' => $breadcrumb,
            'posts' => $posts
                ->published()
                ->newest()
                ->with('categories.term')
                ->paginate(config('naryk.feed.per_page'))
                ->withQueryString(),
        ]);
    }
}
