<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function __invoke(string $slug): View
    {
        $page = Page::query()
            ->published()
            ->where('post_name', $slug)
            ->firstOrFail();

        return view('site.page', ['page' => $page]);
    }
}
