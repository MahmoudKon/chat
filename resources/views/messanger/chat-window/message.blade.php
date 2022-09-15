<div class="message {{ $message->user_id == auth()->id() ? 'message-out' : '' }}">
    <a href="{{ url("user/$message->user_id/details") }}" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-responsive">
        <img class="avatar-img" src="{{ $message->user->image }}" alt="">
    </a>

    <div class="message-inner">
        <div class="message-body">
            <div class="message-content">
                <div class=" {{ $message->type == 'text' ? 'message-text' : '' }}">
                    <p onload="buildFile(this, '{{ $message->type }}', '{{ $message->message }}')"></p>
                </div>
            </div>
        </div>

        <div class="message-footer">
            <span class="extra-small text-muted">{{ $message->created_at }}</span>
        </div>
    </div>
</div>

{{-- Not Used --}}
