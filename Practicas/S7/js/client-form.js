document.addEventListener('DOMContentLoaded', function () {
    var nomField = document.getElementById('nom');
    var emailField = document.getElementById('email');
    var telefonField = document.getElementById('telefon');
    var ciutatField = document.getElementById('ciutat');
    var dataEntradaField = document.getElementById('dataEntrada');
    var dataSortidaField = document.getElementById('dataSortida');
    var personesField = document.getElementById('persones');
    var ciutatSearchField = document.getElementById('ciutatSearch');
    var hotelSearchField = document.getElementById('hotelSearch');
    var previewBox = document.getElementById('previewReserva');
    var previewContent = document.getElementById('previewContingut');
    var ajaxResult = document.getElementById('ajaxResult');
    var form = document.getElementById('formReserva');
    var btnPreview = document.getElementById('btnPreview');
    var btnNetejar = document.getElementById('btnNetejar');

    if (!form || !btnPreview || !btnNetejar) {
        return;
    }

    var avui = new Date().toISOString().split('T')[0];
    if (dataEntradaField) {
        dataEntradaField.setAttribute('min', avui);
    }
    if (dataSortidaField) {
        dataSortidaField.setAttribute('min', avui);
    }

    var regexNom = /^[A-Za-zÀ-ÿ\s]{3,50}$/;
    var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    var regexTelefon = /^(\+34|0034)?[\s]?[6-9][0-9]{8}$/;

    function show(node) {
        if (node) {
            node.style.display = 'block';
        }
    }

    function hide(node) {
        if (node) {
            node.style.display = 'none';
        }
    }

    function revealPreview() {
        show(previewBox);
    }

    function hidePreview() {
        hide(previewBox);
    }

    function emphasizeField(field) {
        if (!field) {
            return;
        }
        field.style.transition = 'background-color 0.6s ease';
        field.style.backgroundColor = '#f2dede';
        window.setTimeout(function () {
            field.style.backgroundColor = '#ffffff';
        }, 120);
    }

    function markError(field, errorId, hasError) {
        var errorNode = document.getElementById(errorId);
        if (!field || !errorNode) {
            return !hasError;
        }
        if (hasError) {
            field.classList.add('has-error');
            show(errorNode);
            emphasizeField(field);
        } else {
            field.classList.remove('has-error');
            hide(errorNode);
        }
        return !hasError;
    }

    function validateNom() {
        var value = nomField ? nomField.value.trim() : '';
        return markError(nomField, 'errorNom', !regexNom.test(value));
    }

    function validateEmail() {
        var value = emailField ? emailField.value.trim() : '';
        return markError(emailField, 'errorEmail', !regexEmail.test(value));
    }

    function validateTelefon() {
        var value = telefonField ? telefonField.value.trim() : '';
        return markError(telefonField, 'errorTelefon', !regexTelefon.test(value));
    }

    function validateCiutat() {
        return markError(ciutatField, 'errorCiutat', !ciutatField || ciutatField.value === '');
    }

    function validatePersones() {
        return markError(personesField, 'errorPersones', !personesField || personesField.value === '');
    }

    function validateTipus() {
        var selected = document.querySelectorAll('input[name="tipusHabitacio"]:checked').length > 0;
        var errorTipus = document.getElementById('errorTipusHabitacio');
        if (!selected) {
            show(errorTipus);
        } else {
            hide(errorTipus);
        }
        return selected;
    }

    function validateDates() {
        var valid = true;
        var dataEntradaTxt = dataEntradaField ? dataEntradaField.value : '';
        var dataSortidaTxt = dataSortidaField ? dataSortidaField.value : '';
        var avuiDate = new Date();
        var errorDataEntrada = document.getElementById('errorDataEntrada');
        var errorDataSortida = document.getElementById('errorDataSortida');

        avuiDate.setHours(0, 0, 0, 0);

        if (dataEntradaTxt) {
            var entradaDate = new Date(dataEntradaTxt);
            if (entradaDate < avuiDate) {
                if (dataEntradaField) {
                    dataEntradaField.classList.add('has-error');
                }
                if (errorDataEntrada) {
                    errorDataEntrada.textContent = 'La data no pot ser anterior a avui';
                }
                show(errorDataEntrada);
                valid = false;
            } else {
                if (dataEntradaField) {
                    dataEntradaField.classList.remove('has-error');
                }
                hide(errorDataEntrada);
            }
        }

        if (!dataEntradaTxt || !dataSortidaTxt) {
            show(errorDataSortida);
            return false;
        }

        if (new Date(dataSortidaTxt) <= new Date(dataEntradaTxt)) {
            if (dataSortidaField) {
                dataSortidaField.classList.add('has-error');
            }
            show(errorDataSortida);
            valid = false;
        } else {
            if (dataSortidaField) {
                dataSortidaField.classList.remove('has-error');
            }
            hide(errorDataSortida);
        }

        return valid;
    }

    function validateAll() {
        var isNomValid = validateNom();
        var isEmailValid = validateEmail();
        var isTelefonValid = validateTelefon();
        var isCiutatValid = validateCiutat();
        var isPersonesValid = validatePersones();
        var isTipusValid = validateTipus();
        var isDatesValid = validateDates();

        return isNomValid && isEmailValid && isTelefonValid && isCiutatValid && isPersonesValid && isTipusValid && isDatesValid;
    }

    if (nomField) {
        nomField.addEventListener('blur', validateNom);
    }
    if (emailField) {
        emailField.addEventListener('blur', validateEmail);
    }
    if (telefonField) {
        telefonField.addEventListener('blur', validateTelefon);
    }
    if (ciutatField) {
        ciutatField.addEventListener('change', function () {
            validateCiutat();
            if (ciutatSearchField && ciutatField.selectedIndex > 0) {
                var text = ciutatField.options[ciutatField.selectedIndex].text;
                ciutatSearchField.value = text.replace(/\s\([A-Z]{2}\)$/, '');
            }
        });
    }
    if (personesField) {
        personesField.addEventListener('change', validatePersones);
    }
    if (dataEntradaField) {
        dataEntradaField.addEventListener('change', validateDates);
    }
    if (dataSortidaField) {
        dataSortidaField.addEventListener('change', validateDates);
    }

    Array.prototype.forEach.call(document.querySelectorAll('input[name="tipusHabitacio"]'), function (node) {
        node.addEventListener('change', function () {
            hide(document.getElementById('errorTipusHabitacio'));
        });
    });

    btnPreview.addEventListener('click', function () {
        var valid = validateAll();
        if (!valid) {
            if (previewContent) {
                previewContent.innerHTML = '<div><strong>No es pot previsualitzar:</strong> corregeix els camps marcats en vermell i torna-ho a provar.</div>';
            }
            revealPreview();
            return;
        }

        var selectedCityText = '';
        if (ciutatField && ciutatField.selectedIndex >= 0) {
            selectedCityText = ciutatField.options[ciutatField.selectedIndex].text;
        }

        var selectedTipusNode = document.querySelector('input[name="tipusHabitacio"]:checked');
        var selectedTipus = selectedTipusNode ? selectedTipusNode.value : '';

        var previewHtml = '';
        previewHtml += '<div><strong>Nom:</strong> ' + (nomField ? nomField.value.trim() : '') + '</div>';
        previewHtml += '<div><strong>Email:</strong> ' + (emailField ? emailField.value.trim() : '') + '</div>';
        previewHtml += '<div><strong>Telefon:</strong> ' + (telefonField ? telefonField.value.trim() : '') + '</div>';
        previewHtml += '<div><strong>Ciutat:</strong> ' + selectedCityText + '</div>';
        previewHtml += '<div><strong>Hotel:</strong> ' + (hotelSearchField ? (hotelSearchField.value.trim() || 'Sense hotel especificat') : 'Sense hotel especificat') + '</div>';
        previewHtml += '<div><strong>Entrada:</strong> ' + (dataEntradaField ? dataEntradaField.value : '') + '</div>';
        previewHtml += '<div><strong>Sortida:</strong> ' + (dataSortidaField ? dataSortidaField.value : '') + '</div>';
        previewHtml += '<div><strong>Persones:</strong> ' + (personesField ? personesField.value : '') + '</div>';
        previewHtml += '<div><strong>Habitacio:</strong> ' + selectedTipus + '</div>';

        // Recollim les peticions per mostrar-les a la previsualització
        var peticionsTextList = [];
        $('#llistaPeticions').find('li').each(function() {
            var $textNode = $(this).contents().filter(function() {
                return this.nodeType === 3; 
            });
            var text = $textNode.text().trim();
            if (text) {
                peticionsTextList.push(text);
            }
        });
        if (peticionsTextList.length > 0) {
            previewHtml += '<div><strong>Peticions Especials:</strong> ' + peticionsTextList.join(', ') + '</div>';
        }

        if (previewContent) {
            previewContent.innerHTML = previewHtml;
        }
        revealPreview();
    });

    btnNetejar.addEventListener('click', function (event) {
        event.preventDefault(); // Atentem l'esdeveniment i usem el DIALOG de jQuery UI

        // 1. JQUERY UI: WIDGET DIALOG
        $('<div title="Confirmació">Vols netejar totes les dades del formulari?</div>').dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Sí, Netejar": function() {
                    $(this).dialog("close");
                    
                    form.reset(); // Forcem el reset natiu
                    Array.prototype.forEach.call(document.querySelectorAll('.error'), function (node) {
                        hide(node);
                    });
                    Array.prototype.forEach.call(document.querySelectorAll('.has-error'), function (node) {
                        node.classList.remove('has-error');
                    });
                    if (previewContent) {
                        previewContent.innerHTML = '';
                    }
                    hidePreview();
                    if (ajaxResult) {
                        hide(ajaxResult);
                    }
                },
                "Cancel·lar": function() {
                    $(this).dialog("close");
                }
            }
        });
    });

    form.addEventListener('submit', function (event) {
        if (!validateAll()) {
            event.preventDefault();
            // 2. JQUERY UI: EFECTE VISUAL (Shake)
            $('#formReserva').effect('shake', { distance: 10, times: 3 }, 500);
            return;
        }

        // Evitem el submit normal per delegar a l'AJAX de jQuery
        event.preventDefault();
        
        if (ajaxResult) {
            hide(ajaxResult);
        }
        
        // Cridem la funció d'AJAX que definirem amb jQuery
        if (typeof window.submitReservaAjax === 'function') {
            window.submitReservaAjax();
        }
    });
});

