(function ($) {
    var lastId = 0;
    function poll() {
        $.ajax({
            dataType: "json",
            url: ROOT + 'notifications',
            timeout: 60000,
            headers: {
                'X-Last-ID': lastId
            }
         })
        .success(function (data) {
            handleData(data);
            for (var i = 0; i < data.length; i++) {
                lastId = Math.max(lastId, data[i].id);
            }
            poll();
        })
        .fail(poll);
    }

    function handleData(notifications) {
        $(window).triggerHandler('notifications', [notifications]);
        for (var i = 0; i < notifications.length; i++) {
            $(window).triggerHandler('notification', [notifications[i]]);
            if (notifications[i].type != -1) { // TODO for chat
                showNotification(notifications[i]);
            }
        }
    }

    function showNotification(notification) {
        addMessage(notification.message, 'info', function () {
            $.get(ROOT + 'notifications/dismiss/' + notification.id);
            return true;
        });
    }

    poll();
}) ($);
