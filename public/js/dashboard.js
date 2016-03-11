$.getJSON(ROOT + 'payment/pending')
.success(function (pending) {
    $(document).ready(function() {
        if (pending === null) { // Household not set up
            messageNoHousehold();
        } else {
            if (pending.length == 0) {
                $('#pending').hide();
                addMessage('You don\'t have any pending bills!');
            }
            var tbody = $('#pending tbody')[0];
            for (var i = 0; i < pending.length; i++) {
                var bill = pending[i];
                var row = tbody.insertRow(-1);
                row.insertCell(-1).textContent = bill.description;
                row.insertCell(-1).textContent = bill.payableTo;
                row.insertCell(-1).textContent = '£' + bill.total;
                row.insertCell(-1).textContent = (bill.proportion * 100) + '%';
                row.insertCell(-1).textContent = '£' + bill.quantityPaid;
                row.insertCell(-1).textContent = '£' + (bill.total * bill.proportion - bill.quantityPaid);

                var payButton = document.createElement('button');
                payButton.textContent = 'Pay Now';
                payButton.addEventListener('click', function (event) {
                    event.target.disabled = true;
                    $.get(ROOT + 'payment/make/' + bill.id)
                    .success(function () {
                        addMessage('Payment made!', 'conf');
                        tbody.removeChild(row);
                        if (tbody.children.length == 0) {
                            $('#pending').hide();
                            addMessage('You don\'t have any pending bills!');
                        }
                    })
                    .fail(function () {
                        addMessage('Payment failed!', 'error');
                        event.target.disabled = false;
                    });
                });
                row.insertCell(-1).appendChild(payButton);
            }
        }
    });
})
.fail(function(xhr, textStatus, error) {
    addMessage('Failed to load data', 'error');
});

