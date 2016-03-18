(function () {
    function updateHistory() {
        $.getJSON(ROOT + 'bill/history.json')
        .success(function (history) {
            $(document).ready(function() {
                handleData(history);
            });
        })
        .fail(function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.error) {
                if (xhr.responseJSON.error.code === 100) {
                    $('#historyTable').hide();
                    messageNoHousehold();
                    return;
                }
            }
            addMessage('Failed to load bill history data', 'error');
        });
    }

    function handleData(history) {
        var tbody = $('#historyTable tbody')[0];
        $(tbody).children().remove();
        $('#historyTable').show();

        if (history.length == 0) {
            addMessage("No history");
            $('#historyTable').hide();
        }

        for (var i = 0; i < history.length; i++) {
            var bill = history[i];
            var row = tbody.insertRow(-1);
            row.insertCell(-1).textContent = bill.description;
            row.insertCell(-1).textContent = bill.payableTo;
            row.insertCell(-1).textContent = '£' + bill.total;
            row.insertCell(-1).textContent = bill.collectorName;
            row.insertCell(-1).textContent = new Date(bill.date * 1000).toLocaleString();
        }
    }

    updateHistory();

    $(window).on('notifications', function (event, notifications) {
        var update = false;
        for (var i = 0; i < notifications.length; i++) {
            update = update || [7].indexOf(notifications[i].type) != -1;
        }
        if (update) {
            updateHistory();
        }
    });

}) ();
