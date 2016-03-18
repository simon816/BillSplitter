(function () {
    function updateHistory() {
        $.getJSON(ROOT + 'payment/history.json')
        .success(function (history) {
            $(document).ready(function () {
                handleData(history);
            });
        })
        .fail(function () {
            $('#historyTable').hide();
            addMessage('Failed to fetch history data', 'error');
        });
    }

    function handleData(history) {
        if (history == null || history.length == 0) {
            $('#historyTable').hide();
            if (history == null) {
                messageNoHousehold();
            } else {
                addMessage('No payment history');
            }
            return;
        }
        var tbody = $('#historyTable tbody')[0];
        $(tbody).children().remove();
        $('#historyTable').show();
        for (var i = 0; i < history.length; i++) {
            var payment = history[i];
            var row = tbody.insertRow(-1);
            row.insertCell(-1).textContent = payment.description;
            row.insertCell(-1).textContent = payment.payableTo;
            row.insertCell(-1).textContent = '£' + payment.total;
            row.insertCell(-1).textContent = '£' + payment.quantityPaid;
            row.insertCell(-1).textContent = new Date(payment.date * 1000).toLocaleString();
        }
    }

    updateHistory();

    $(window).on('notifications', function (event, notifications) {
        var update = false;
        for (var i = 0; i < notifications.length; i++) {
            update = update || [5].indexOf(notifications[i].type) != -1;
        }
        if (update) {
            updateHistory();
        }
    });

}) ();
