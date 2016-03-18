function fetchDetails() {
    $.getJSON(ROOT + 'household/details')
    .success(function (details) {
        $(document).ready(function() {
            populateDetails(details);
        });
    })
    .fail(function () {
       addMessage("Failed to load data", 'error');
    });
}

function populateDetails(details) {
    if (details === null) { // No household
        $('#noHousehold').show();
        $('#household').hide();
        return;
    }
    $('#noHousehold').hide();
    $('#household').show();
    $('#hhName').text(details.name);
    var mTableBody = $('#membersTable tbody');
    for(var i = 0; i < details.members.length; i++) {
        var detailRow = details.members[i];
        var row = mTableBody[0].insertRow(-1);
        row.insertCell(-1).textContent = detailRow.name;
        row.insertCell(-1).textContent = detailRow.paymentsMade;
        row.insertCell(-1).textContent = detailRow.paymentsDue;

        if (detailRow.isOwner) {
            row.style.background = 'gold';
        }
    }
    $('#paymentInfo').html('Bills Paid: ' + details.billsPaid + '<br> Bills Due: ' + details.billsDue);

    $('#leaveButton').click(function (event) {
        if (!confirm("Are you sure you want to leave this household?")) {
            return;
        }
        $.get(ROOT + 'household/leave')
        .success(function () {
            $('#noHousehold').show();
            $('#household').hide();
        })
        .fail(function (xhr) {
            var message = '';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                message = xhr.responseJSON.error.message;
            }
            addMessage('Failed to leave household. ' + message, 'error');
        });
    });
}

$(document).ready(function() {

    var joinPopup = $('#joinPopup').popup({
        'open': function () {
            this.email.value = '';
            this.email.focus();
        }
    });
    joinPopup.element.submit(function (event) {
        event.preventDefault();
        var email = event.target.email.value;
        if (!/.+@.+/.test(email)) {
            alert("Invalid email address");
        }
        $.post(event.target.action, {'email': email})
        .success(function (data) {
            joinPopup.hide();
            addMessage("Request to join household sent", 'conf');
            fetchDetails();
        })
        .fail(function (xhr, textStatus, error) {
            if (xhr.responseJSON && xhr.responseJSON.error) {
                alert(xhr.responseJSON.error.message);
            } else {
                alert(error);
            }
        });
    });
    $('#joinButton').click(function() {
        joinPopup.show();
    });

    var createPopup = $('#createPopup').popup({
        'open': function () {
            this.name.value = '';
            this.name.focus();
        }
    });
    createPopup.element.submit(function (event) {
        event.preventDefault();
        var form = event.target;
        var name = form.name.value;
        if (!name) {
            alert("Need a name");
            return;
        }
        $.post(form.action, {'name': name})
        .success(function (data) {
            createPopup.hide();
            fetchDetails();
        })
        .fail(function () {
            alert("There was an error");
        });
    });
    $('#createButton').click(function() {
        createPopup.show();
    });
});

fetchDetails();
