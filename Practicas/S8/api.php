<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/xml; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/app/db_config.php';

$dbWarning = '';
$conn = open_db_connection($dbWarning);

if (!$conn) {
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<response><status>error</status><message>Connexio a la BD fallida.</message></response>';
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT id, name, city_name, stars, has_pool, price_per_night FROM hotels ORDER BY stars DESC, price_per_night ASC";
    $result = $conn->query($sql);
    
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    
    $root = $xml->createElement('response');
    $xml->appendChild($root);
    
    $statusNode = $xml->createElement('status', 'success');
    $root->appendChild($statusNode);
    
    if ($result) {
        $dataNode = $xml->createElement('hotels');
        $count = 0;
        
        while ($row = $result->fetch_assoc()) {
            $hotelNode = $xml->createElement('hotel');
            $hotelNode->setAttribute('id', $row['id']);
            
            $hotelNode->appendChild($xml->createElement('name', htmlspecialchars($row['name'])));
            $hotelNode->appendChild($xml->createElement('city', htmlspecialchars($row['city_name'])));
            $hotelNode->appendChild($xml->createElement('stars', $row['stars']));
            $hotelNode->appendChild($xml->createElement('has_pool', $row['has_pool']));
            $hotelNode->appendChild($xml->createElement('price', $row['price_per_night']));
            
            $dataNode->appendChild($hotelNode);
            $count++;
        }
        
        $root->appendChild($xml->createElement('count', $count));
        $root->appendChild($dataNode);
        
    } else {
        $root->removeChild($statusNode);
        $root->appendChild($xml->createElement('status', 'error'));
        $root->appendChild($xml->createElement('message', 'Error a la consulta SQL'));
    }
    
    echo $xml->saveXML();
} else {
    echo '<?xml version="1.0" encoding="UTF-8"?><response><status>error</status><message>Method Not Allowed</message></response>';
}

$conn->close();
?>
