<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Feeds the "тағы да" button; returns the markup of the next 15 cards.
Route::get('/feed', FeedController::class)->name('feed');
