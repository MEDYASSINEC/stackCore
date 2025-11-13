<?php
session_start();
header('Content-Type: application/json');

// 🔒 Verify authentication
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté.']);
    exit;
}

// 🔒 Accept only POST
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
    error_log("DB connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

if ($cartItemId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

try {
    // ✅ Get item and related product
    $query = "
        SELECT ci.id, ci.quantity AS current_qty, p.stock, p.min_order_quantity, p.name 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = :cart_item_id AND ci.user_id = :user_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':cart_item_id', $cartItemId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Article non trouvé dans votre panier.']);
        exit;
    }

    // ✅ Validate quantity
    if ($quantity < $item['min_order_quantity']) {
        echo json_encode([
            'success' => false,
            'message' => 'Quantité minimale requise: ' . (int) $item['min_order_quantity']
        ]);
        exit;
    }

    if ($quantity > $item['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuffisant. Stock disponible: ' . (int) $item['stock']
        ]);
        exit;
    }

    // ✅ Update cart quantity
    $update = $db->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :cart_item_id AND user_id = :user_id");
    $update->bindValue(':quantity', $quantity, PDO::PARAM_INT);
    $update->bindValue(':cart_item_id', $cartItemId, PDO::PARAM_INT);
    $update->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $update->execute();

    // ✅ Fetch new cart total
    $count = $db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = :user_id");
    $count->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $count->execute();
    $cartCount = (int) $count->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Quantité mise à jour avec succès.',
        'cart_count' => $cartCount
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Cart quantity update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur interne.']);
}
