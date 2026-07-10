<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Feeds the "тағы да" button; returns the markup of the next 15 cards.
Route::get('/feed', FeedController::class)->name('feed');

Route::get('/search', SearchController::class)->name('search');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/category/{slug}', [ArchiveController::class, 'category'])->name('category');
Route::get('/tag/{slug}', [ArchiveController::class, 'tag'])->name('tag');

Route::get(config('naryk.post_prefix').'/{slug}', PostController::class)->name('post');

/*
 * Pages sit at the site root (`page_permalink_type` is `page_name`), so this
 * has to be the last route, and it must not swallow the admin panel or the
 * asset directories.
 */
Route::get('/{slug}', PageController::class)
    ->where('slug', '(?!admin|livewire|storage|assets|img|feed|search|contact|category|tag|'.config('naryk.post_prefix').')[a-z0-9][a-z0-9\-]*')
    ->name('page');
