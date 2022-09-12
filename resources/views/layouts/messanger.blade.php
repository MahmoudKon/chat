
<!DOCTYPE html>
<html lang="en">
    <!-- Head -->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1, shrink-to-fit=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Messenger</title>

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/icon.png') }}" type="image/x-icon">

        <!-- Font -->
        {{-- <link rel="preconnect" href="https://fonts.gstatic.com">
    	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700" rel="stylesheet"> --}}

        <!-- Template CSS -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('assets') }}/css/template.bundle.css">
        <link rel="stylesheet" href="{{ asset('assets') }}/css/template.dark.bundle.css" media="(prefers-color-scheme: dark)">

    <body>
        <h3>{{ auth()->user()->name }}</h3>
        <!-- Layout -->
        <div class="layout overflow-hidden">
            @include('layouts.includes.nav')

            @include('layouts.includes.side')

            <!-- Chat -->
            <main class="main is-visible" id="load-chat" data-dropzone-area=""></main>
            <!-- Chat -->

        </div>
        <!-- Layout -->

        @include('messanger.modals')

        <!-- Scripts -->
        <script src="{{ asset('assets') }}/js/vendor.js"></script>
        <script src="{{ asset('assets') }}/js/template.js"></script>
        <script src="{{ asset('assets') }}/js/jquery-3.6.1.min.js"></script>
        <script src="{{ asset('assets') }}/js/moment.js" type="text/javascript"></script>
        <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

        <script>
            $(function() {
                const AUTH_USER_ID = {{ auth()->id() }};
                loadData();
                function loadData(empty = false) {
                    $(`.conversations-list`).addClass('load');
                    $.ajax({
                        url: window.location.href,
                        type: "GET",
                        success: function (response, textStatus, jqXHR) {
                            if (empty) $(`.conversations-list`).empty();
                            $(`.conversations-list`).append(response);
                            $(`.conversations-list`).removeClass('load');
                        }
                    });
                }

                $('#create-conversation').click(function() {
                    $.ajax({
                        url: '/conversations/create',
                        type: "GET",
                        success: function (response, textStatus, jqXHR) {
                            $('#tab-content-create-chat').empty().append(response);
                        }
                    });
                });

                $('body').on('submit', '#create-conversation-form', function(e) {
                    e.preventDefault();
                    let form = $(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: new FormData(form[0]),
                        dataType: 'JSON',
                        processData: false,
                        contentType: false,
                        success: function (response, textStatus, jqXHR) {
                            let ele = response;
                            if (response.is_new) $(`.conversations-list`).prepend(response.view);
                            $('#open-list-chat').click();
                            $(`[data-conversation-id="${response.conversation_id}"]`).click();
                        }
                    });
                });

                $('body').on('click', '.user-room', function(e) {
                    e.preventDefault();
                    $('#load-chat').addClass('load');
                    $('.user-room').not($(this)).removeClass('open-chat');
                    $(this).addClass('open-chat');
                    $.ajax({
                        url: $(this).attr('href'),
                        type: "get",
                        success: function(response, textStatus, jqXHR) {
                            $('#load-chat').empty().append(response);
                            $('#load-chat').removeClass('load');
                            $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                        }
                    });
                });

                $('body').on('submit', '#send-message', function(e) {
                    e.preventDefault();
                    $(this).parent().removeClass('load');
                    $('.empty-message').remove();
                    $.ajax({
                        url: $(this).attr('action'),
                        type: $(this).attr('method'),
                        data: $(this).serialize(),
                        success: function(response, textStatus, jqXHR) {
                            $('[name="message"]').val('');
                            $('#load-chat').find(`#conversation_${response.message.conversation_id}`).append(response.view);
                            $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                            reOrder(response.message);
                        }
                    });
                });

                function reOrder(message) {
                    let ele = $('body').find('.open-chat');
                    if (ele.hasClass('new-chat') || $('.conversation-item').length == 0) {
                        loadData('conversations-list', window.location.href, true);
                        ele.remove();
                        return;
                    }

                    let conversation = $('body').find(`[data-conversation-id=${message.conversation_id}]`);
                    conversation.find('.last-message').text(message.message);
                    conversation.find('.message-time').text(message.created_at);
                    let text = conversation.get(0);
                    $('.conversations-list').prepend(conversation.get(0));
                }

                function messageTemplate(message) {
                    return `<div class="message">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-online avatar-responsive">
                                    <img class="avatar-img" src="${message.user.image}" alt="">
                                </a>

                                <div class="message-inner">
                                    <div class="message-body">
                                        <div class="message-content">
                                            <div class="message-text">
                                                <p>${message.message}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="message-footer">
                                        <span class="extra-small text-muted">${message.created_at}</span>
                                    </div>
                                </div>
                            </div>`;
                }

                var pusher = new Pusher('8a54692d8cd3a078d328', {
                    cluster: 'eu',
                    authEndpoint: '/broadcasting/auth',
                    auth: {headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}}
                });

                console.log(pusher.user);

                var channel = pusher.subscribe(`private-new-message.${AUTH_USER_ID}`);
                channel.bind('App\\Events\\MessageCreated', function(data) {
                    reOrder(data.message);
                    let ele = $('#load-chat').find(`#conversation_${data.message.conversation_id}`);
                    if (ele.length == 0) return;
                    ele.append(messageTemplate(data.message));
                    $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                });

                // window.Echo.channel(`new-message.${AUTH_USER_ID}`).listen('\\App\\Events\\MessageCreated', (data) => {
                //     reOrder(data.message);
                //     if ($('body').find(`#conversation_${data.message.conversation_id}`).length == 0) return;
                //     $('#load-chat').find('.chat-body-inner').append(messageTemplate(data.message));
                //     $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                // });
            });
        </script>
    </body>
</html>