// Implementació dels requisits de jQuery: Interaccions DOM, seleccions, filtres, agregar/eliminar elements
(function($) {
    if (typeof $ === 'undefined') {
        return; // No jQuery loaded
    }
    
    $(document).ready(function() {
        // 3. JQUERY UI: INTERACCIÓ (SORTABLE)
        // Permet arrossegar i reordenar les peticions de la llista
        $('#llistaPeticions').sortable({
            placeholder: "ui-state-highlight"
        }).disableSelection();

        // Afegir elements al DOM
        $('#btnAfegirPeticio').on('click', function() {
            var textPeticio = $('#novaPeticio').val().trim();
            if (textPeticio !== '') {
                // Creació d'un nou element li per al DOM
                var nouItem = $('<li class="list-group-item peticio-item"></li>');
                nouItem.text(textPeticio + ' ');
                
                // Botó per eliminar l'element (Afegir elements secundaris)
                var btnEliminar = $('<button class="btn btn-danger btn-xs pull-right">Eliminar</button>');
                nouItem.append(btnEliminar);
                
                // Afegim l'element al final de la llista (DOM insertion)
                $('#llistaPeticions').append(nouItem);
                
                // Netejar el camp de text
                $('#novaPeticio').val('');
            }
        });

        // Eliminar elements del DOM (Esdeveniment delegat)
        $('#llistaPeticions').on('click', '.btn-danger', function() {
            // Elimina l'element 'li' (eliminació de nodes del DOM)
            $(this).closest('li').remove();
        });

        // Navegar els elements amb seleccions (individuals i múltiples) i filtres
        $('#btnDestacarPeticions').on('click', function() {
            // Netejem estils anteriors per tornar a començar
            $('.peticio-item').css({ 'background-color': '', 'font-weight': 'normal', 'color': '' });
            
            // Selecció múltiple i filtre: Totes les peticions a la llista
            var $peticions = $('#llistaPeticions').find('li');
            
            // Filtre per destacar elements senars (odd) via selecció múltiple en jQuery (.filter(':odd'))
            $peticions.filter(':odd').css('background-color', '#d9edf7'); // Blau clar
            
            // Navegació individual de elements
            $peticions.each(function(index, element) {
                // Trobem el text de l'element (ignorant els fills com el botó) per poder avaluar
                var $textNode = $(element).contents().filter(function() {
                    return this.nodeType === 3; 
                });
                
                var text = $textNode.text().toLowerCase();
                if (text.indexOf('urgent') !== -1) {
                    // Si la peticio conte la paraula "urgent", seleccionem i destaquem l'element específicament
                    $(element).css({
                        'background-color': '#f2dede',
                        'font-weight': 'bold',
                        'color': '#a94442'
                    });
                }
            });
        });

        // 1. AUTO-COMPLETAR AMB AJAX I JQUERY UI (Autocomplete)
        $('#ciutatSearch').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: 'server.php',
                    data: { ajax: 1, action: 'autocomplete', type: 'city', q: request.term },
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.ok && res.items && res.items.length > 0) {
                            var items = $.map(res.items, function(item) {
                                return { label: item.label, value: item.name, id: item.id };
                            });
                            response(items);
                        } else {
                            response([]);
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#ciutat').val(ui.item.id);
                // Trigereja el canvi de ciutat (per netejar errors visuals)
                $('#ciutat').trigger('change');
            },
            minLength: 1
        });

        $('#hotelSearch').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: 'server.php',
                    data: { ajax: 1, action: 'autocomplete', type: 'hotel', q: request.term },
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.ok && res.items && res.items.length > 0) {
                            var items = $.map(res.items, function(item) {
                                return { label: item.label, value: item.name };
                            });
                            response(items);
                        } else {
                            response([]);
                        }
                    }
                });
            },
            minLength: 1
        });

        // 2. IMPRESSIÓ DE DADES A UNA TAULA VÍA AJAX (jQuery)
        window.submitReservaAjax = function() {
            // Omplim el camp ocult amb les peticions afegides al panell de jQuery UI
            var peticionsTextList = [];
            $('#llistaPeticions').find('li').each(function() {
                var $textNode = $(this).contents().filter(function() {
                    return this.nodeType === 3; 
                });
                var text = $textNode.text().trim();
                if (text) {
                    peticionsTextList.push(text);
                }
            });
            $('#comentarisHidden').val(peticionsTextList.join('\n'));

            // Recopilem les dades per fer la petició POST
            var postData = $('#formReserva').serialize() + '&ajax=1&action=reserve';

            $.ajax({
                url: 'server.php',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(res) {
                    var $resBox = $('#ajaxResult');
                    $resBox.empty();

                    if (res && res.success) {
                        var r = res.reservation;
                        // Imprimir dades a una taula
                        var taulaHtml = '<div class="panel panel-success">' +
                            '<div class="panel-heading"><h4><span class="glyphicon glyphicon-ok"></span> Reserva Confirmada (AJAX)</h4></div>' +
                            '<table class="table table-bordered table-striped">' +
                            '<tbody>' +
                            '<tr><th width="30%">Codi de Reserva</th><td><strong>' + r.code + '</strong></td></tr>' +
                            '<tr><th>Nom Complet</th><td>' + r.nom + '</td></tr>' +
                            '<tr><th>Correu Electrònic</th><td>' + r.email + '</td></tr>' +
                            '<tr><th>Telèfon</th><td>' + r.telefon + '</td></tr>' +
                            '<tr><th>Ciutat de Destí</th><td>' + r.ciutat + ' (' + r.countryCode + ')</td></tr>' +
                            '<tr><th>Hotel Triat</th><td>' + r.hotelNom + '</td></tr>' +
                            '<tr><th>Dates</th><td>Del <strong>' + r.dataEntrada + '</strong> al <strong>' + r.dataSortida + '</strong></td></tr>' +
                            '<tr><th>Persones</th><td>' + r.persones + '</td></tr>' +
                            '<tr><th>Tipus d\'Habitació</th><td>' + r.tipusHabitacio + '</td></tr>' +
                            '<tr class="success"><th>Preu Total</th><td><strong>' + r.preuTotal + ' €</strong></td></tr>' +
                            '</tbody></table></div>';
                        
                        $resBox.html(taulaHtml).fadeIn();

                        $('html, body').animate({
                            scrollTop: $resBox.offset().top
                        }, 500);
                    } else {
                        var errorHtml = '<div class="alert alert-danger"><h4>Errors en la reserva:</h4><ul>';
                        $.each(res.errors || ['Error desconegut al servidor'], function(idx, err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                        errorHtml += '</ul></div>';
                        $resBox.html(errorHtml).fadeIn();
                    }
                },
                error: function() {
                    $('#ajaxResult').html('<div class="alert alert-danger">Error de connexió al servidor (AJAX).</div>').show();
                }
            });
        };

    });
})(window.jQuery);
