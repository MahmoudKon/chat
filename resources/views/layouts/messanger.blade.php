
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

        <!-- Template CSS -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('assets') }}/css/template.bundle.dark.css">
        <link rel="stylesheet" href="{{ asset('assets') }}/css/template.dark.bundle.css" media="(prefers-color-scheme: dark)">

    <body>
        <h3>{{ auth()->user()->name }}</h3>
        <!-- Layout -->
        <div class="layout overflow-hidden">
            @include('layouts.includes.nav')

            @include('layouts.includes.side')

            <!-- Chat -->
            <main class="main is-visible" id="load-chat" data-dropzone-area="">
                @include('layouts.includes.empty')
            </main>
            <!-- Chat -->

        </div>
        <!-- Layout -->

        @include('messanger.modals')

        <!-- Scripts -->
        <script src="{{ asset('assets') }}/js/vendor.js"></script>
        <script src="{{ asset('assets') }}/js/template.js"></script>
        <script src="{{ asset('assets') }}/js/jquery-3.6.1.min.js"></script>
        <script src="{{ asset('assets') }}/js/moment.js" type="text/javascript"></script>

        <script>
            $(function() {
                const AUTH_USER_ID = {{ auth()->id() }};
                // Load Conversations list
                loadConversations('conversations-list');
                function loadConversations(ele, url = '', empty = false) {
                    $.ajax({
                        url: window.location.href+url,
                        type: "GET",
                        success: function (response, textStatus, jqXHR) {
                            if (empty) $(`.${ele}`).empty();
                            $(`.${ele}`).append(response);
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

                $('body').on('click', '#tab-friends', function(e) {
                    loadConversations('users-list', 'list/users', true);
                });

                $('body').on('click', '.user-room', function(e) {
                    e.preventDefault();
                    $('.user-room').not($(this)).removeClass('open-chat');
                    $(this).addClass('open-chat');
                    $.ajax({
                        url: $(this).attr('href'),
                        type: "get",
                        success: function(response, textStatus, jqXHR) {
                            $('#load-chat').empty().append(response);
                            $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                        }
                    });
                });

                $('body').on('submit', '#send-message', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('action'),
                        type: $(this).attr('method'),
                        data: $(this).serialize(),
                        success: function(response, textStatus, jqXHR) {
                            $('[name="message"]').val('');

                            if (response.new_conversation) {
                                $('#load-chat').find('#conversation_').attr('id', `conversation_${response.message.conversation_id}`);
                                $(`.conversations-list`).prepend(response.new_conversation);
                                $('body').find('input[name="conversation_id"]').val(response.message.conversation_id);
                            } else {
                                reOrder(response.message);
                            }

                            $('#load-chat').find(`#conversation_${response.message.conversation_id}`).append(response.view);
                            $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);

                            $('body').find('.user-item.open-chat').remove();
                        }
                    });
                });

                // Reorder conversation according last send message
                function reOrder(message) {
                    console.log(message.conversation_id);
                    let ele = $('body').find(`.conversation-item[data-conversation-id="${message.conversation_id}"]`);
                    if (ele.length == 0) {
                        $('.conversations-list').prepend(conversationTemplate(message, message.conversation));
                        return;
                    }

                    let conversation = $('body').find(`[data-conversation-id=${message.conversation_id}]`);
                    conversation.find('.last-message').text(message.message);
                    conversation.find('.message-time').text(message.created_at);
                    $('.conversations-list').prepend(conversation.get(0));
                }

                function messageTemplate(message) {
                    return `<div class="message">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-responsive">
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

                function conversationTemplate (message, conversation) {
                    return `<a href="/conversations/${conversation.id}/messages" class="card conversation-item border-0 text-reset user-room" data-conversation-id="${conversation.id}">
                                <div class="card-body">
                                    <div class="row gx-5">
                                        <div class="col-auto">
                                            <div class="avatar avatar-online online-status-${conversation.users[0].id}">
                                                <img src="${conversation.image ?? conversation.users[0].image}" alt="#" class="avatar-img">
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="d-flex align-items-center mb-3">
                                                <h5 class="me-auto mb-0">${conversation.label ?? conversation.users[0].name}</h5>
                                                <span class="text-muted extra-small ms-2 message-time">${message.created_at}</span>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <div class="line-clamp me-auto">
                                                    <span class="user-typing d-none"> is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span> </span>
                                                    <span class="last-message"> ${message.message} </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- .card-body -->
                            </a>`;
                }

                Echo.channel(`private-new-message.${AUTH_USER_ID}`)
                .listen('MessageCreated', (data) => {
                    reOrder(data.message);
                    let conversation_body = $('body').find(`#conversation_${data.message.conversation_id}`);
                    if (conversation_body.length == 0) return;

                    conversation_body.append(messageTemplate(data.message));
                    $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                });

                let chatRoom = window.Echo.join(`chat`)
                            .joining((user) => {
                                $('body').find(`.online-status-${user.id}`).addClass('avatar-online');
                                $('body').find('.chat-body-inner > div').append(typing());
                                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                            })
                            .leaving((user) => {
                                $('body').find(`.online-status-${user.id}`).removeClass('avatar-online');
                                $('body').find('.chat-body-inner').find('.user-typing').remove();
                                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                            })
                            .listenForWhisper('typing', (e) => {
                                console.log(e);
                            })
                            .listenForWhisper('stoped-typing', (e) => {
                                console.log(e);
                            });

                $('body').on('focus', '[name="message"]', function (e) {
                    chatRoom.whisper('typing', (e) => {
                                id: AUTH_USER_ID
                            });
                });

                function typing() {
                    return `<div class="message user-typing">
                                <div class="message-inner">
                                    <div class="message-body">
                                        <div class="message-content">
                                            <div class="message-text">
                                                <small class="text-truncate">is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                }
            });
        </script>
    </body>
</html>
