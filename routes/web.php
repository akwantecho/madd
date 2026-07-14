<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/tasks', [PageController::class, 'tasks'])->name('tasks');
Route::post('/tasks', [PageController::class, 'taskStore'])->name('tasks.store');
Route::put('/tasks/{task}', [PageController::class, 'taskUpdate'])->name('tasks.update');
Route::delete('/tasks/{task}', [PageController::class, 'taskDestroy'])->name('tasks.destroy');
Route::get('/tasks/{task}/details', [PageController::class, 'taskDetails'])->name('tasks.details');
Route::post('/tasks/{task}/subtasks', [PageController::class, 'subtaskStore'])->name('subtasks.store');
Route::put('/subtasks/{subtask}', [PageController::class, 'subtaskUpdate'])->name('subtasks.update');
Route::delete('/subtasks/{subtask}', [PageController::class, 'subtaskDestroy'])->name('subtasks.destroy');
Route::post('/tasks/{task}/assignees', [PageController::class, 'assigneeStore'])->name('assignees.store');
Route::delete('/assignees/{assignee}', [PageController::class, 'assigneeDestroy'])->name('assignees.destroy');

Route::get('/exhibitions', [PageController::class, 'exhibitions'])->name('exhibitions');
Route::post('/exhibitions', [PageController::class, 'exhibitionStore'])->name('exhibitions.store');
Route::get('/exhibitions/{id}', [PageController::class, 'exhibitionShow'])->name('exhibitions.show');
Route::put('/exhibitions/{exhibition}', [PageController::class, 'exhibitionUpdate'])->name('exhibitions.update');
Route::delete('/exhibitions/{exhibition}', [PageController::class, 'exhibitionDestroy'])->name('exhibitions.destroy');

Route::get('/contacts', [PageController::class, 'contacts'])->name('contacts');
Route::post('/contacts', [PageController::class, 'contactStore'])->name('contacts.store');
Route::put('/contacts/{contact}', [PageController::class, 'contactUpdate'])->name('contacts.update');
Route::delete('/contacts/{contact}', [PageController::class, 'contactDestroy'])->name('contacts.destroy');

Route::get('/finance', [PageController::class, 'finance'])->name('finance');
Route::get('/finance/accounts/{id}', [PageController::class, 'accountShow'])->name('finance.account');
Route::get('/contracts', [PageController::class, 'contracts'])->name('contracts');
Route::get('/contracts/create', [PageController::class, 'contractCreate'])->name('contracts.create');
Route::post('/contracts', [PageController::class, 'contractStore'])->name('contracts.store');
Route::get('/contracts/{contract}/edit', [PageController::class, 'contractEdit'])->name('contracts.edit');
Route::put('/contracts/{contract}', [PageController::class, 'contractUpdate'])->name('contracts.update');
Route::delete('/contracts/{contract}', [PageController::class, 'contractDestroy'])->name('contracts.destroy');
Route::get('/contracts/{id}', [PageController::class, 'contractShow'])->name('contracts.show');

Route::post('/invoices/quick-client', [PageController::class, 'quickClientStore'])->name('invoices.quickClient');
Route::post('/invoices/quick-exhibition', [PageController::class, 'quickExhibitionStore'])->name('invoices.quickExhibition');
Route::post('/invoices/quick-stock', [PageController::class, 'quickStockStore'])->name('invoices.quickStock');
Route::get('/invoices/create', [PageController::class, 'invoiceCreate'])->name('invoices.create');
Route::post('/invoices', [PageController::class, 'invoiceStore'])->name('invoices.store');
Route::get('/invoices/{invoice}/edit', [PageController::class, 'invoiceEdit'])->name('invoices.edit');
Route::put('/invoices/{invoice}', [PageController::class, 'invoiceUpdate'])->name('invoices.update');
Route::delete('/invoices/{invoice}', [PageController::class, 'invoiceDestroy'])->name('invoices.destroy');
Route::get('/invoices/{id}', [PageController::class, 'invoiceShow'])->name('invoices.show');
Route::get('/stock', [PageController::class, 'stock'])->name('stock');
Route::post('/stock', [PageController::class, 'stockStore'])->name('stock.store');
Route::put('/stock/{stockItem}', [PageController::class, 'stockUpdate'])->name('stock.update');
Route::delete('/stock/{stockItem}', [PageController::class, 'stockDestroy'])->name('stock.destroy');
Route::post('/notices', [PageController::class, 'noticeStore'])->name('notices.store');
Route::put('/notices/{notice}', [PageController::class, 'noticeUpdate'])->name('notices.update');
Route::delete('/notices/{notice}', [PageController::class, 'noticeDestroy'])->name('notices.destroy');

Route::get('/archive', [PageController::class, 'archive'])->name('archive');
Route::get('/reports', [PageController::class, 'reports'])->name('reports');
Route::get('/data', [PageController::class, 'data'])->name('data');
Route::get('/settings', [PageController::class, 'settings'])->name('settings');
Route::post('/settings', [PageController::class, 'settingsSave'])->name('settings.save');
Route::post('/users', [PageController::class, 'userStore'])->name('users.store');
Route::put('/users/{user}', [PageController::class, 'userUpdate'])->name('users.update');
Route::delete('/users/{user}', [PageController::class, 'userDestroy'])->name('users.destroy');

// Switch UI language (ar / en) and return to the previous page.
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');
