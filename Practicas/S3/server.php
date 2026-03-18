<!--
    SERVER.PHP - PÀGINA DE PROCESSAMENT I VISUALITZACIÓ DE RESERVES
    
    Requisit 1: Segona de les dues pàgines PHP requerides
    Funció: Rebre, validar i mostrar el resultat de la reserva
    
    Implementa:
    - Recepció de dades amb mètode POST ($_POST)
    - Validació server-side amb expressions regulars (preg_match)
    - Neteja i sanitització de dades
    - Visualització del resultat de la reserva o errors detectats
-->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmació de Reserva</title>
    <!-- Bootstrap per al disseny responsive -->
    <link rel="stylesheet" href="bootstrap-3.3.7-dist/css/bootstrap.min.css">
    <!-- Fulls d'estil personalitzats -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/server.css">
</head>
<body>
    <!-- Barra de navegació -->
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.html">
                    <span class="glyphicon glyphicon-home"></span> Hotel Paradise
                </a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="client.php">Reservar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="container-confirmacio">
            
<?php
/*
 * PROCESSAMENT I VALIDACIÓ DE DADES AL SERVIDOR
 * Requisit 3: Recepció de dades amb POST
 * Requisit 2: Validació amb expressions regulars (REGEXP) en PHP
 */

// Arrays per emmagatzemar errors i dades validades
$errors = array();
$dadesReserva = array();

// Connexio directa a MySQL des de server.php (sense fitxer extern de BD)
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'world';
$dbWarning = '';
$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    $dbWarning = 'No s\'ha pogut connectar a la base de dades world.';
    $conn = null;
} else {
    $conn->set_charset('utf8mb4');
    $sqlReserves = "CREATE TABLE IF NOT EXISTS clients_reserves (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reservation_code VARCHAR(20) NOT NULL UNIQUE,
        client_name VARCHAR(100) NOT NULL,
        email VARCHAR(120) NOT NULL,
        phone VARCHAR(25) NOT NULL,
        city_id INT NOT NULL,
        city_name VARCHAR(120) NOT NULL,
        checkin_date DATE NOT NULL,
        checkout_date DATE NOT NULL,
        nights INT NOT NULL,
        guests INT NOT NULL,
        room_type VARCHAR(20) NOT NULL,
        comments TEXT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_city_id (city_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!$conn->query($sqlReserves)) {
        $dbWarning = 'No s\'ha pogut crear/verificar la taula clients_reserves.';
    }
}

