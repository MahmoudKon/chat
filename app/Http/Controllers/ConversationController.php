<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConversationRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    use UploadFile;

    public function index()
    {
        if (request()->ajax()) return $this->conversations();
        return view('messanger.index');
    }

    public function conversations()
    {
        $conversations = auth()->user()->conversations()->with([
            'lastMessage',
            'users' => function($query) {
                $query->where('user_id', '<>', auth()->id());
            }
            ])->get();

        return view('messanger.includes.conversations', compact('conversations'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        return view('messanger.create', compact('users'));
    }

    public function store(ConversationRequest $request)
    {
        DB::beginTransaction();
            $new = false;
            $conversation = auth()->user()->conversations()->whereHas('users', function($query) use($request) {
                                $query->where('user_id', $request->user_id);
                            })->when($request->label, function($query) use($request) {
                                $query->where('label', $request->label);
                            })->first();

            if (! $conversation) {
                $new = true;
                $conversation = Conversation::create([
                    'label' => $request->label,
                    'user_id' => auth()->id(),
                    'image' => $request->image ? $this->uploadImage($request->image, 'conversations') : null
                ]);
                $conversation->users()->attach([auth()->id(), $request->user_id]);
            }

            $conversation->load(['users' => function($query) {
                $query->where('user_id', '<>', auth()->id());
            }]);
        DB::commit();

        return response()->json([
                'is_new' => $new,
                'view' => view('messanger.includes.conversations', ['conversations' => [$conversation]])->render(),
                'conversation_id' => $conversation->id
            ], 200);
    }

    public function show(Conversation $conversation)
    {
        return $conversation->load('users');
    }
}
