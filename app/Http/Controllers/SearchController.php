<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q'));

        return view('site.search', [
            'query' => $query,
            'posts' => $query === '' ? null : $this->search($query),
        ]);
    }

    /**
     * The title carries the meaning; the summary catches the rest. The body is
     * a longtext with no index, so it stays out of the LIKE.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    protected function search(string $query)
    {
        $like = '%'.addcslashes($query, '%_\\').'%';

        return Post::query()
            ->published()
            ->where(fn (Builder $posts) => $posts
                ->where('post_title', 'like', $like)
                ->orWhere('post_summary', 'like', $like))
            ->newest()
            ->with('categories.term')
            ->paginate(config('naryk.feed.per_page'))
            ->withQueryString();
    }
}
