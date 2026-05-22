(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
            return;
        }
        fn();
    }

    function requestJson(url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }
            if (xhr.status < 200 || xhr.status >= 300) {
                callback(null);
                return;
            }
            try {
                callback(JSON.parse(xhr.responseText));
            } catch (e) {
                callback(null);
            }
        };
        xhr.send();
    }

    function clearChildren(node) {
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    }

    ready(function () {
        if (window.__cowAutocompleteFallbackReady) {
            return;
        }
        window.__cowAutocompleteFallbackReady = true;

        var hasPrototype = (typeof document.observe === 'function') && (typeof window.$ === 'function');

        var cityInput = document.getElementById('ciutatSearch');
        var hotelInput = document.getElementById('hotelSearch');
        var cityList = document.getElementById('ciutatSuggestions');
        var hotelList = document.getElementById('hotelSuggestions');
        var citySelect = document.getElementById('ciutat');

        if (!cityInput || !hotelInput || !cityList || !hotelList) {
            return;
        }

        var cityTimer = null;
        var hotelTimer = null;

        function hideList(listNode) {
            listNode.style.display = 'none';
        }

        function showList(listNode) {
            listNode.style.display = 'block';
        }

        function renderSuggestions(listNode, items, onPick) {
            clearChildren(listNode);
            if (!items || items.length === 0) {
                hideList(listNode);
                return;
            }

            items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'suggestion-item';
                li.textContent = item.label;
                li.addEventListener('click', function () {
                    onPick(item);
                });
                listNode.appendChild(li);
            });

            showList(listNode);
        }

        function doAutocomplete(type, query) {
            var url = 'server.php?ajax=1&action=autocomplete&type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(query);
            requestJson(url, function (data) {
                if (!data || !data.ok) {
                    return;
                }

                if (type === 'city') {
                    renderSuggestions(cityList, data.items || [], function (item) {
                        cityInput.value = item.name;
                        if (citySelect) {
                            citySelect.value = String(item.id);
                        }
                        hideList(cityList);
                    });
                    return;
                }

                renderSuggestions(hotelList, data.items || [], function (item) {
                    hotelInput.value = item.name;
                    hideList(hotelList);
                });
            });
        }

        cityInput.addEventListener('input', function () {
            var query = cityInput.value.trim();
            if (citySelect) {
                citySelect.value = '';
            }
            window.clearTimeout(cityTimer);
            if (query.length < 1) {
                hideList(cityList);
                return;
            }
            cityTimer = window.setTimeout(function () {
                doAutocomplete('city', query);
            }, 220);
        });

        hotelInput.addEventListener('input', function () {
            var query = hotelInput.value.trim();
            window.clearTimeout(hotelTimer);
            if (query.length < 1) {
                hideList(hotelList);
                return;
            }
            hotelTimer = window.setTimeout(function () {
                doAutocomplete('hotel', query);
            }, 220);
        });

        document.addEventListener('click', function (event) {
            if (!cityList.contains(event.target) && event.target !== cityInput) {
                hideList(cityList);
            }
            if (!hotelList.contains(event.target) && event.target !== hotelInput) {
                hideList(hotelList);
            }
        });

        if (hasPrototype) {
            return;
        }

        var form = document.getElementById('formReserva');
        var btnPreview = document.getElementById('btnPreview');
        var btnNetejar = document.getElementById('btnNetejar');
        var nomField = document.getElementById('nom');
        var emailField = document.getElementById('email');
        var telefonField = document.getElementById('telefon');
        var dataEntradaField = document.getElementById('dataEntrada');
        var dataSortidaField = document.getElementById('dataSortida');
        var personesField = document.getElementById('persones');
        var previewBox = document.getElementById('previewReserva');
        var previewContent = document.getElementById('previewContingut');

        if (!form || !btnPreview || !btnNetejar || !previewBox || !previewContent) {
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

        function markError(field, errorId, hasError) {
            var errorNode = document.getElementById(errorId);
            if (!field || !errorNode) {
                return !hasError;
            }
            if (hasError) {
                field.classList.add('has-error');
                show(errorNode);
            } else {
                field.classList.remove('has-error');
                hide(errorNode);
            }
            return !hasError;
        }

        function validateNom() {
            return markError(nomField, 'errorNom', !regexNom.test((nomField && nomField.value ? nomField.value : '').trim()));
        }

        function validateEmail() {
            return markError(emailField, 'errorEmail', !regexEmail.test((emailField && emailField.value ? emailField.value : '').trim()));
        }

        function validateTelefon() {
            return markError(telefonField, 'errorTelefon', !regexTelefon.test((telefonField && telefonField.value ? telefonField.value : '').trim()));
        }

        function validateCiutat() {
            return markError(citySelect, 'errorCiutat', !citySelect || citySelect.value === '');
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
            var entrada = dataEntradaField ? dataEntradaField.value : '';
            var sortida = dataSortidaField ? dataSortidaField.value : '';
            var errorDataEntrada = document.getElementById('errorDataEntrada');
            var errorDataSortida = document.getElementById('errorDataSortida');
            var avuiDate = new Date();
            avuiDate.setHours(0, 0, 0, 0);

            if (entrada) {
                var entradaDate = new Date(entrada);
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

            if (!entrada || !sortida) {
                show(errorDataSortida);
                return false;
            }

            if (new Date(sortida) <= new Date(entrada)) {
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

        function renderPreview() {
            var selectedCityText = citySelect && citySelect.selectedIndex >= 0 ? citySelect.options[citySelect.selectedIndex].text : '';
            var selectedTipusNode = document.querySelector('input[name="tipusHabitacio"]:checked');
            var selectedTipus = selectedTipusNode ? selectedTipusNode.value : '';

            var previewHtml = '';
            previewHtml += '<div><strong>Nom:</strong> ' + (nomField ? nomField.value.trim() : '') + '</div>';
            previewHtml += '<div><strong>Email:</strong> ' + (emailField ? emailField.value.trim() : '') + '</div>';
            previewHtml += '<div><strong>Telefon:</strong> ' + (telefonField ? telefonField.value.trim() : '') + '</div>';
            previewHtml += '<div><strong>Ciutat:</strong> ' + selectedCityText + '</div>';
            previewHtml += '<div><strong>Hotel:</strong> ' + (hotelInput ? (hotelInput.value.trim() || 'Sense hotel especificat') : 'Sense hotel especificat') + '</div>';
            previewHtml += '<div><strong>Entrada:</strong> ' + (dataEntradaField ? dataEntradaField.value : '') + '</div>';
            previewHtml += '<div><strong>Sortida:</strong> ' + (dataSortidaField ? dataSortidaField.value : '') + '</div>';
            previewHtml += '<div><strong>Persones:</strong> ' + (personesField ? personesField.value : '') + '</div>';
            previewHtml += '<div><strong>Habitacio:</strong> ' + selectedTipus + '</div>';

            previewContent.innerHTML = previewHtml;
            show(previewBox);
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
        if (citySelect) {
            citySelect.addEventListener('change', validateCiutat);
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
            if (!validateAll()) {
                previewContent.innerHTML = '<div><strong>No es pot previsualitzar:</strong> corregeix els camps marcats en vermell i torna-ho a provar.</div>';
                show(previewBox);
                return;
            }
            renderPreview();
        });

        btnNetejar.addEventListener('click', function (event) {
            if (!confirm('Vols netejar totes les dades del formulari?')) {
                event.preventDefault();
                return;
            }

            window.setTimeout(function () {
                Array.prototype.forEach.call(document.querySelectorAll('.error'), function (node) {
                    hide(node);
                });
                Array.prototype.forEach.call(document.querySelectorAll('.has-error'), function (node) {
                    node.classList.remove('has-error');
                });
                previewContent.innerHTML = '';
                hide(previewBox);
            }, 0);
        });

        form.addEventListener('submit', function (event) {
            if (!validateAll()) {
                event.preventDefault();
                alert('Si us plau, corregeix els errors del formulari');
            }
        });
    });
})();
