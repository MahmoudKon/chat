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
        $this->users();
        if (request()->ajax()) return $this->users();
        return view('messanger.index');
    }

    public function users()
    {
        $users = User::where('id', '<>', auth()->id())
        ->when(request('search'), function($query) {
            $query->where('name', 'LIKE', '%'.request('search').'%')->orWhere('email', 'LIKE', '%'.request('search').'%');
        })
        ->with([
            'conversations' => function($query) {
                $query->whereHas('users', function($query) {
                    $query->where('useR_id', auth()->id());
                });
            }
        ])->paginate(8);

        $next_page = $users->currentPage() + 1;
        $next_page = $next_page <= $users->lastPage() ? $next_page : null;

        $users = $users->sortByDesc(function($user) {
            if (isset($user->conversations[0]))
                return $user->conversations[0]->last_message_id;
        });

        return response()->json([
            'view' => view('messanger.includes.list-users', compact('users'))->render(),
            'next_page' => $next_page
        ]);
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

    public function updateLastSeen()
    {
        User::find(request('user_id'))->update(['last_seen' => now()]);
        return 'updated';
    }

    public function userDetails(User $user)
    {
        return view('messanger.user.show', compact('user'));
    }
}
