<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/welcome', function () {
    return view('welcome');
});


Route::middleware('auth')->group(function() {

    Route::get('/', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('list/users', [ConversationController::class, 'users'])->name('conversations.users');


    Route::get('conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');

    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    Route::get('new-conversations/{user}/messages', [MessageController::class, 'newChat'])->name('new-conversations.messages.index');
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index'])->name('conversations.messages.index');
    Route::post('messages', [MessageController::class, 'store'])->name('message.store');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('message.show');
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);
});

