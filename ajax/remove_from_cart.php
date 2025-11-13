<?php
session_start();
header('Content-Type: application/json');

// 🔒 Ensure user is authenticated
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté.']);
    exit;
}

// 🔒 Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id'] : 0;

if ($cartItemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID invalide.']);
    exit;
}

try {
    // ✅ Vérifier que l'article appartient à l'utilisateur
    $checkQuery = "SELECT id FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindValue(':cart_item_id', $cartItemId, PDO::PARAM_INT);
    $checkStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Article non trouvé dans votre panier.']);
        exit;
    }

    // ✅ Supprimer l'article
    $deleteQuery = "DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindValue(':cart_item_id', $cartItemId, PDO::PARAM_INT);
    $deleteStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $deleteStmt->execute();

    // ✅ Mettre à jour le nombre total d’articles dans le panier
    $countQuery = "SELECT COALESCE(SUM(quantity), 0) AS total_items FROM cart_items WHERE user_id = :user_id";
    $countStmt = $db->prepare($countQuery);
    $countStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $countStmt->execute();

    $cartCount = (int) $countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Article retiré du panier.',
        'cart_count' => $cartCount
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Cart delete error: " . $e->getMessage()); // Don’t expose DB details
    echo json_encode(['success' => false, 'message' => 'Erreur serveur interne.']);
}
