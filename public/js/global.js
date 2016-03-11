
function addMessage(message, type) {
    type = type || 'info';
    var messageArea = $('#messageArea');
    var messageDiv = $('<div>', {'class': type + ' message'});
    messageDiv.text(message);
    var dismiss = $('<a>', {
        'href': '#',
        'style': 'font-size: x-small; border-bottom: 1px dotted black; text-decoration: none; margin-left: 10px',
        'on': {
            'click': function (event) {
                event.preventDefault();
                messageDiv.remove();
            }
        }
    });
    dismiss.text('Dismiss');
    messageDiv.append(dismiss);
    messageArea.append(messageDiv);
}

// Since it's used in several pages
function messageNoHousehold() {
    addMessage('You aren\'t a registered member of a household. Click "Household" on the navigation menu to create or join a household.', 'error');
}
