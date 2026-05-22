<?php
// Configurar les capçaleres de resposta per a API REST
header("Access-Control-Allow-Origin: *"); // Permetre crides creuades
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// 1. CONFIGURACIÓ DE LA CONNEXIÓ (Normalment aniria en connexio.php)
$host = 'localhost';
$db   = 'api_demo'; // CANVIA-HO per la teva BD
$user = 'root';     // CANVIA-HO pel teu usuari
$pass = '';         // CANVIA-HO per la teva contrasenya

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connexió a la BD fallida. Has creat la base de dades 'api_demo' i la taula 'usuaris'?"]);
    exit;
}

// Obtenim el mètode de la petició (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// LLegim el cos del missatge JSON entrant (Per a POST, PUT, DELETE)
$data = json_decode(file_get_contents("php://input"));

switch ($method) {
    case 'GET':
        // LLEGIR DADES
        $stmt = $pdo->query("SELECT * FROM usuaris ORDER BY id DESC");
        $usuaris = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($usuaris);
        break;

    case 'POST':
        // CREAR DADES
        if(!empty($data->nom) && !empty($data->email)) {
            $stmt = $pdo->prepare("INSERT INTO usuaris (nom, email) VALUES (?, ?)");
            if($stmt->execute([$data->nom, $data->email])) {
                echo json_encode(["missatge" => "Usuari creat correctament!"]);
            } else {
                echo json_encode(["error" => "No s'ha pogut crear l'usuari."]);
            }
        } else {
            echo json_encode(["error" => "Falten dades (nom i email)."]);
        }
        break;

    case 'PUT':
        // ACTUALITZAR DADES
        if(!empty($data->id) && !empty($data->nom) && !empty($data->email)) {
            $stmt = $pdo->prepare("UPDATE usuaris SET nom = ?, email = ? WHERE id = ?");
            if($stmt->execute([$data->nom, $data->email, $data->id])) {
                echo json_encode(["missatge" => "Usuari amb ID {$data->id} actualitzat!"]);
            }
        } else {
            echo json_encode(["error" => "Falta ID, nom o email per actualitzar."]);
        }
        break;

    case 'DELETE':
        // ESBORRAR DADES
        if(!empty($data->id)) {
            $stmt = $pdo->prepare("DELETE FROM usuaris WHERE id = ?");
            if($stmt->execute([$data->id])) {
                echo json_encode(["missatge" => "Usuari amb ID {$data->id} esborrat!"]);
            }
        } else {
            echo json_encode(["error" => "No s'ha especificat l'ID a esborrar."]);
        }
        break;

    default:
        // Mètode no permès
        echo json_encode(["error" => "Mètode no permès"]);
        break;
}
?>