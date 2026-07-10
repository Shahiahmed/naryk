<?php

namespace App\Http\Controllers;

use App\Models\AdPlacement;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class PostController extends Controller
{
    public function __invoke(string $slug): View
    {
        $post = Post::query()
            ->published()
            ->with(['categories.term', 'tags.term', 'author'])
            ->where('post_name', $slug)
            ->firstOrFail();

        $post->recordHit();

        $placement = $post->bannerPlacement();

        return view('site.post', [
            'post' => $post,
            'banner' => $placement ? AdPlacement::bannerFor($placement) : null,
            'latest' => $this->latest($post),
        ]);
    }

    /**
     * @return Collection<int, Post>
     */
    protected function latest(Post $post, int $limit = 6): Collection
    {
        return Post::query()
            ->published()
            ->newest()
            ->whereKeyNot($post->getKey())
            ->limit($limit)
            ->get();
    }
}
