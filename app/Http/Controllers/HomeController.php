<?php

namespace App\Http\Controllers;

use App\Models\AdPlacement;
use App\Models\Post;
use App\Support\Quotes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    public function __invoke(Quotes $quotes): View
    {
        return view('site.home', [
            'quotes' => $quotes->get(),
            'feed' => $this->feed(),
            /*
             * Мамандар пікірі is a column that scrolls within itself, and five
             * headlines all fitted at once, so there was nothing to scroll to
             * and the scroll disappeared. Nine give it its run back. The phone,
             * where the column unfolds into the feed and only the page scrolls,
             * still takes five — feed-slice trims it.
             */
            'specialProjects' => $this->column(config('naryk.columns.special_projects')),
            'expertOpinions' => $this->column(config('naryk.columns.expert_opinions'), 9),
            'feedBanner' => AdPlacement::bannerFor(config('naryk.placements.feed')),
            'bannerAfter' => config('naryk.feed.banner_after'),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public static function feed(): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->newest()
            ->with('categories.term')
            ->paginate(config('naryk.feed.per_page'));
    }

    /**
     * @return Collection<int, Post>
     */
    protected function column(string $slug, int $limit = 5): Collection
    {
        return Post::query()
            ->published()
            ->inCategory($slug)
            ->newest()
            ->with('categories.term')
            ->limit($limit)
            ->get();
    }
}
