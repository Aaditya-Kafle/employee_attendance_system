<?php

// $host = 'localhost';
// $database   = 'np03cs4a240225';
// $username = 'np03cs4a240225';
// $password = 'MdNoEF3WKq';

$host = 'localhost';
$database   = 'employee_system';
$username = 'root';
$password = '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => true,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    die('Database connection failed.');
}
?>