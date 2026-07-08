<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'home'])->name('home');
Route::get('/metric/{metric}', [DashboardController::class, 'detail'])->name('metric.detail');
Route::get('/api/data/{metric}', [DashboardController::class, 'getData'])->name('metric.data');
Route::get('/api/search-suggestions', [DashboardController::class, 'getSuggestions'])->name('api.search_suggestions');
Route::get('/api/compare/{metric}', [DashboardController::class, 'compareData'])->name('api.compare_data');

