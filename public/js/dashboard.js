
$.getJSON(ROOT + 'payment/pending')
.success(function (pending) {
    $(document).ready(function() {
        if (pending === null) { // Household not set up
            addMessage('You aren\'t a registered member of a household. Click "Household" on the navigation menu to create or join a household.', 'error');
        } else {
            // TODO
        }
    });
})
.fail(function(xhr, textStatus, error) {
});

