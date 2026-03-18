<?php
/*
 * Connexio directa a MySQL des de client.php (sense fitxer extern de BD).
 * Es carrega la llista de ciutats de world.cities i, opcionalment, la taula hotels.
 */
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'world';

$cities = array();
$hotels = array();
$dbWarning = '';

$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    $dbWarning = 'No s\'ha pogut connectar a la base de dades world. Revisa la configuracio de MySQL/XAMPP.';
} else {
    $conn->set_charset('utf8mb4');

    // Taula opcional de hotels (part opcional de la practica)
    $sqlHotels = "CREATE TABLE IF NOT EXISTS hotels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL UNIQUE,
        city_name VARCHAR(120) NOT NULL,
        stars TINYINT NOT NULL,
        has_pool TINYINT(1) NOT NULL DEFAULT 0,
        has_spa TINYINT(1) NOT NULL DEFAULT 0,
        has_gym TINYINT(1) NOT NULL DEFAULT 0,
        price_per_night DECIMAL(10,2) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sqlHotels);

    // Seed minim per mostrar informació d'hotels a client.php
    $conn->query("INSERT IGNORE INTO hotels (name, city_name, stars, has_pool, has_spa, has_gym, price_per_night) VALUES
        ('Hotel Paradise Barcelona', 'Barcelona', 4, 1, 1, 1, 145.00),
        ('Grand Lisbon Center', 'Lisboa', 5, 1, 1, 1, 210.00),
        ('Roma Riverside Stay', 'Roma', 3, 0, 0, 1, 98.00)");

    $resCities = $conn->query("SELECT id, name, country_code FROM cities ORDER BY name LIMIT 500");
    if ($resCities) {
        while ($row = $resCities->fetch_assoc()) {
            $cities[] = $row;
        }
        $resCities->free();
    }

    $resHotels = $conn->query("SELECT name, city_name, stars, has_pool, has_spa, has_gym, price_per_night FROM hotels ORDER BY stars DESC, name ASC");
    if ($resHotels) {
        while ($row = $resHotels->fetch_assoc()) {
            $hotels[] = $row;
        }
        $resHotels->free();
    }

    $conn->close();
}
?>
<!--
    CLIENT.PHP - PÀGINA DE RESERVA D'HOTEL
    
    Requisit 1: Primera de les dues pàgines PHP requerides
    Funció: Mostrar el formulari per efectuar una reserva d'hotel
    
    Implementa:
    - Formulari HTML amb diversos tipus de controls (input, select, radio, textarea)
    - Validació client-side amb expressions regulars (REGEXP) en JavaScript
    - Enviament de dades per mètode POST a server.php
    - Framework Bootstrap per al disseny responsive
-->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva d'Hotel</title>
    <!-- Bootstrap per al disseny responsive -->
    <link rel="stylesheet" href="bootstrap-3.3.7-dist/css/bootstrap.min.css">
    <!-- Fulls d'estil personalitzats (organitzats en fitxers CSS separats) -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/client.css">
