@foreach ($conversations as $conversation)
    <a href="{{ route('conversations.messages.index', $conversation) }}" class="card conversation-item border-0 text-reset user-room" data-conversation-id="{{ $conversation->id }}">
        <div class="card-body">
            <div class="row gx-5">
                <div class="col-auto">
                    <div class="avatar avatar-online">
                        <img src="{{ $conversation->image ?? $conversation->users[0]->image }}" alt="#" class="avatar-img">
                    </div>
                </div>

                <div class="col">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="me-auto mb-0">{{ $conversation->label ?? $conversation->users[0]->name }}</h5>
                        <span class="text-muted extra-small ms-2 message-time">{{ $conversation->lastMessage ? $conversation->lastMessage->created_at : '' }}</span>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="line-clamp me-auto last-message">{{ $conversation->lastMessage ? $conversation->lastMessage->message : '' }}</div>
                    </div>
                </div>
            </div>
        </div><!-- .card-body -->
    </a>
@endforeach
