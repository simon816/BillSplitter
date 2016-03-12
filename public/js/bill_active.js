$.getJSON(ROOT + 'bill/list')
.success(function (bills) {
    $(document).ready(function() {
        var tableBody = $('#billTable tbody');
        for (var i = 0; i < bills.length; i++) {
            var bill = bills[i];
            var row = tableBody[0].insertRow(-1);
            var allPaid = true;

            var blankCell = row.insertCell(-1);
            blankCell.textContent = i + 1;
            blankCell.setAttribute('rowspan', 2);
            blankCell.style.background = i % 2 == 0 ? 'lightblue' : '#DFDFFF';

            row.insertCell(-1).textContent = bill.description;
            row.insertCell(-1).textContent = bill.payableTo;
            row.insertCell(-1).textContent = '£' + bill.total;
            var row2 = tableBody[0].insertRow(-1);
            var payeesCell = row2.insertCell(-1);
            payeesCell.setAttribute('colspan', 4);
            var payeesTable = document.createElement('table');
            payeesCell.appendChild(payeesTable);
            var pHead = document.createElement('thead');
            payeesTable.appendChild(pHead);
            var headerRow = pHead.insertRow(-1);
            headerRow.insertCell(-1).textContent = 'Name';
            headerRow.insertCell(-1).textContent = 'Money Owed';
            headerRow.insertCell(-1).textContent = 'Money Paid';
            var pBody = document.createElement('tbody');
            payeesTable.appendChild(pBody);
            payeesTable.style.width = '100%';
            for (var j = 0; j < bill.payees.length; j++) {
                var payee = bill.payees[j];
                var userRow = pBody.insertRow(-1);
                userRow.style.background = 'lightblue';
                userRow.insertCell(-1).textContent = payee.name;
                userRow.insertCell(-1).textContent = '£' + payee.quantityOwed;
                userRow.insertCell(-1).textContent = '£' + payee.quantityPaid;
                allPaid &= payee.quantityOwed == payee.quantityPaid;
            }
            var collectorCell = row.insertCell(-1);
            $(collectorCell).append($('<div>', {text: bill.collector.name}));
            if (bill.collector.isCurrentUser && allPaid) {
                var confButton = $('<button>Confirm Payment</button>');
                (function (row1, row2, bill) {
                    confButton.click(function (event) {
                        $.get(ROOT + 'bill/confirm/' + bill.id)
                        .success(function () {
                            $(row1).remove();
                            $(row2).remove();
                            addMessage('Payment confirmed', 'conf');
                        })
                        .fail(function () {
                            addMessage('Failed to confirm payment', 'error');
                        });
                    });
                }) (row, row2, bill);
                $(collectorCell).append(confButton);
            }
        }
    });
})
.fail(function (xhr, textStatus, error) {
    $('#billTable').hide();
    if (xhr.responseJSON && xhr.responseJSON.error) {
        if (xhr.responseJSON.error.code === 100) {
            messageNoHousehold();
            return;
        }
    }
    addMessage('Failed to load bill data', 'error');
});