</head>
<body>
    <!-- Barra de navegació amb Bootstrap -->
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.html">
                    <span class="glyphicon glyphicon-home"></span> Hotel Paradise
                </a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="index.html">Inicio</a></li>
                <li class="active"><a href="client.php">Reservar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="container-reserva">
            <div class="header-reserva">
                <h1><span class="glyphicon glyphicon-home"></span> Reserva del teu Hotel</h1>
                <p class="lead">Completa el formulari per confirmar la teva reserva</p>
            </div>

            <?php if (!empty($dbWarning)) { ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars($dbWarning); ?>
                </div>
            <?php } ?>

            <?php if (!empty($hotels)) { ?>
                <div class="panel panel-info" style="margin-bottom: 25px;">
                    <div class="panel-heading">
                        <strong>Hotels disponibles (part opcional)</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Hotel</th>
                                    <th>Ciutat</th>
                                    <th>Estrelles</th>
                                    <th>Piscina</th>
                                    <th>Spa</th>
                                    <th>Gimnàs</th>
                                    <th>Preu/Nit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hotels as $hotel) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                                        <td><?php echo htmlspecialchars($hotel['city_name']); ?></td>
                                        <td><?php echo (int)$hotel['stars']; ?></td>
                                        <td><?php echo ((int)$hotel['has_pool'] === 1) ? 'Si' : 'No'; ?></td>
                                        <td><?php echo ((int)$hotel['has_spa'] === 1) ? 'Si' : 'No'; ?></td>
                                        <td><?php echo ((int)$hotel['has_gym'] === 1) ? 'Si' : 'No'; ?></td>
                                        <td><?php echo number_format((float)$hotel['price_per_night'], 2); ?> EUR</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

            <!-- 
                FORMULARI DE RESERVA
                Requisit 2: Formulari amb diversos controls i botons
                Requisit 3: Utilitza method="POST" per enviar dades a server.php
                novalidate: Desactiva la validació HTML5 per utilitzar la nostra validació REGEXP
            -->
            <form id="formReserva" action="server.php" method="POST" novalidate>
                
                <!-- Camp de text: Nom complet del client -->
                <div class="form-group">
                    <label for="nom">Nom Complet <span class="required">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Joan García López" required>
                    <span class="error" id="errorNom">El nom ha de contenir només lletres i espais (mínim 3 caràcters)</span>
                </div>

                <!-- Camp email: Correu electrònic -->
                <div class="form-group">
                    <label for="email">Correu Electrònic <span class="required">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="exemple@correu.com" required>
                    <span class="error" id="errorEmail">Introdueix un correu electrònic vàlid</span>
                </div>

                <!-- Camp telèfon: Número de contacte -->
                <div class="form-group">
                    <label for="telefon">Telèfon <span class="required">*</span></label>
                    <input type="tel" class="form-control" id="telefon" name="telefon" placeholder="+34 600 123 456" required>
                    <span class="error" id="errorTelefon">Format vàlid: +34 600 123 456 o 600123456</span>
                </div>

                <!-- Select: Selecció de ciutat de destinació -->
                <div class="form-group">
                    <label for="ciutat">Ciutat de Destinació <span class="required">*</span></label>
                    <select class="form-control" id="ciutat" name="ciutat" required>
                        <option value="">Selecciona una ciutat</option>
                        <?php foreach ($cities as $city) { ?>
                            <option value="<?php echo (int)$city['id']; ?>">
                                <?php echo htmlspecialchars($city['name'] . ' (' . $city['country_code'] . ')'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <span class="error" id="errorCiutat">Selecciona una ciutat</span>
                </div>

                <!-- Camps de data: Entrada i sortida -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dataEntrada">Data d'Entrada <span class="required">*</span></label>
                            <input type="date" class="form-control" id="dataEntrada" name="dataEntrada" required>
                            <span class="error" id="errorDataEntrada">Selecciona una data vàlida</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dataSortida">Data de Sortida <span class="required">*</span></label>
                            <input type="date" class="form-control" id="dataSortida" name="dataSortida" required>
                            <span class="error" id="errorDataSortida">La data de sortida ha de ser posterior a l'entrada</span>
                        </div>
                    </div>
                </div>

                <!-- Nombre de persones -->
                <div class="form-group">
                    <label for="persones">Nombre de Persones <span class="required">*</span></label>
                    <select class="form-control" id="persones" name="persones" required>
                        <option value="">Selecciona</option>
                        <option value="1">1 Persona</option>
                        <option value="2">2 Persones</option>
                        <option value="3">3 Persones</option>
                        <option value="4">4 Persones</option>
                        <option value="5">5+ Persones</option>
                    </select>
                    <span class="error" id="errorPersones">Selecciona el nombre de persones</span>
                </div>

                <!-- Tipus d'habitació -->
                <div class="form-group">
                    <label>Tipus d'Habitació <span class="required">*</span></label>
                    <div class="radio">
                        <label>
                            <input type="radio" name="tipusHabitacio" value="Individual" required> Individual
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="tipusHabitacio" value="Doble"> Doble
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="tipusHabitacio" value="Suite"> Suite
                        </label>
                    </div>
                    <span class="error" id="errorTipusHabitacio">Selecciona un tipus d'habitació</span>
                </div>

                <!-- Comentaris opcionals -->
                <div class="form-group">
                    <label for="comentaris">Comentaris o Peticions Especials</label>
                    <textarea class="form-control" id="comentaris" name="comentaris" rows="4" placeholder="Indica qualsevol petició especial..."></textarea>
                </div>

                <!-- Botó enviar -->
                <button type="submit" class="btn btn-primary btn-reservar">
                    <span class="glyphicon glyphicon-ok"></span> Confirmar Reserva
                </button>
            </form>
        </div>
    </div>

    <script src="jquery-ui-1.12.1/external/jquery/jquery.js"></script>
    <script src="bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script>
        /*
         * VALIDACIÓ CLIENT-SIDE AMB EXPRESSIONS REGULARS (REGEXP)
         * Requisit 2: Control de dades amb REGEXP
         * 
         * Aquesta validació es fa al navegador abans d'enviar les dades al servidor.
         * Proporciona feedback immediat a l'usuari i redueix càrrega al servidor.
         */
        $(document).ready(function() {
            // Establir data mínima a avui per evitar reserves en dates passades
            var avui = new Date().toISOString().split('T')[0];
            $('#dataEntrada').attr('min', avui);
            $('#dataSortida').attr('min', avui);

            /*
             * DEFINICIÓ D'EXPRESSIONS REGULARS (REGEXP)
             * Requisit 2: Validació amb expressions regulars
             */
            
            // Nom: només lletres (inclou caràcters catalans), espais, entre 3 i 50 caràcters
            var regexNom = /^[A-Za-zÀ-ÿ\s]{3,50}$/;
            
            // Email: format estàndard d'email amb @ i domini
            var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            
            // Telèfon: format espanyol amb/sense prefix internacional, 9 dígits començant per 6-9
            var regexTelefon = /^(\+34|0034)?[\s]?[6-9][0-9]{8}$/;

            /*
             * VALIDACIÓ EN TEMPS REAL (event 'blur' - quan l'usuari deixa el camp)
             * Comprova cada camp amb la seva expressió regular corresponent
             */
            $('#nom').on('blur', function() {
                var valor = $(this).val().trim();
                if (!regexNom.test(valor)) {
                    $(this).addClass('has-error');
                    $('#errorNom').show();
                } else {
                    $(this).removeClass('has-error');
                    $('#errorNom').hide();
                }
            });

            $('#email').on('blur', function() {
                var valor = $(this).val().trim();
                if (!regexEmail.test(valor)) {
                    $(this).addClass('has-error');
                    $('#errorEmail').show();
                } else {
                    $(this).removeClass('has-error');
                    $('#errorEmail').hide();
                }
            });

            $('#telefon').on('blur', function() {
                var valor = $(this).val().trim();
                if (!regexTelefon.test(valor)) {
                    $(this).addClass('has-error');
                    $('#errorTelefon').show();
                } else {
                    $(this).removeClass('has-error');
                    $('#errorTelefon').hide();
                }
            });

            $('#ciutat').on('change', function() {
                if ($(this).val() === '') {
                    $(this).addClass('has-error');
                    $('#errorCiutat').show();
                } else {
                    $(this).removeClass('has-error');
                    $('#errorCiutat').hide();
                }
            });

            $('#persones').on('change', function() {
                if ($(this).val() === '') {
                    $(this).addClass('has-error');
                    $('#errorPersones').show();
                } else {
                    $(this).removeClass('has-error');
                    $('#errorPersones').hide();
                }
            });

            // Validació de dates
            $('#dataEntrada, #dataSortida').on('change', function() {
                var dataEntrada = new Date($('#dataEntrada').val());
                var dataSortida = new Date($('#dataSortida').val());
                var avui = new Date();
                avui.setHours(0, 0, 0, 0);

                if ($('#dataEntrada').val() && dataEntrada < avui) {
                    $('#dataEntrada').addClass('has-error');
                    $('#errorDataEntrada').text('La data no pot ser anterior a avui').show();
                } else {
                    $('#dataEntrada').removeClass('has-error');
                    $('#errorDataEntrada').hide();
                }

                if ($('#dataEntrada').val() && $('#dataSortida').val()) {
                    if (dataSortida <= dataEntrada) {
                        $('#dataSortida').addClass('has-error');
                        $('#errorDataSortida').show();
                    } else {
                        $('#dataSortida').removeClass('has-error');
                        $('#errorDataSortida').hide();
                    }
                }
            });

            // Validació abans d'enviar el formulari
            $('#formReserva').on('submit', function(e) {
                var valid = true;

                // Validar nom
                if (!regexNom.test($('#nom').val().trim())) {
                    $('#nom').addClass('has-error');
                    $('#errorNom').show();
                    valid = false;
                }

                // Validar email
                if (!regexEmail.test($('#email').val().trim())) {
                    $('#email').addClass('has-error');
                    $('#errorEmail').show();
                    valid = false;
                }

                // Validar telèfon
                if (!regexTelefon.test($('#telefon').val().trim())) {
                    $('#telefon').addClass('has-error');
                    $('#errorTelefon').show();
                    valid = false;
                }

                // Validar ciutat
                if ($('#ciutat').val() === '') {
                    $('#ciutat').addClass('has-error');
                    $('#errorCiutat').show();
                    valid = false;
                }

                // Validar dates
                var dataEntrada = new Date($('#dataEntrada').val());
                var dataSortida = new Date($('#dataSortida').val());
                if (!$('#dataEntrada').val() || !$('#dataSortida').val() || dataSortida <= dataEntrada) {
                    $('#errorDataSortida').show();
                    valid = false;
                }

                // Validar persones
                if ($('#persones').val() === '') {
                    $('#persones').addClass('has-error');
                    $('#errorPersones').show();
                    valid = false;
                }

                // Validar tipus habitació
                if (!$('input[name="tipusHabitacio"]:checked').length) {
                    $('#errorTipusHabitacio').show();
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                    alert('Si us plau, corregeix els errors del formulari');
                }
            });
        });
    </script>
</body>
</html>