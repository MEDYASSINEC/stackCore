<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['cart_count' => 0]);
    exit;
}

include_once '../config/database.php';
include_once '../helpers/cart_functions.php';

$database = new Database();
$db = $database->getConnection();

$cart_count = getCartCount($_SESSION['user_id'], $db);

echo json_encode(['cart_count' => $cart_count]);

?>