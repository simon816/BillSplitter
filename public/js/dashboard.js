(function () {
    function updatePending() {
        $.getJSON(ROOT + 'payment/pending')
        .success(function (pending) {
            $(document).ready(function () {
                handleData(pending);
            });
        })
        .fail(function() {
            addMessage('Failed to load data', 'error');
        });
    }

    var noPendingShowing = false;

    function messageNoBills() {
        $('#pending').hide();
        if (!noPendingShowing) {
            addMessage('You don\'t have any pending bills!', 'info', function () {
                noPendingShowing = false;
                return true;
            });
        }
        noPendingShowing = true;
    }

    function handleData(pending) {
        if (pending === null) { // Household not set up
            $('#pending, #confTable').hide();
            messageNoHousehold();
            return;
        }
        $('#pending tbody, #confTable tbody').children().remove();
        $('#pending, #confTable').show();
        if (pending.yours.length == 0) {
            messageNoBills();
        }
        if (pending.others.length == 0) {
            $('#confTable').hide();
        }
        var tbody = $('#pending tbody')[0];
        for (var i = 0; i < pending.yours.length; i++) {
            var bill = pending.yours[i];
            var row = tbody.insertRow(-1);
            row.insertCell(-1).textContent = bill.description;
            row.insertCell(-1).textContent = bill.payableTo;
            row.insertCell(-1).textContent = '£' + bill.total;
            row.insertCell(-1).textContent = '£' + bill.quantityPaid;
            row.insertCell(-1).textContent = '£' + bill.quantityOwed;
            if (bill.status == 0) {
                makePayNowButton(bill.id, row);
            } else if (bill.status == 1) {
                makeCancelPaymentButton(bill.id, row);
            } else if (bill.status == 2) {
                var cell = makePayNowButton(bill.id, row);
                cell.appendChild(document.createTextNode('Your payment was denied'));
            }
        }

        tbody = $('#confTable tbody')[0];
        for (var i = 0; i < pending.others.length; i++) {
            var payment = pending.others[i];
            var row = tbody.insertRow(-1);
            row.insertCell(-1).textContent = payment.description;
            row.insertCell(-1).textContent = payment.user.name;
            row.insertCell(-1).textContent = '£' + payment.amount;
            var actionCell = row.insertCell(-1);
            makeConfirmPaymentButton(payment.user.id, payment.billId, actionCell, row);
            makeDenyPaymentButton(payment.user.id, payment.billId, actionCell, row);
        }
    }

    function makeConfirmPaymentButton(userId, billId, actionCell, row) {
        var button = document.createElement('button');
        button.textContent = 'Confirm';
        button.style.marginRight = '2px';
        $(button).click(function (event) {
            $.post(ROOT + 'payment/confirm', {userId: userId, billId: billId})
            .success(function () {
                addMessage('Payment confirmed', 'conf');
                row.remove();
            })
            .fail(function () {
                addMessage('Failed to confirm payment', 'error');
            });
        });
        actionCell.appendChild(button);
    }

    function makeDenyPaymentButton(userId, billId, actionCell, row) {
        var button = document.createElement('button');
        button.textContent = 'Deny';
        button.className = 'red';
        $(button).click(function (event) {
            $.post(ROOT + 'payment/deny', {userId: userId, billId: billId})
            .success(function () {
                addMessage('Payment has been marked as denied', 'conf');
                row.remove();
            })
            .fail(function () {
                addMessage('Failed to deny payment', 'error');
            });
        });
        actionCell.appendChild(button);
    }

    function makePayNowButton(billId, row) {
        var cell = row.insertCell(-1);
        var payButton = document.createElement('button');
        payButton.textContent = 'Pay Now';
        payButton.addEventListener('click', function (event) {
            event.target.disabled = true;
            $.get(ROOT + 'payment/make/' + billId)
            .success(function () {
                addMessage('Payment request sent. Waiting for confirmation from bill collector.', 'conf');
                row.removeChild(cell);
                makeCancelPaymentButton(billId, row);
            })
            .fail(function () {
                addMessage('Payment failed!', 'error');
                event.target.disabled = false;
            });
        });
        cell.appendChild(payButton);
        return cell;
    }

    function makeCancelPaymentButton(billId, row) {
        var cell = row.insertCell(-1);
        var cancel = document.createElement('button');
        cancel.textContent = 'Cancel Payment';
        cancel.className = 'red';
        $(cancel).click(function (event) {
            event.target.disabled = true;
            $.get(ROOT + 'payment/cancel/' + billId)
            .success(function () {
                addMessage('Cancelled payment request', 'conf');
                row.removeChild(cell);
                makePayNowButton(billId, row);
            })
            .fail(function () {
                addMessage('Failed to cancel payment', 'error');
                event.target.disabled = false;
            });
        });
        cell.appendChild(cancel);
    }

    updatePending();

    $(window).on('notifications', function (event, notifications) {
        var update = false;
        for (var i = 0; i < notifications.length; i++) {
            update = update || [2, 3, 4, 5, 6].indexOf(notifications[i].type) != -1;
        }
        if (update) {
            updatePending();
        }
    });
}) ();
