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
        $('.user-room').not($(this)).removeClass('open-chat');
        $(this).addClass('open-chat');
        $.ajax({
            url: $(this).attr('href'),
            type: "get",
            success: function(response, textStatus, jqXHR) {
                $('#load-chat').empty().append(response.view);
                conversation_user_id = $('body').find('[data-conversation-user]').data('conversation-user');
                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
            }
        });
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
        loadConversations('conversations-list', '', {search: $(this).val()}, true);
    });


    let time = false;
    $('body').on('keydown', '[name="message"]', function(){
        chatChannel.whisper('typing', {
            typing: true,
            user_id: AUTH_USER_ID
        });

        if (time) clearTimeout(time);
        time = setTimeout( () => {
            chatChannel.whisper('typing', {
                typing: false,
                user_id: AUTH_USER_ID
            });
        }, 600);
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
            if (conversation_body.length == 0) return;

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
                                let ele = $('body').find(`[data-conversation-user="${e.user_id}"]`);
                                if (ele.length == 0) return;
                                if (e.typing) {
                                    if (ele.find('.user-typing').length == 0)
                                        ele.append(typing());
                                } else {
                                    ele.find('.user-typing').remove();
                                }
                                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                            });


/**********************************************************************************************************************************************************************
//? LINK **************************************************************** Helper Functions ****************************************************************************
**********************************************************************************************************************************************************************/


    // Load Conversations list
    let jqXHR = {abort: function () {}};
    loadConversations('conversations-list');

    function loadConversations(ele, url = '', data = {}, empty = false) {
        jqXHR.abort();
        jqXHR = $.ajax({
            url: window.location.href+url,
            type: "GET",
            data: data,
            success: function (response, textStatus, jqXHR) {
                if (empty) $(`.${ele}`).empty();
                $(`.${ele}`).append(response);
            }
        });
    }

    function updateLastActive(id) {
        $.ajax({
            url: window.location.href+'/update/last-seen',
            type: "POST",
            data: {user_id: id},
            success: function (response, textStatus, jqXHR) {
                console.log(response);
            }
        });
    }


    // Reorder conversation according last send message
    function reOrder(message, user_id) {
        let ele = $('body').find(`.user-room[data-user-id="${user_id}"]`);
        let sender = message.user_id == AUTH_USER_ID ? 'You: ' : `${message.user.name}: `;
        let msg = message.type == 'text' ? message.message : 'Send File';
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
                                <div class="message-text">
                                    <p>${message.message} </p>
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
});
