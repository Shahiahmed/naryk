<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Serves the next slice of the centre column for the "тағы да" button. Only
 * page one carries the in-feed banner, so this never renders it.
 */
class FeedController extends Controller
{
    public function __invoke(): View
    {
        $feed = HomeController::feed();

        return view('site.partials.feed-slice', [
            'feed' => $feed,
            'feedBanner' => null,
            'bannerAfter' => null,
        ]);
    }
}
