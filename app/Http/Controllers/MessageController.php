<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class MessageController extends Controller
{
    public function index($id)
    {
        $conversation = auth()->user()->conversations()->with([
            'lastMessage',
            'users' => function($query) {
                $query->where('user_id', '<>', auth()->id());
            }])->where('conversation_id', $id)->first();

        $user = $conversation->users[0];

        return view('messanger.chat-window.index', compact('conversation', 'user'));
    }

    public function newChat(User $user)
    {
        $conversation = new Conversation();
        return view('messanger.chat-window.index', compact('user', 'conversation'));
    }

    public function store(MessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $conversation = $this->getConversation($request->conversation_id, $request->user_id);

            $message = $conversation->messages()->create([
                'user_id' => auth()->id(),
                'message' => $request->message
            ]);

            $message->users()->attach([
                auth()->id() => ['read_at' => now()],
                $request->user_id => ['read_at' => now()],
            ]);

            $conversation->update(['last_message_id' => $message->id]);
            DB::commit();

            $message->load(['user', 'conversation', 'conversation.users' => function($query) { $query->where('user_id', '<>', auth()->id()); }]);
            foreach ($conversation->users()->where('user_id', '<>', auth()->id())->pluck('user_id') as $user_id) {
                broadcast(new MessageCreated($message, $user_id));
            }

            $new_conversation = false;
            if (! $request->conversation_id) {
                $new_conversation = view('messanger.includes.conversations', ['conversations' => [$message->conversation]])->render();
            }
            return [
                'new_conversation' => $new_conversation,
                'message' => $message,
                'view'    => view('messanger.chat-window.message', compact('message'))->render()
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e);
        }
    }

    public function show(Message $message)
    {
        $message->load('user');
        return view('backend.massenger.includes.message', compact('message'));
    }

    protected function getConversation($conversation_id = null, $user_id = null)
    {
        if ($conversation_id) {
            $conversation = auth()->user()->conversations()->with('messages')->find($conversation_id);
        } else {
            $conversation = auth()->user()->conversations()->with('messages')
                            ->where('type', 'peer')
                            ->whereHas('users', function($query) use($user_id) {
                                $query->where('user_id', $user_id);
                            })->first();
        }

        if (! $conversation) {
            $conversation = Conversation::create(['user_id' => auth()->id()]);
            $conversation->users()->attach([auth()->id(), $user_id]);
        }
        return $conversation;
    }

    protected function destroy($id)
    {
        auth()->user()->messages()->where('message_id', $id)->delete();
        return 'deleted';
    }
}