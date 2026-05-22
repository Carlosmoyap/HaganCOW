<?php

function get_db_config()
{
    $envHost = getenv('DB_HOST');
    $envPort = getenv('DB_PORT');
    $envUser = getenv('DB_USER');
    $envPass = getenv('DB_PASS');
    $envName = getenv('DB_NAME');

    return array(
        'host' => ($envHost !== false && $envHost !== '') ? $envHost : 'localhost',
        'port' => ($envPort !== false && ctype_digit((string)$envPort)) ? (int)$envPort : 3306,
        'user' => ($envUser !== false && $envUser !== '') ? $envUser : 'root',
        'pass' => ($envPass !== false) ? $envPass : '',
        'name' => ($envName !== false && $envName !== '') ? $envName : 'world',
    );
}

function open_db_connection(&$dbWarning = '')
{
    $dbConfig = get_db_config();
    $dbName = $dbConfig['name'];

    $hosts = array_unique(array($dbConfig['host'], 'localhost', '127.0.0.1'));
    $passwordCandidates = array($dbConfig['pass']);
    if ($dbConfig['pass'] !== '') {
        $passwordCandidates[] = '';
    }

    foreach ($hosts as $host) {
        foreach ($passwordCandidates as $password) {
            try {
                $conn = new mysqli($host, $dbConfig['user'], $password, $dbName, (int)$dbConfig['port']);
            } catch (mysqli_sql_exception $e) {
                $conn = null;
            }

            if ($conn !== null && !$conn->connect_error) {
                $conn->set_charset('utf8mb4');
                return $conn;
            }

            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
    }

    $dbWarning = 'No s\'ha pogut connectar a la base de dades world.';
    return null;
}
