<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChurchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChurchController::class, 'home'])->name('home');
Route::get('/contact', [ChurchController::class, 'contact'])->name('contact');
Route::post('/contact', [ChurchController::class, 'sendContact'])->name('contact.send');

Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:6,1')->name('admin.authenticate');
Route::middleware('auth')->prefix('dashboard')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::match(['post', 'put'], '/content', [AdminController::class, 'saveEntry'])->name('entries.save');
    Route::delete('/content/{entry}', [AdminController::class, 'deleteEntry'])->name('entries.delete');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings');
    Route::delete('/messages/{message}', [AdminController::class, 'deleteMessage'])->name('messages.delete');
});

Route::get('/ministries/{slug}', [ChurchController::class, 'detail'])->defaults('section', 'ministries')->name('detail.ministry');
Route::get('/events/{slug}', [ChurchController::class, 'detail'])->defaults('section', 'events')->name('detail.event');
Route::get('/sermons/{slug}', [ChurchController::class, 'detail'])->defaults('section', 'sermons')->name('detail.sermon');
Route::get('/devotionals/{slug}', [ChurchController::class, 'detail'])->defaults('section', 'devotionals')->name('detail.devotional');

Route::get('/ministries', [ChurchController::class, 'listing'])->defaults('section', 'ministries')->name('listing.ministries');
Route::get('/sermons', [ChurchController::class, 'listing'])->defaults('section', 'sermons')->name('listing.sermons');
Route::get('/events', [ChurchController::class, 'listing'])->defaults('section', 'events')->name('listing.events');
Route::get('/devotionals', [ChurchController::class, 'listing'])->defaults('section', 'devotionals')->name('listing.devotionals');

Route::get('/{slug}', [ChurchController::class, 'page'])->where('slug', '[A-Za-z0-9-]+')->name('page');
