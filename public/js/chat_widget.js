// This is a complete hack script (last-minute chat system addition)
// Needs to be redone
$(document).ready(function() {
    var widget = document.createElement('div');
    widget.style.bottom = '0px';
    widget.style.position = 'fixed';
    widget.style.width = '200px';
    widget.style.padding = '3px 0px';
    widget.style.zIndex = 10;
    document.body.appendChild(widget);
    var chatArea = document.createElement('div');
    chatArea.style.height = '250px';
    chatArea.style.border = '1px solid black';
    chatArea.style.position = 'relative';
    chatArea.style.display = 'none';
    chatArea.style.background = 'white';
    widget.appendChild(chatArea);
    var messageArea = document.createElement('div');
    messageArea.style.overflowY = 'scroll';
    messageArea.style.height = '100%';
    chatArea.appendChild(messageArea);
    var textBox = document.createElement('input');
    textBox.type = 'text';
    textBox.style.position = 'absolute';
    textBox.style.bottom = '2px';
    textBox.style.width = '97%';
    chatArea.appendChild(textBox);
    var drawer = document.createElement('div');
    drawer.style.color = 'white';
    drawer.style.background = '#333';
    drawer.style.cursor = 'pointer';
    drawer.textContent = 'Chat';
    widget.appendChild(drawer);
    $(drawer).click(function () {
        $(chatArea).toggle();
    });

    $(textBox).keyup(function (event) {
        if (event.keyCode == 13 && textBox.value.trim()) {
            var msg = textBox.value.trim();
            addChat('Me', msg);
            $.post(ROOT + 'notifications/chat', {'msg': msg});
            textBox.value = '';
        }
    });

    function addChat(fromName, message) {
        var msg = document.createElement('div');
        msg.style.padding = '3px';
        msg.style.margin = '3px 2px 25px 2px';
        var name = document.createElement('div');
        name.style.marginRight = '5px';
        name.textContent = fromName;
        msg.appendChild(name);
        var content = document.createElement('div');
        content.style.background = 'lightgrey';
        content.style.padding = '5px';
        content.textContent = message;
        msg.appendChild(content);
        messageArea.appendChild(msg);
        $(messageArea).scrollTop(messageArea.scrollHeight);
    }

    $(window).on('notification', function (e, notification) {
        if (notification.type == -1) {
            var msg = notification.message;
            var name = msg.split(':', 1)[0];
            addChat(name, msg.substring(name.length + 1));
        }
    });

});
