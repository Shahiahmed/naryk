<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Feeds the "тағы да" button; returns the markup of the next 15 cards.
Route::get('/feed', FeedController::class)->name('feed');

Route::get('/category/{slug}', [ArchiveController::class, 'category'])->name('category');
Route::get('/tag/{slug}', [ArchiveController::class, 'tag'])->name('tag');

// Keep last: the prefix is a single segment, so it must not shadow the routes
// above.
Route::get(config('naryk.post_prefix').'/{slug}', PostController::class)->name('post');
