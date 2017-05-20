$(document).ready(function () {
    validateFormFields();

    $('#classify-incident').click(function () {
        validateFormFields();
    });

    $('#resolve-incident').click(function () {
        validateFormFields();
    });
});

function validateFormFields() {
    $('input').each(function () {
        validateField(this);
    });

    $('select').each(function () {
        validateField(this);
    });
}

function validateField(field) {
    if ($('#' + field.id).hasClass('required')) {
        if (field.type == "email") {
            if (validateEmail(field.value)) {
                $('#' + field.id).removeClass('invalid');
            } else {
                $('#' + field.id).addClass('invalid');
            }
            return;
        }
        if (field.type == "number") {
            if (validateNumber(field.value)) {
                $('#' + field.id).removeClass('invalid');
            } else {
                $('#' + field.id).addClass('invalid');
            }
        }
        if (field.value != "") {
            $('#' + field.id).removeClass('invalid');
        }

        if (field.value == "") {
            $('#' + field.id).addClass('invalid');
        }
    }
}

function validateInputGroup(field) {
    var inputGroupId = $('#' + field.id);
    if (inputGroupId.first().value != "") {
        inputGroupId.removeClass('invalid');
    }

    if (inputGroupId.first().value == "") {
        inputGroupId.addClass('invalid');
    }
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function validateDate(date) {
}

function validateNumber(number) {
    return $.isNumeric(number);
}

function validatePhoneNumber(tel) {
}

