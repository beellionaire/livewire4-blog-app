<?php

use App\Livewire\PostList;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function() {
    return redirect('/blog');
})->name('home');

Route::get('/blog', PostList::class)->name('blog.index');
Route::livewire('/blog/{slug}', 'pages::posts.show')->name('blog.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('posts')->name('posts.')->middleware('can:create posts')->group(function() {
        Route::livewire('/', 'pages::posts.index')->name('index');
        Route::livewire('/create', 'pages::posts.create')->name('create');
        Route::livewire('/{post}/edit', 'pages::posts.edit')->name('edit');
        
    });

    Route::prefix('users')->name('users.')->middleware('can:manage users')->group(function() {
        Route::livewire('/', 'pages::users.index')->name('index');
        Route::livewire('/create', 'pages::users.create')->name('create');
        Route::livewire('/{user}/edit', 'pages::users.edit')->name('edit');
    });

    Route::prefix('categories')->name('categories.')->middleware('can:manage roles')->group(function() {
        Route::livewire('/', 'pages::category.index')->name('index');
        Route::livewire('/create', 'pages::category.create')->name('create');
        Route::livewire('/{category}/edit', 'pages::category.edit')->name('edit');
    });

    Route::livewire('/', 'pages::comments.index')->middleware('can:manage roles')->name('comments.index');
   
    
});

require __DIR__.'/settings.php';
