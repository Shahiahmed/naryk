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
            'specialProjects' => $this->column(config('naryk.columns.special_projects')),
            /*
             * Point 13: Мамандар пікірі scrolls on its own, like Арнайы
             * жобалар, but with only six headlines there was nothing under the
             * fold to scroll to. Three more give the column its own run.
             */
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
    protected function column(string $slug, int $limit = 6): Collection
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
