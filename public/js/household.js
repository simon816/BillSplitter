$.getJSON(ROOT + 'household/details')
.success(function (details) {
    $(document).ready(function() {
        if (details === null) { // No household
            $('#noHousehold').show();
            $('#household').hide();
        } else {
            $('#hhName').text(details.name);
            var mTableBody = $('#membersTable tbody');
            for(var i = 0; i < details.members.length; i++) {
                var detailRow = details.members[i];
                var row = mTableBody[0].insertRow(-1);
                row.insertCell(-1).textContent = detailRow.name;
                row.insertCell(-1).textContent = detailRow.payments_made;
                row.insertCell(-1).textContent = detailRow.payments_due;
                row.insertCell(-1).textContent = detailRow.default_proportion;
            }
            $('#paymentInfo').html('Bills Paid: ' + details.billsPaid + '<br> Bills Due: ' + details.billsDue);
        }
    });
})
.fail(function (xhr, textError, error) {
   addMessage("Failed to load data", 'error');
});

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
            console.log(data);
        })
        .fail(function () {
            alert("There was an error");
        });
    });
    $('#createButton').click(function() {
        createPopup.show();
    });
});
