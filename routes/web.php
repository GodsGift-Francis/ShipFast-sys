<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — full route map is filled in over the coming sprints.
|--------------------------------------------------------------------------
*/

// Public site
Route::view('/', 'public.home')->name('home');

// Health check (also available at /up via the framework)
Route::get('/healthz', fn () => response()->json(['status' => 'ok']))->name('healthz');

// Auth, portal, and admin routes are added in later sprints:
// require __DIR__.'/auth.php';
