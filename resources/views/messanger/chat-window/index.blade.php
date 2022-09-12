<div class="container h-100">

    <div class="d-flex flex-column h-100 position-relative">
        @include('messanger.chat-window.header')

        <!-- Chat: Content -->
        <div class="chat-body hide-scrollbar flex-1 h-100">
            <div class="chat-body-inner" style="padding-bottom: 45px">
                <div class="py-6 py-lg-12" id="conversation_{{ $conversation->id }}">

                    @foreach ($conversation->messages->sortBy('id') as $message)
                        @include('messanger.chat-window.message')
                    @endforeach

                </div>
            </div>
        </div>
        <!-- Chat: Content -->

        @include('messanger.chat-window.footer')
    </div>

</div>
