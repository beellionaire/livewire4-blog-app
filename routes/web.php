<?php

use App\Livewire\PostList;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Route;

Route::get('/', function() {
    return redirect()->route('blog.index');
})->name('home');

Route::get('/blog', PostList::class)->name('blog.index');
Route::livewire('/blog/{slug}', 'pages::posts.show')->name('blog.show');

Route::get('/unsubscribe/{token}', function($token) {
    $subscriber = Subscriber::where('token', $token)->firstOrFail();

    if ($subscriber) {
        $subscriber->delete();
        return view('unsubscribed');
    } 

    abort('404');

})->name('unsubscribed');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

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

    Route::livewire('/comments', 'pages::comments.index')->middleware('can:manage roles')->name('comments.index');
   
    
});

require __DIR__.'/settings.php';
