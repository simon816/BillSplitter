$(document).ready(function () {

    function validateName(name, errorElem) {
        if (typeof name !== 'string' || name.length < 2) {
            errorElem.text('Name must be at least 2 characters');
            return false;
        }
        errorElem.text('');
        return true;
    }

    function validateEmail(email, errorElem) {
        // Not a complete validation (PHP and the browser will do that) but just an indication.
        if (typeof email !== 'string' || !/.+@.+/.test(email)) {
            errorElem.text('Not a valid email');
            return false;
        }
        errorElem.text('');
        return true;
    }

    function validatePassword(password, errorElem) {
        if (typeof password !== 'string' || password.length < 6) {
            errorElem.text('Password must be at least 6 characters');
            return false;
        }
        errorElem.text('');
        return true;
    }

    $('#loginform').on('submit', function (event) {
        var valid = true;
        valid = validateEmail(event.target.email.value, $('#emailError')) && valid;
        valid = validatePassword(event.target.password.value, $('#passwordError')) && valid;
        if (!valid) {
            event.preventDefault();
            return;
        }
        // Let the form process as default
    });

    $('#regform').on('submit', function (event) {
        var valid = true;
        valid = validateName(event.target.name.value, $('#nameError')) && valid;
        valid = validateEmail(event.target.email.value, $('#emailError')) && valid;
        valid = validatePassword(event.target.password.value, $('#passwordError')) && valid;
        if (!valid) {
            event.preventDefault();
            return;
        }
        // Let the form process as default
    });

});
