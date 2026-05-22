<?php
require_once __DIR__ . '/app/client_data.php';
list($cities, $hotels, $dbWarning) = load_client_page_data();
?>
<!--
    CLIENT.PHP - PÀGINA DE RESERVA D'HOTEL
    
    Sessio 4: millores client-side amb JavaScript
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
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="jquery-ui-1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="jquery-ui-1.12.1/jquery-ui.theme.min.css">
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
                <li><a href="admin.html">Admin (AJAX/JSON)</a></li>
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

            <?php if (!empty($hotels)) { 
                // Ordenem hotels per estrelles (DESC) i preu (ASC)
                usort($hotels, function($a, $b) {
                    if ($a['stars'] == $b['stars']) {
                        return $a['price_per_night'] <=> $b['price_per_night'];
                    }
                    return $b['stars'] <=> $a['stars'];
                });
                $topHotels = array_slice($hotels, 0, 3);
            ?>
                <div class="panel panel-warning panel-hotels" style="border-color: #faebcc;">
                    <div class="panel-heading" style="background-color: #fcf8e3; color: #8a6d3b; border-color: #faebcc;">
                        <strong><span class="glyphicon glyphicon-star"></span> Els nostres hotels destacats</strong>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php foreach ($topHotels as $hotel) { ?>
                                <div class="col-md-4">
                                    <div class="thumbnail text-center" style="border: 2px solid #faebcc; border-radius: 8px;">
                                        <div class="caption">
                                            <h4 style="color: #8a6d3b; font-weight: bold; height: 40px;"><?php echo htmlspecialchars($hotel['name']); ?></h4>
                                            <p class="text-muted"><span class="glyphicon glyphicon-map-marker"></span> <?php echo htmlspecialchars($hotel['city_name']); ?></p>
                                            <p style="font-size: 1.2em;">
                                                <?php for($i=0; $i<$hotel['stars']; $i++) { echo '⭐'; } ?>
                                            </p>
                                            <h3 style="color: #333; margin-top: 10px;"><?php echo number_format((float)$hotel['price_per_night'], 2); ?> € <small>/ nit</small></h3>
                                            <hr style="margin: 10px 0;">
                                            <p class="small text-muted" style="height: 20px;">
                                                <?php 
                                                $extras = [];
                                                if((int)$hotel['has_pool'] === 1) $extras[] = "Piscina";
                                                if((int)$hotel['has_spa'] === 1) $extras[] = "Spa";
                                                if((int)$hotel['has_gym'] === 1) $extras[] = "Gimnàs";
                                                echo !empty($extras) ? implode(" · ", $extras) : "Dormitori simple";
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
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

                <div class="form-group autocomplete-group">
                    <label for="ciutatSearch">Auto-completar ciutat (Ajax)</label>
                    <input type="text" class="form-control" id="ciutatSearch" placeholder="Escriu les inicials de la ciutat...">
                </div>

                <div class="form-group autocomplete-group">
                    <label for="hotelSearch">Auto-completar hotel (Ajax)</label>
                    <input type="text" class="form-control" id="hotelSearch" name="hotelNom" placeholder="Escriu les inicials de l'hotel...">
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

                <!-- Camp Ocult per desar les Peticions Especials de jQuery -->
                <input type="hidden" id="comentarisHidden" name="comentaris" value="">

                <!-- SECCIÓ DE PETICIONS ESPECIALS (Requisit jQuery) -->
                <div class="panel panel-default" style="margin-top: 20px;">
                    <div class="panel-heading">Peticions Especials (jQuery)</div>
                    <div class="panel-body">
                        <div class="input-group">
                            <input type="text" id="novaPeticio" class="form-control" placeholder="Escriu una petició especial...">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="button" id="btnAfegirPeticio">Afegir</button>
                            </span>
                        </div>
                        <br>
                        <ul id="llistaPeticions" class="list-group">
                            <!-- Les peticions s'afegiran aquí -->
                        </ul>
                        <button class="btn btn-warning btn-sm" type="button" id="btnDestacarPeticions">Destacar peticions senars i amb filtre</button>
                    </div>
                </div>

                <!-- Botó enviar -->
                <div class="form-group actions-group">
                    <button type="button" id="btnPreview" class="btn btn-info">
                        <span class="glyphicon glyphicon-eye-open"></span> Previsualitzar
                    </button>
                    <button type="reset" id="btnNetejar" class="btn btn-default btn-gap-left">
                        <span class="glyphicon glyphicon-refresh"></span> Netejar
                    </button>
                    <button type="submit" class="btn btn-primary btn-reservar btn-gap-left">
                        <span class="glyphicon glyphicon-ok"></span> Confirmar Reserva
                    </button>
                </div>

                <div id="previewReserva" class="alert alert-info preview-reserva">
                    <strong>Previsualitzacio:</strong>
                    <div id="previewContingut" class="preview-contingut"></div>
                </div>

                <div id="ajaxResult" class="ajax-result-box"></div>
            </form>
        </div>
    </div>

    <!-- S'ha actualitzat a la llibreria de jQuery sol·licitada -->
    <script src="jquery_3_4_0/jquery-3.4.0.min.js"></script>
    <!-- Mantenim jQuery UI si cal per altres coses (però usem jQuery 3.4.0 de base) -->
    <script src="jquery-ui-1.12.1/jquery-ui.min.js"></script>
    <script src="bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script src="js/client-form.js"></script>
</body>
</html>