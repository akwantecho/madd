<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/exhibitions', [PageController::class, 'exhibitions'])->name('exhibitions');
Route::get('/customers', [PageController::class, 'customers'])->name('customers');
Route::get('/finance', [PageController::class, 'finance'])->name('finance');
Route::get('/archive', [PageController::class, 'archive'])->name('archive');
Route::get('/settings', [PageController::class, 'settings'])->name('settings');

// Switch UI language (ar / en) and return to the previous page.
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');
