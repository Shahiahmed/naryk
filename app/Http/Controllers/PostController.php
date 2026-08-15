<?php

namespace App\Http\Controllers;

use App\Models\AdPlacement;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __invoke(Request $request, string $slug): View
    {
        $post = Post::query()
            ->with(['categories.term', 'tags.term', 'author'])
            ->where('post_name', $slug)
            ->firstOrFail();

        /*
         * A material that is not yet live — a draft or one dated in the future —
         * opens for signed-in staff (so an editor can proof it) and for anyone
         * holding its signed preview link (Post::previewUrl(), for showing a
         * client a draft without an account). To everyone else it is simply not
         * there — a plain guess of the URL gets a 404.
         */
        $preview = ! $post->isPublished();

        $mayPreview = auth()->user()?->isStaff() || $request->hasValidSignature();

        abort_if($preview && ! $mayPreview, 404);

        // A preview must not inflate the view count.
        if (! $preview) {
            $post->recordHit();
        }

        $placement = $post->bannerPlacement();

        return view('site.post', [
            'post' => $post,
            'preview' => $preview,
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
