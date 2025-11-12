<?php
$host = 'sql203.infinityfree.com';
$db = 'if0_39394855_stackcore_db';
$username = 'if0_39394855';
$password = 'b1Zogl8tXJXx';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // erreurs levées en exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES => false,                  // vraie préparation des requêtes
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données.'
        // 'error' => $e->getMessage() // à activer en DEV uniquement
    ]);
    exit;
}