/*
 * COMPROVACIÓ DEL MÈTODE D'ENVIAMENT
 * Requisit 3: Verificar que les dades s'han enviat per POST
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    /*
     * DEFINICIÓ D'EXPRESSIONS REGULARS (REGEXP) AL SERVIDOR
     * Requisit 2: Control de dades amb REGEXP en PHP
     * Es tornen a validar al servidor per seguretat (mai confiar només en validació client)
     */
    
    // Mateixos patrons que al client per coherència
    $regexNom = "/^[A-Za-zÀ-ÿ\s]{3,50}$/";
    $regexEmail = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";
    $regexTelefon = "/^(\+34|0034)?[\s]?[6-9][0-9]{8}$/";
    
    /*
     * FUNCIÓ DE NETEJA I SANITITZACIÓ DE DADES
     * Seguretat: Prevenir injecció de codi i XSS
     */
    function neteja_dades($dada) {
        $dada = trim($dada);              // Elimina espais al principi i final
        $dada = stripslashes($dada);      // Elimina barres invertides
        $dada = htmlspecialchars($dada);  // Converteix caràcters especials aHTML
        return $dada;
    }
    
    /*
     * VALIDACIÓ DEL NOM
     * Requisit 2: Utilitza preg_match() per aplicar REGEXP
     */
    if (empty($_POST["nom"])) {
        $errors[] = "El nom és obligatori";
    } else {
        $nom = neteja_dades($_POST["nom"]);
        // Aplicació de l'expressió regular amb preg_match()
        if (!preg_match($regexNom, $nom)) {
            $errors[] = "El nom només pot contenir lletres i espais (mínim 3 caràcters)";
        } else {
            $dadesReserva["nom"] = $nom;
        }
    }
    
    /*
     * VALIDACIÓ DE L'EMAIL
     * Requisit 2: Utilitza preg_match() per aplicar REGEXP
     */
    if (empty($_POST["email"])) {
        $errors[] = "L'email és obligatori";
    } else {
        $email = neteja_dades($_POST["email"]);
        // Aplicació de l'expressió regular amb preg_match()
        if (!preg_match($regexEmail, $email)) {
            $errors[] = "El format de l'email no és vàlid";
        } else {
            $dadesReserva["email"] = $email;
        }
    }
    
    // VALIDACIÓ DEL TELÈFON
    if (empty($_POST["telefon"])) {
        $errors[] = "El telèfon és obligatori";
    } else {
        $telefon = neteja_dades($_POST["telefon"]);
        if (!preg_match($regexTelefon, $telefon)) {
            $errors[] = "El format del telèfon no és vàlid";
        } else {
            $dadesReserva["telefon"] = $telefon;
        }
    }
    
    // VALIDACIÓ DE LA CIUTAT (ara es valida contra world.cities)
    if (empty($_POST["ciutat"])) {
        $errors[] = "La ciutat és obligatòria";
    } else {
        $cityId = neteja_dades($_POST["ciutat"]);
        if (!ctype_digit($cityId)) {
            $errors[] = "La ciutat seleccionada no és vàlida";
        } elseif ($conn === null) {
            $errors[] = "No es pot validar la ciutat sense connexió a base de dades";
        } else {
            $stmtCity = $conn->prepare("SELECT id, name, country_code FROM cities WHERE id = ? LIMIT 1");
            if ($stmtCity) {
                $cityIdInt = (int)$cityId;
                $stmtCity->bind_param("i", $cityIdInt);
                $stmtCity->execute();
                $resultCity = $stmtCity->get_result();
                if ($resultCity && $resultCity->num_rows > 0) {
                    $cityRow = $resultCity->fetch_assoc();
                    $dadesReserva["cityId"] = (int)$cityRow["id"];
                    $dadesReserva["ciutat"] = $cityRow["name"];
                    $dadesReserva["countryCode"] = $cityRow["country_code"];
                } else {
                    $errors[] = "La ciutat seleccionada no existeix a la base de dades";
                }
                if ($resultCity) {
                    $resultCity->free();
                }
                $stmtCity->close();
            } else {
                $errors[] = "Error intern validant la ciutat";
            }
        }
    }
    
    // VALIDACIÓ DE LES DATES
    if (empty($_POST["dataEntrada"]) || empty($_POST["dataSortida"])) {
        $errors[] = "Les dates són obligatòries";
    } else {
        $dataEntrada = neteja_dades($_POST["dataEntrada"]);
        $dataSortida = neteja_dades($_POST["dataSortida"]);
        
        $dateEntrada = strtotime($dataEntrada);
        $dateSortida = strtotime($dataSortida);
        $dateAvui = strtotime(date("Y-m-d"));
        
        if ($dateEntrada < $dateAvui) {
            $errors[] = "La data d'entrada no pot ser anterior a avui";
        } elseif ($dateSortida <= $dateEntrada) {
            $errors[] = "La data de sortida ha de ser posterior a la data d'entrada";
        } else {
            $dadesReserva["dataEntradaDB"] = date("Y-m-d", $dateEntrada);
            $dadesReserva["dataSortidaDB"] = date("Y-m-d", $dateSortida);
            $dadesReserva["dataEntrada"] = date("d/m/Y", $dateEntrada);
            $dadesReserva["dataSortida"] = date("d/m/Y", $dateSortida);
            
            // Calcular nombre de nits
            $diferencia = $dateSortida - $dateEntrada;
            $dadesReserva["nits"] = floor($diferencia / (60 * 60 * 24));
        }
    }
    
    // VALIDACIÓ DEL NOMBRE DE PERSONES
    if (empty($_POST["persones"])) {
        $errors[] = "El nombre de persones és obligatori";
    } else {
        $persones = neteja_dades($_POST["persones"]);
        if (!in_array($persones, array("1", "2", "3", "4", "5"))) {
            $errors[] = "El nombre de persones no és vàlid";
        } else {
            $dadesReserva["persones"] = $persones;
        }
    }
    
    // VALIDACIÓ DEL TIPUS D'HABITACIÓ
    if (empty($_POST["tipusHabitacio"])) {
        $errors[] = "El tipus d'habitació és obligatori";
    } else {
        $tipusHabitacio = neteja_dades($_POST["tipusHabitacio"]);
        if (!in_array($tipusHabitacio, array("Individual", "Doble", "Suite"))) {
            $errors[] = "El tipus d'habitació no és vàlid";
        } else {
            $dadesReserva["tipusHabitacio"] = $tipusHabitacio;
        }
    }
    
    // COMENTARIS (camp opcional - no requereix validació estricta)
    if (!empty($_POST["comentaris"])) {
        $dadesReserva["comentaris"] = neteja_dades($_POST["comentaris"]);
    }
    
    /*
     * PROCESSAMENT FINAL I VISUALITZACIÓ DE RESULTATS
     * Requisit 1: Visualització del resultat de la reserva
     */
    if (empty($errors)) {
        // ===== RESERVA VÀLIDA: GENERAR CONFIRMACIÓ =====
        
        // Generar codi únic de reserva amb hash MD5
        $codiReserva = "RES-" . strtoupper(substr(md5(uniqid()), 0, 8));
        $dadesReserva["codiReserva"] = $codiReserva;
        
        // Calcular preu total segons tipus d'habitació i nombre de nits
        $preuBase = 80; // preu base per nit habitació individual
        if ($dadesReserva["tipusHabitacio"] == "Doble") {
            $preuBase = 120;
        } elseif ($dadesReserva["tipusHabitacio"] == "Suite") {
            $preuBase = 200;
        }
        $preuTotal = $preuBase * $dadesReserva["nits"];
        $dadesReserva["preuTotal"] = $preuTotal;

        // Inserir reserva a clients_reserves
        $saveOk = false;
        $saveMessage = '';
        if ($conn === null) {
            $saveMessage = 'Reserva validada, pero no s\'ha pogut guardar per falta de connexio a la base de dades.';
        } else {
            $insertSql = "INSERT INTO clients_reserves
                (reservation_code, client_name, email, phone, city_id, city_name, checkin_date, checkout_date, nights, guests, room_type, comments, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtInsert = $conn->prepare($insertSql);
            if ($stmtInsert) {
                $cityIdInsert = (int)$dadesReserva["cityId"];
                $nitsInsert = (int)$dadesReserva["nits"];
                $personesInsert = (int)$dadesReserva["persones"];
                $comentarisInsert = isset($dadesReserva["comentaris"]) ? $dadesReserva["comentaris"] : null;
                $preuInsert = (float)$preuTotal;

                $stmtInsert->bind_param(
                    "ssssisssiissd",
                    $codiReserva,
                    $dadesReserva["nom"],
                    $dadesReserva["email"],
                    $dadesReserva["telefon"],
                    $cityIdInsert,
                    $dadesReserva["ciutat"],
                    $dadesReserva["dataEntradaDB"],
                    $dadesReserva["dataSortidaDB"],
                    $nitsInsert,
                    $personesInsert,
                    $dadesReserva["tipusHabitacio"],
                    $comentarisInsert,
                    $preuInsert
                );

                if ($stmtInsert->execute()) {
                    $saveOk = true;
                    $saveMessage = 'Reserva guardada correctament a la taula clients_reserves.';
                } else {
                    $saveMessage = 'La reserva es valida, pero no s\'ha pogut inserir a la base de dades.';
                }
                $stmtInsert->close();
            } else {
                $saveMessage = 'La reserva es valida, pero hi ha un error preparant la insercio SQL.';
            }
        }
        
        /*
         * MOSTRAR CONFIRMACIÓ DE RESERVA
         * Visualització detallada de totes les dades processades
         */
        echo '<div class="header-confirmacio">';
        echo '<span class="glyphicon glyphicon-ok-circle success-icon"></span>';
        echo '<h1>Reserva Confirmada!</h1>';
        echo '<p class="lead">La teva reserva s\'ha processat correctament</p>';
        echo '</div>';
        
        // Mostrar codi de reserva destacat
        echo '<div class="codi-reserva">';
        echo 'Codi de Reserva: ' . $codiReserva;
        echo '</div>';
        
        echo '<div class="alert alert-success alert-custom">';
        echo '<strong>Important:</strong> Guarda aquest codi per a futures consultes. T\'hem enviat un email de confirmació a <strong>' . $dadesReserva["email"] . '</strong>';
        echo '</div>';

        if (!empty($dbWarning)) {
            echo '<div class="alert alert-warning alert-custom">' . htmlspecialchars($dbWarning) . '</div>';
        }

        if ($saveOk) {
            echo '<div class="alert alert-info alert-custom">' . htmlspecialchars($saveMessage) . '</div>';
        } else {
            echo '<div class="alert alert-warning alert-custom">' . htmlspecialchars($saveMessage) . '</div>';
        }
        
        // Bloc de detalls de la reserva
        echo '<div class="info-reserva">';
        echo '<h3><span class="glyphicon glyphicon-list-alt"></span> Detalls de la Reserva</h3>';
        echo '<hr>';
        
        // Mostrar cada camp validat i processat
        echo '<div class="info-item">';
        echo '<span class="info-label">Nom del client:</span>';
        echo '<span class="info-value">' . $dadesReserva["nom"] . '</span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Email:</span>';
        echo '<span class="info-value">' . $dadesReserva["email"] . '</span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Telèfon:</span>';
        echo '<span class="info-value">' . $dadesReserva["telefon"] . '</span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Destinació:</span>';
        echo '<span class="info-value"><strong>' . $dadesReserva["ciutat"] . ' (' . $dadesReserva["countryCode"] . ')</strong></span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Data d\'entrada:</span>';
        echo '<span class="info-value">' . $dadesReserva["dataEntrada"] . '</span>';
        echo '</div>';

        // Mostrar ultimes reserves guardades (lectura de BD)
        if ($conn !== null) {
            $recentSql = "SELECT reservation_code, client_name, city_name, checkin_date, checkout_date, total_price
                          FROM clients_reserves
                          ORDER BY id DESC
                          LIMIT 5";
            $recentResult = $conn->query($recentSql);
            if ($recentResult && $recentResult->num_rows > 0) {
                echo '<div class="info-reserva" style="margin-top: 25px;">';
                echo '<h3><span class="glyphicon glyphicon-time"></span> Ultimes reserves registrades</h3>';
                echo '<div class="table-responsive">';
                echo '<table class="table table-striped">';
                echo '<thead><tr><th>Codi</th><th>Client</th><th>Ciutat</th><th>Entrada</th><th>Sortida</th><th>Total</th></tr></thead>';
                echo '<tbody>';
                while ($row = $recentResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['reservation_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['client_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['city_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['checkin_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['checkout_date']) . '</td>';
                    echo '<td>' . number_format((float)$row['total_price'], 2) . ' EUR</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
                echo '</div>';
                $recentResult->free();
            }
        }
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Data de sortida:</span>';
        echo '<span class="info-value">' . $dadesReserva["dataSortida"] . '</span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Nombre de nits:</span>';
        echo '<span class="info-value"><strong>' . $dadesReserva["nits"] . ' nit' . ($dadesReserva["nits"] > 1 ? 's' : '') . '</strong></span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Nombre de persones:</span>';
        echo '<span class="info-value">' . $dadesReserva["persones"] . ' persona' . ($dadesReserva["persones"] > 1 ? 'es' : '') . '</span>';
        echo '</div>';
        
        echo '<div class="info-item">';
        echo '<span class="info-label">Tipus d\'habitació:</span>';
        echo '<span class="info-value">' . $dadesReserva["tipusHabitacio"] . '</span>';
        echo '</div>';
        
        if (isset($dadesReserva["comentaris"]) && !empty($dadesReserva["comentaris"])) {
            echo '<div class="info-item">';
            echo '<span class="info-label">Comentaris:</span>';
            echo '<span class="info-value">' . nl2br($dadesReserva["comentaris"]) . '</span>';
            echo '</div>';
        }
        
        echo '<div class="info-item" style="background: #e8f5e9; margin-top: 20px; padding: 15px; border-radius: 5px;">';
        echo '<span class="info-label" style="font-size: 18px;">Preu Total:</span>';
        echo '<span class="info-value" style="font-size: 24px; color: #2ecc71; font-weight: bold;">' . number_format($preuTotal, 2) . ' €</span>';
        echo '</div>';
        
        echo '</div>';
        
        // Botó per fer una altra reserva
        echo '<div class="text-center">';
        echo '<a href="client.php" class="btn btn-primary btn-lg btn-tornar">';
        echo '<span class="glyphicon glyphicon-arrow-left"></span> Fer una altra reserva';
        echo '</a>';
        echo '</div>';
        
    } else {
        /*
         * GESTIÓ D'ERRORS
         * Si hi ha errors de validació, mostrar-los a l'usuari
         * Això demostra la validació server-side amb REGEXP
         */
        echo '<div class="header-confirmacio">';
        echo '<span class="glyphicon glyphicon-remove-circle error-icon"></span>';
        echo '<h1>Error en la Reserva</h1>';
        echo '<p class="lead">S\'han detectat errors en les dades enviades</p>';
        echo '</div>';
        
        // Llistar tots els errors detectats durant la validació
        echo '<div class="alert alert-danger alert-custom">';
        echo '<h4><span class="glyphicon glyphicon-exclamation-sign"></span> Errors detectats:</h4>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        // Botó per tornar al formulari i corregir els errors
        echo '<div class="text-center">';
        echo '<a href="client.php" class="btn btn-warning btn-lg btn-tornar">';
        echo '<span class="glyphicon glyphicon-arrow-left"></span> Tornar al formulari';
        echo '</a>';
        echo '</div>';
    }
    
} else {
    /*
     * ACCÉS NO VÀLID
     * Si s'intenta accedir directament sense enviar dades per POST
     * Protecció: només es pot accedir a aquesta pàgina des del formulari
     */
    echo '<div class="header-confirmacio">';
    echo '<span class="glyphicon glyphicon-warning-sign error-icon"></span>';
    echo '<h1>Accés No Vàlid</h1>';
    echo '<p class="lead">No s\'han rebut dades de reserva</p>';
    echo '</div>';
    
    echo '<div class="alert alert-warning alert-custom">';
    echo 'Si us plau, accedeix a aquesta pàgina a través del formulari de reserva.';
    echo '</div>';
    
    echo '<div class="text-center">';
    echo '<a href="client.php" class="btn btn-primary btn-lg btn-tornar">';
    echo '<span class="glyphicon glyphicon-home"></span> Anar al formulari de reserva';
    echo '</a>';
    echo '</div>';
}

if ($conn !== null) {
    $conn->close();
}
?>

        </div>
    </div>

    <script src="jquery-ui-1.12.1/external/jquery/jquery.js"></script>
    <script src="bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
</body>
</html>