<?php

require_once __DIR__ . '/db_config.php';

function load_client_page_data()
{
    $cities = get_fallback_cities();
    $hotels = array();
    $dbWarning = '';

    $conn = open_db_connection($dbWarning);
    if ($conn === null) {
        $dbWarning = 'No s\'ha pogut connectar a la base de dades world. Es mostren ciutats de mostra.';
        return array($cities, $hotels, $dbWarning);
    }

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
    if ($resCities && $resCities->num_rows > 0) {
        $cities = array();
        while ($row = $resCities->fetch_assoc()) {
            $cities[] = $row;
        }
        $resCities->free();
    } else {
        $dbWarning = 'No s\'han pogut carregar les ciutats de world. Es mostren ciutats de mostra.';
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

function get_fallback_cities()
{
    return array(
        array('id' => 1001, 'name' => 'Barcelona', 'country_code' => 'ES'),
        array('id' => 1002, 'name' => 'Paris', 'country_code' => 'FR'),
        array('id' => 1003, 'name' => 'Roma', 'country_code' => 'IT'),
        array('id' => 1004, 'name' => 'Lisboa', 'country_code' => 'PT'),
        array('id' => 1005, 'name' => 'Valencia', 'country_code' => 'ES'),
    );
}

function load_users_data_json()
{
    // Aquesta funció s'encarrega d'executar els requeriments específics:
    // a. Recuperar múltiples variables des de columnes diferents d'una taula (nom, email i passwords).
    // b. Retornar aquestes dades per codificar en JSON.
    $dbWarning = '';
    $users = array();
    $conn = open_db_connection($dbWarning);
    
    if ($conn !== null) {
        // Creem una taula d'usuaris fictícia per a la demostració
        $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            rol VARCHAR(20) DEFAULT 'client'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($sqlUsers);

        // Inserim algunes dades de referència
        $conn->query("INSERT IGNORE INTO users (nom, email, password_hash, rol) VALUES
            ('Joan García', 'joan@exemple.com', '" . password_hash('Pass1234', PASSWORD_DEFAULT) . "', 'client'),
            ('Marta Pons', 'marta@exemple.com', '" . password_hash('Qwert567', PASSWORD_DEFAULT) . "', 'admin'),
            ('Pere Lluís', 'pere@exemple.com', '" . password_hash('Zxcv890', PASSWORD_DEFAULT) . "', 'client')");

        // a. Recuperar múltiples variables des de columnes diferents d'una taula (nom, email, passwords, rol)
        $res = $conn->query("SELECT id, nom, email, password_hash, rol FROM users ORDER BY nom ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users[] = $row;
            }
            $res->free();
        }
        $conn->close();
    } else {
        // Si no hi ha base de dades, utilitzem valors dummy per codificar al JSON
        $users = array(
            array('id' => 1, 'nom' => 'Joan García (Offline)', 'email' => 'joan@exemple.com', 'password_hash' => '***', 'rol' => 'client'),
            array('id' => 2, 'nom' => 'Marta Pons (Offline)', 'email' => 'marta@exemple.com', 'password_hash' => '***', 'rol' => 'admin')
        );
    }
    
    return $users;
}
