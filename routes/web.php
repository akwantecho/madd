<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/tasks', [PageController::class, 'tasks'])->name('tasks');

Route::get('/exhibitions', [PageController::class, 'exhibitions'])->name('exhibitions');
Route::get('/exhibitions/{id}', [PageController::class, 'exhibitionShow'])->name('exhibitions.show');

Route::get('/contacts', [PageController::class, 'contacts'])->name('contacts');

Route::get('/finance', [PageController::class, 'finance'])->name('finance');
Route::get('/contracts', [PageController::class, 'contracts'])->name('contracts');
Route::get('/stock', [PageController::class, 'stock'])->name('stock');

Route::get('/oman', [PageController::class, 'oman'])->name('oman');

Route::get('/archive', [PageController::class, 'archive'])->name('archive');
Route::get('/reports', [PageController::class, 'reports'])->name('reports');
Route::get('/data', [PageController::class, 'data'])->name('data');
Route::get('/settings', [PageController::class, 'settings'])->name('settings');

// Switch UI language (ar / en) and return to the previous page.
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');
