<?php

use App\Events\MessageSentEvent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    return view('dashboard', [
        'users' => User::whereNot('id', auth()->id())->get()
    ]);
})->middleware(['auth'])->name('dashboard');


Route::get('/chat/{friend}', function(User $friend){
    return view('chat', [
        'friend' => $friend
    ]);
})->middleware(['auth'])->name('chat');

Route::get('/messages/{friend}', function(User $friend){
    return Chat::query()
    ->where(function($q) use($friend){
        $q->where('sender_id', auth()->id())
        ->where('receiver_id', $friend->id);
    })->orWhere(function($q) use($friend){
        $q->where('receiver_id', auth()->id())
        ->where('sender_id', $friend->id);
    })->with('sender', 'receiver')->orderBy('id', 'asc')->get();
} )->name('messages.chat')->middleware(['auth']);

Route::post('/messages/{friend}', function(User $friend){
    $message = Chat::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $friend->id,
        'text' => request()->input('message')
    ]);

    broadcast(new MessageSentEvent($message));

    return $message;
});

require __DIR__.'/auth.php';
