$.getJSON(ROOT + 'bill/list')
.success(function (bills) {
    $(document).ready(function() {
        var tableBody = $('#billTable tbody');
        for (var i = 0; i < bills.length; i++) {
            var bill = bills[i];
            var row = tableBody[0].insertRow(-1);
            var blankCell = row.insertCell(-1);
            blankCell.textContent = i + 1;
            blankCell.setAttribute('rowspan', 2);
            blankCell.style.background = i % 2 == 0 ? 'lightblue' : '#DFDFFF';
            row.insertCell(-1).textContent = bill.description;
            row.insertCell(-1).textContent = bill.payableTo;
            row.insertCell(-1).textContent = '£' + bill.total;
            var row2 = tableBody[0].insertRow(-1);
            var payeesCell = row2.insertCell(-1);
            payeesCell.setAttribute('colspan', 3);
            var payeesTable = document.createElement('table');
            payeesCell.appendChild(payeesTable);
            var pHead = document.createElement('thead');
            payeesTable.appendChild(pHead);
            var headerRow = pHead.insertRow(-1);
            headerRow.insertCell(-1).textContent = 'Name';
            headerRow.insertCell(-1).textContent = 'Proportion';
            headerRow.insertCell(-1).textContent = 'Quantity Paid';
            var pBody = document.createElement('tbody');
            payeesTable.appendChild(pBody);
            payeesTable.style.width = '100%';
            for (var j = 0; j < bill.payees.length; j++) {
                var payee = bill.payees[j];
                var row = pBody.insertRow(-1);
                row.style.background = 'lightblue';
                row.insertCell(-1).textContent = payee.name;
                row.insertCell(-1).textContent = (payee.proportion * 100) + '%';
                row.insertCell(-1).textContent = '£' + payee.quantityPaid;
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
