$.getJSON(ROOT + 'bill/history.json')
.success(function (history) {
    $(document).ready(function() {
        var tbody = $('#historyTable tbody')[0];
        for (var i = 0; i < history.length; i++) {
            var bill = history[i];
            var row = tbody.insertRow(-1);
            row.insertCell(-1).textContent = bill.description;
            row.insertCell(-1).textContent = bill.payableTo;
            row.insertCell(-1).textContent = '£' + bill.total;
            row.insertCell(-1).textContent = bill.collectorName;
            row.insertCell(-1).textContent = new Date(bill.date * 1000).toLocaleString();
        }
    });
})
.fail(function (xhr, textStatus, error) {
    if (xhr.responseJSON && xhr.responseJSON.error) {
        if (xhr.responseJSON.error.code === 100) {
            messageNoHousehold();
            return;
        }
    }
    addMessage('Failed to load bill history data', 'error');
});
