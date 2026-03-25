document.observe('dom:loaded', function() {
    var nomField = $('nom');
    var emailField = $('email');
    var telefonField = $('telefon');
    var ciutatField = $('ciutat');
    var dataEntradaField = $('dataEntrada');
    var dataSortidaField = $('dataSortida');
    var personesField = $('persones');
    var previewBox = $('previewReserva');
    var previewContent = $('previewContingut');

    var avui = new Date().toISOString().split('T')[0];
    dataEntradaField.setAttribute('min', avui);
    dataSortidaField.setAttribute('min', avui);

    var regexNom = /^[A-Za-zÀ-ÿ\s]{3,50}$/;
    var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    var regexTelefon = /^(\+34|0034)?[\s]?[6-9][0-9]{8}$/;

    function markError(field, errorId, hasError) {
        var errorNode = $(errorId);
        if (hasError) {
            field.addClassName('has-error');
            errorNode.show();
        } else {
            field.removeClassName('has-error');
            errorNode.hide();
        }
        return !hasError;
    }

    function validateNom() {
        var value = nomField.value.trim();
        return markError(nomField, 'errorNom', !regexNom.test(value));
    }

    function validateEmail() {
        var value = emailField.value.trim();
        return markError(emailField, 'errorEmail', !regexEmail.test(value));
    }

    function validateTelefon() {
        var value = telefonField.value.trim();
        return markError(telefonField, 'errorTelefon', !regexTelefon.test(value));
    }

    function validateCiutat() {
        return markError(ciutatField, 'errorCiutat', ciutatField.value === '');
    }

    function validatePersones() {
        return markError(personesField, 'errorPersones', personesField.value === '');
    }

    function validateTipus() {
        var selected = $$('input[name="tipusHabitacio"]:checked').length > 0;
        if (!selected) {
            $('errorTipusHabitacio').show();
        } else {
            $('errorTipusHabitacio').hide();
        }
        return selected;
    }

    function validateDates() {
        var valid = true;
        var dataEntradaTxt = dataEntradaField.value;
        var dataSortidaTxt = dataSortidaField.value;
        var avuiDate = new Date();
        avuiDate.setHours(0, 0, 0, 0);

        if (dataEntradaTxt) {
            var entradaDate = new Date(dataEntradaTxt);
            if (entradaDate < avuiDate) {
                dataEntradaField.addClassName('has-error');
                $('errorDataEntrada').update('La data no pot ser anterior a avui').show();
                valid = false;
            } else {
                dataEntradaField.removeClassName('has-error');
                $('errorDataEntrada').hide();
            }
        }

        if (!dataEntradaTxt || !dataSortidaTxt) {
            $('errorDataSortida').show();
            return false;
        }

        if (new Date(dataSortidaTxt) <= new Date(dataEntradaTxt)) {
            dataSortidaField.addClassName('has-error');
            $('errorDataSortida').show();
            valid = false;
        } else {
            dataSortidaField.removeClassName('has-error');
            $('errorDataSortida').hide();
        }

        return valid;
    }

    nomField.observe('blur', validateNom);
    emailField.observe('blur', validateEmail);
    telefonField.observe('blur', validateTelefon);
    ciutatField.observe('change', validateCiutat);
    personesField.observe('change', validatePersones);
    dataEntradaField.observe('change', validateDates);
    dataSortidaField.observe('change', validateDates);

    $$('input[name="tipusHabitacio"]').each(function(node) {
        node.observe('change', function() {
            $('errorTipusHabitacio').hide();
        });
    });

    $('btnPreview').observe('click', function() {
        var valid = validateNom() && validateEmail() && validateTelefon() && validateCiutat() && validatePersones() && validateTipus() && validateDates();
        if (!valid) {
            previewBox.hide();
            alert('No es pot previsualitzar fins corregir els errors del formulari.');
            return;
        }

        var selectedCityText = ciutatField.options[ciutatField.selectedIndex].text;
        var selectedTipus = $$('input[name="tipusHabitacio"]:checked')[0].value;

        var previewHtml = '';
        previewHtml += '<div><strong>Nom:</strong> ' + nomField.value.trim() + '</div>';
        previewHtml += '<div><strong>Email:</strong> ' + emailField.value.trim() + '</div>';
        previewHtml += '<div><strong>Telefon:</strong> ' + telefonField.value.trim() + '</div>';
        previewHtml += '<div><strong>Ciutat:</strong> ' + selectedCityText + '</div>';
        previewHtml += '<div><strong>Entrada:</strong> ' + dataEntradaField.value + '</div>';
        previewHtml += '<div><strong>Sortida:</strong> ' + dataSortidaField.value + '</div>';
        previewHtml += '<div><strong>Persones:</strong> ' + personesField.value + '</div>';
        previewHtml += '<div><strong>Habitacio:</strong> ' + selectedTipus + '</div>';

        previewContent.update(previewHtml);
        previewBox.show();
    });

    $('btnNetejar').observe('click', function(event) {
        if (!confirm('Vols netejar totes les dades del formulari?')) {
            event.stop();
            return;
        }

        window.setTimeout(function() {
            $$('.error').each(function(node) {
                node.hide();
            });
            $$('.has-error').each(function(node) {
                node.removeClassName('has-error');
            });
            previewContent.update('');
            previewBox.hide();
        }, 0);
    });

    $('formReserva').observe('submit', function(event) {
        var valid = validateNom() && validateEmail() && validateTelefon() && validateCiutat() && validatePersones() && validateTipus() && validateDates();
        if (!valid) {
            event.stop();
            alert('Si us plau, corregeix els errors del formulari');
        }
    });
});
