<?php
// Configurar les capçaleres de resposta per a API REST / JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Carreguem la configuració de connexió a la BD utilitzada a la pràctica
require_once __DIR__ . '/app/db_config.php';

$dbWarning = '';
$conn = open_db_connection($dbWarning);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Connexió a la BD fallida: " . $dbWarning]);
    exit;
}

// Obtenim el mètode de la petició
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // a. Recuperem múltiples variables des de columnes diferents de la taula clients_reserves
    // Dades útils: nom, email, telèfon, l'hotel, les dates...
    $sql = "SELECT reservation_code, client_name, email, phone, city_name, hotel_name, checkin_date, checkout_date 
            FROM clients_reserves 
            ORDER BY id DESC LIMIT 50";
            
    $result = $conn->query($sql);
    
    if ($result) {
        $reserves = [];
        while ($row = $result->fetch_assoc()) {
            $reserves[] = $row; // Anem afegint instàncies
        }
        
        // b. Codifiquem l'array associatiu en format JSON
        echo json_encode([
            "status" => "success", 
            "count" => count($reserves),
            "data" => $reserves
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error a la consulta SQL"]);
    }
} else {
    // Si no és una petició GET (No mètode permès en el nostre escenari restringit)
    echo json_encode(["status" => "error", "error" => "Mètode no permès. Es requereix GET."]);
}

$conn->close();
?>