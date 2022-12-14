<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class MessageController extends Controller
{
    use UploadFile;

    public function index(User $user)
    {
        $conversation = auth()->user()->conversations()->whereHas('users', function($query) use($user) {
                                $query->where('user_id', $user->id);
                            })
                            ->with([
                                'users' => function($query) {
                                    $query->where('user_id', '<>', auth()->id());
                            }])->first();

        if ($conversation) {
            $messages = $conversation->messages()->paginate(5);
        } else {
            $messages = [];
            $conversation = new Conversation();
        }

        return response()->json(['view' => view('messanger.chat-window.index', compact('conversation', 'user'))->render(), 'conversation' => $conversation] + $this->getMessages($conversation->id), 200);
    }

    public function getMessages($conversation)
    {
        $messages = Message::where('conversation_id', $conversation)->orderBy('created_at', 'DESC')->paginate(5);

        $next_page = $messages->currentPage() + 1;
        $next_page = $next_page <= $messages->lastPage() ? $next_page : null;

        return [
            'messages' => $messages,
            'next_page' => $next_page
        ];
    }

    public function store(MessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $conversation = $this->getConversation($request->conversation_id, $request->user_id);


            $message = $conversation->messages()->create([
                'user_id' => auth()->id(),
                'type'    => $request->message ? 'text' : $request->file->getMimeType(),
                'message' => $request->file ? $this->uploadImage($request->file, 'messages') : $request->message ,
            ]);

            $message->users()->attach([
                auth()->id() => ['read_at' => now()],
                $request->user_id => ['read_at' => now()],
            ]);

            $conversation->update(['last_message_id' => $message->id]);
            DB::commit();

            $message->load(['user', 'conversation', 'conversation.users' => function($query) { $query->where('user_id', '<>', auth()->id()); }]);
            broadcast(new MessageCreated($message, $request->user_id));
            return [
                'user_id'    => $request->user_id,
                'message' => $message
                // 'view'    => view('messanger.chat-window.message', compact('message'))->render()
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
            $conversation = auth()->user()->conversations()->find($conversation_id);
        } else {
            $conversation = auth()->user()->conversations()
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
