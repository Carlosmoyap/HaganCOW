<?php

function load_client_page_data()
{
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
        return array($cities, $hotels, $dbWarning);
    }

    $conn->set_charset('utf8mb4');

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

    return array($cities, $hotels, $dbWarning);
}
