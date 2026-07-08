<?php
// Routes for BPS Dashboard Application - Automated Deploy

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'home'])->name('home');
Route::get('/metric/{metric}', [DashboardController::class, 'detail'])->name('metric.detail');
Route::get('/api/data/{metric}', [DashboardController::class, 'getData'])->name('metric.data');
Route::get('/api/search-suggestions', [DashboardController::class, 'getSuggestions'])->name('api.search_suggestions');
Route::get('/api/compare/{metric}', [DashboardController::class, 'compareData'])->name('api.compare_data');

Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return response()->json([
        'status' => 'success',
        'message' => 'Semua cache Laravel (view, config, route) di hosting berhasil dibersihkan!'
    ]);
});
