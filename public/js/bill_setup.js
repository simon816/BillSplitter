$.getJSON(ROOT + 'household/members')
.success(function (members) {
    $(document).ready(function() {
        var form = $('#billForm');
        if (members === null) {
            messageNoHousehold();
            form.hide();
            return;
        }
        var split = $('#splitTable tbody')[0];

        var propTracker = {};

        for (var i = 0; i < members.length; i++) {
            var member = members[i];
            var row = split.insertRow(-1);

            var enableInput = $('<input>', {type: 'checkbox', name: 'splitwith[]', 'checked': true, value: member.id});
            $(row.insertCell(-1)).append(enableInput);

            row.insertCell(-1).textContent = member.name;

            var percentInput = $('<input>', {type: 'number', name: 'proportions[]', min: 1, max: 100});
            $(row.insertCell(-1)).append(percentInput);

            var owed = row.insertCell(-1);
            propTracker[member.id] = {
                'percent': percentInput,
                'owed': owed
            };

            percentInput.on('keyup change', function (event) {
                updateValues();
            });

            (function (percentInput, owed, userId) {
                enableInput.change(function (event) {
                    if (event.target.checked) {
                        percentInput[0].disabled = false;
                        propTracker[userId] = {
                            'percent': percentInput,
                            'owed': owed
                        };
                    } else {
                        percentInput[0].disabled = true;
                        percentInput.val('');
                        owed.textContent = '';
                        delete propTracker[userId];
                    }
                    updateProportions();
                });
            })(percentInput, owed, member.id);
        }

        var updateProportions = function () {
            var length = Object.keys(propTracker).length;
            if (length == 0) {
                form.find('input[type="submit"]')[0].disabled = true;
                return;
            }
            form.find('input[type="submit"]')[0].disabled = false;
            var prop = parseInt(100 / length);
            var runningTotal = 0;
            var last = null;
            for (var id in propTracker) {
                runningTotal += prop;
                (last = propTracker[id]).percent.val(prop);
            }
            if (runningTotal != 100) {
                last.percent.val(prop + (100 - runningTotal));
                runningTotal = 100;
            }
            updateValues();
        }

        var checkPercentage = function () {
            var runningTotal = 0;
            for (var id in propTracker) {
                runningTotal += parseInt(propTracker[id].percent.val()) || 0;
            }
            if (runningTotal != 100) {
                $('#splitError').text('Percentages don\'t add up to 100, it is currently ' + runningTotal);
            } else {
                $('#splitError').text('');
            }
        }
        var updateValues = function () {
            var total = parseFloat(form[0].total.value) || 0;
            for (var id in propTracker) {
                var prop = propTracker[id].percent.val();
                propTracker[id].owed.textContent = '£' + (total * (prop / 100));
            }
            checkPercentage();
        }

        updateProportions();
        $(form[0].total).on('keyup change', function (event) {
            updateValues();
        });

        form.submit(function (event) {
            event.preventDefault();
            if ($('#splitError').text()) {
                return;
            }
            var fElem = form[0];
            if (!fElem.desc.value) {
                alert("Description can't be empty");
                return;
            }
            if (!fElem.total.value || parseFloat(fElem.total.value) < 0.01) {
                alert("Total can't be less than 0.01");
                return;
            }
            if (!fElem.payTo.value) {
                alert("Payable to can't be empty");
                return;
            }
            if (Object.keys(propTracker).length == 0) {
                alert("Must select at least one person to split with");
                return;
            }
            $.post(fElem.action, form.serializeArray())
            .success(function () {
                addMessage("Successfully created the bill", 'conf');
                form.find('input[type="text"], input[type="number"]').val('');
                form.find('input[type="checkbox"]').prop('checked', 'true').trigger('change');
                updateProportions();
            })
            .fail(function () {
                addMessage('Failed to create bill', 'error');
            });
        });
    });
})
.fail(function () {
    addMessage("Failed to list members", 'error');
});
