$(function() {

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

    let conversation_user_id = null;
    $('body').on('click', '.user-room', function(e) {
        e.preventDefault();
        let btn = $(this);
        $('.user-room').not(btn).removeClass('open-chat');
        btn.addClass('open-chat');
        $.ajax({
            url: btn.attr('href'),
            type: "get",
            success: function(response, textStatus, jqXHR) {
                $('#load-chat').empty().append(response.view);
                conversation_user_id = btn.data('user-id');
                next_messages_page = response.next_page;
                conversation_id = response.conversation.id;

                $.each(response.messages.data, function (key, message) {
                    $('body').find('[data-conversation-user]').prepend(messageTemplate(message, message.user_id == AUTH_USER_ID ? 'message-out' : ''));
                });

                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .hide-scrollbar').prop("scrollHeight")}, 200);
            }
        });
    });


    $('#load-chat .hide-scrollbar').scroll(function () {
        console.log('asdasdasd');
        // if ( $(this).scrollTop() + $(this).innerHeight() == $(this)[0].scrollHeight && next_messages_page !== null)
        //     loadMoreMessages(response.conversation.id, conversation_user_id);
    });


    $('body').on('submit', '#send-message', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: new FormData($(this)[0]),
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function(response, textStatus, jqXHR) {
                $('[name="message"]').val('');
                $('[name="file"]').val('');
                reOrder(response.message, response.user_id);
                $('#load-chat').find(`[data-conversation-user='${response.user_id}']`).append(messageTemplate(response.message, 'message-out'));
                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
            }
        });
    });


    $('body').on('change', '#input-file', function() {
        $('#send-message').submit();
    });


    $('body').on('click', '[data-bs-target]', function(e) {
        e.preventDefault();
        let btn = $(this);
        $.ajax({
            url: btn.attr('href'),
            type: "get",
            success: function (response, textStatus, jqXHR) {
                $(`${btn.data('bs-target')}`).find('.modal-content').empty().append(response);
            }
        });
    });

    $('body').on('keyup', 'input#search', function(e) {
        loadConversations(1, {search: $(this).val()}, true);
    });

    $('#tab-content-chats .hide-scrollbar').scroll(function () {
        if ( $(this).scrollTop() + $(this).innerHeight() == $(this)[0].scrollHeight && next_page !== null)
            loadConversations(next_page, {search: $('input#search').val()});
    });


    $('body').on('keyup', 'input#users-search', function(e) {
        loadUsers(1, {search: $('input#users-search').val()}, true);
    });

    $('#tab-content-friends .hide-scrollbar').scroll(function () {
        if ( $(this).scrollTop() + $(this).innerHeight() == $(this)[0].scrollHeight && next_page !== null)
            loadUsers(next_page, {search: $('input#users-search').val()});
    });


    let time = false;
    $('body').on('keydown', '[name="message"]', function(){
        if (event.keyCode > 90 || event.keyCode < 65) return;

        chatChannel.whisper('typing', {
            typing: true,
            auth_id: AUTH_USER_ID,
            user_id: $('input[name="user_id"]').val()
        });

        if (time) clearTimeout(time);
        time = setTimeout( () => {
            chatChannel.whisper('typing', {
                typing: false,
                auth_id: AUTH_USER_ID,
                user_id: $('input[name="user_id"]').val()
            });
        }, 600);
    });


    $('#tab-friends').click(function() {
        loadUsers(1, {}, true);
    });


    $('#tab-chats').click(function() {
        loadConversations(1, {}, true);
    });


/**********************************************************************************************************************************************************************
//! SECTION **************************************************************** PUSHER Functions *************************************************************************
**********************************************************************************************************************************************************************/

    // To get message from pusher and append it
    window.Echo.private(`new-message.${AUTH_USER_ID}`)
        .listen('MessageCreated', (data) => {
            $('body').find(`[data-conversation-user="${conversation_user_id}"]`).find('.user-typing').remove();
            reOrder(data.message, data.message.user_id);
            let conversation_body = $('body').find(`[data-conversation-user="${data.message.user_id}"]`);
            if (conversation_body.length == 0) {
                try { audio.play(); } catch (error) {}
                return;
            }

            conversation_body.append(messageTemplate(data.message));
            $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
        });

    let chatChannel = window.Echo.join(`chat`)
                            .joining((user) => { // This user is join to chat page
                                $('body').find(`.online-status-${user.id}`).addClass('avatar-online');
                                $('body').find(`.online-status-${user.id}-text`).text('Online');
                            })
                            .leaving((user) => { // This user is leaving to chat page
                                $('body').find(`.online-status-${user.id}`).removeClass('avatar-online');
                                $('body').find(`.online-status-${user.id}-text`).text('Offline');
                                updateLastActive(user.id);
                            })
                            .listenForWhisper('typing', (e) => {
                                if (AUTH_USER_ID != e.user_id) return;
                                toggleTyping(e.typing, e.auth_id);
                                toggleTypingInChat(e.typing, e.auth_id);
                                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                            });


