$.getJSON(ROOT + 'payment/history.json')
.success(function (history) {
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
    for (var i = 0; i < history.length; i++) {
        var payment = history[i];
        var row = tbody.insertRow(-1);
        row.insertCell(-1).textContent = payment.description;
        row.insertCell(-1).textContent = payment.payableTo;
        row.insertCell(-1).textContent = '£' + payment.total;
        row.insertCell(-1).textContent = '£' + payment.quantityPaid;
    }
})
.fail(function () {
    $('#historyTable').hide();
    addMessage('Failed to fetch history data', 'error');
});
