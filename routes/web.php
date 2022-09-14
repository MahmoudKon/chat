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

    Route::post('update/last-seen', [ConversationController::class, 'updateLastSeen'])->name('conversations.index');
    Route::get('user/{user}/details', [ConversationController::class, 'userDetails'])->name('user.details');

    Route::get('/', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');

    Route::get('conversation/{user}/messages', [MessageController::class, 'index'])->name('conversation.user.messages');
    // Route::get('conversations/{conversation}/messages', [MessageController::class, 'index'])->name('conversations.messages.index');
    Route::post('messages', [MessageController::class, 'store'])->name('message.store');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('message.show');
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);
});