/**********************************************************************************************************************************************************************
//? LINK **************************************************************** Helper Functions ****************************************************************************
**********************************************************************************************************************************************************************/


    // Load Conversations list
    let jqXHR = {abort: function () {}}; // init empty object
    let next_page  = 1;
    let next_messages_page = 1;
    loadConversations();

    function loadConversations(page = 1, data = {}, empty = false) {
        loadData('conversations-list', `?page=${page}`, data, empty)
    }

    function loadUsers(page = 1, data = {}, empty = false) {
        loadData('users-list', `users?page=${page}`, data, empty)
    }

    function loadData(ele, url = '', data = {}, empty = false) {
        jqXHR.abort();
        jqXHR = $.ajax({
            url: window.location.href+url,
            type: "GET",
            data: data,
            success: function (response) {
                next_page = response.next_page;
                if (empty) $(`.${ele}`).empty();
                $(`.${ele}`).append(response.view);

            }
        });
    }

    function loadMoreMessages(conversation, user_id) {
        $.ajax({
            url: window.location.href+`conversation/${conversation}/messages/load-more?page=${next_messages_page}`,
            type: "GET",
            success: function (response) {
                next_messages_page = response.next_page;
                $.each(response.messages.data, function (key, message) {
                    $('body').find(`[data-conversation-user=${user_id}]`).prepend(messageTemplate(message, message.user_id == AUTH_USER_ID ? '' : 'message-out'));
                });
            }
        });
    }

    function updateLastActive(id) {
        $.ajax({
            url: window.location.href+'update/last-seen',
            type: "get",
            data: {user_id: id},
            success: function (response, textStatus, jqXHR) {
            }
        });
    }


    // Reorder conversation according last send message
    function reOrder(message, user_id) {
        let ele = $('body').find(`.user-room[data-user-id="${user_id}"]`);
        let sender = message.user_id == AUTH_USER_ID ? 'You: ' : `${message.user.name}: `;
        let msg = message.type == 'text' ? message.message : `Send ${message.type}`;
        ele.find('.last-message').text(sender + ' ' + msg);
        ele.find('.message-time').text(message.created_at);
        $('.conversations-list').prepend(ele.get(0));
    }


    function messageTemplate(message, new_class = '') {
        return `<div class="message ${new_class}">
                    <a href="/user/${message.user_id}/details" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-responsive">
                        <img class="avatar-img" src="${message.user.image}" alt="">
                    </a>

                    <div class="message-inner">
                        <div class="message-body">
                            <div class="message-content">
                                <div class="${message.type == 'text' ? 'message-text' : ''}">
                                    <p>${buildFile (message.type, message.message)} </p>
                                </div>
                            </div>
                        </div>

                        <div class="message-footer">
                            <span class="extra-small text-muted">${message.created_at}</span>
                        </div>
                    </div>
                </div>`;
    }


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


    function toggleTyping(check, user_id)
    {
        let user_item = $('.conversations-list, .users-list').find(`[data-user-id="${user_id}"]`);
        if (user_item.length == 0) return;
        if (check) {
            user_item.find('.last-message').addClass('d-none');
            user_item.find('.user-typing').removeClass('d-none');
        } else {
            user_item.find('.last-message').removeClass('d-none');
            user_item.find('.user-typing').addClass('d-none');
        }
    }


    function toggleTypingInChat(check, user_id)
    {
        let ele = $('body').find(`[data-conversation-user="${user_id}"]`);
        if (ele.length == 0) return;
        if (check) {
            if (ele.find('.user-typing').length == 0)
                ele.append(typing());
        } else {
            ele.find('.user-typing').remove();
        }
    }

    function buildFile (type, src) {
        if (type == 'text/plain') return `<a href='${src}' target='_blank' class='btn btn-success'> ${type} </a>`;
        let text = '';
        let type_array = type.split('/');
        switch (type_array[0]) {
            case 'text':
                text = src;
            break;

            case 'image':
                text = `<img src='${src}' width='100%'>`;
            break;

            case 'audio':
                text = `<audio controls width='100%'> <source src="${src}"></audio>`;
            break;

            case 'video':
                text = `<video width="100%" controls> <source src="${src}"> </video>`;
            break;

            default:
                text = `<a href='${src}' target='_blank' class='btn btn-success'> ${type} </a>`;
                break;
        }

        return text;
    }
});
