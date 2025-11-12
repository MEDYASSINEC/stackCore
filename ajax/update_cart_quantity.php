<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '../config/database.php';

    $database = new Database();
    $db = $database->getConnection();

    $user_id = $_SESSION['user_id'];
    $cart_item_id = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if ($cart_item_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit;
    }

    try {
        // Obtenir les informations de l'article et du produit
        $item_query = "SELECT ci.*, p.stock, p.min_order_quantity, p.name 
                       FROM cart_items ci 
                       JOIN products p ON ci.product_id = p.id 
                       WHERE ci.id = :cart_item_id AND ci.user_id = :user_id";
        $item_stmt = $db->prepare($item_query);
        $item_stmt->bindParam(':cart_item_id', $cart_item_id);
        $item_stmt->bindParam(':user_id', $user_id);
        $item_stmt->execute();

        $item = $item_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Article non trouvé dans votre panier']);
            exit;
        }

        // Vérifier la quantité minimale
        if ($quantity < $item['min_order_quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Quantité minimale requise: ' . $item['min_order_quantity']
            ]);
            exit;
        }

        // Vérifier le stock
        if ($quantity > $item['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Stock insuffisant. Stock disponible: ' . $item['stock']
            ]);
            exit;
        }

        // Mettre à jour la quantité
        $update_query = "UPDATE cart_items SET quantity = :quantity WHERE id = :cart_item_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':quantity', $quantity);
        $update_stmt->bindParam(':cart_item_id', $cart_item_id);

        if ($update_stmt->execute()) {
            // Obtenir le nouveau total du panier
            $count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = :user_id";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->bindParam(':user_id', $user_id);
            $count_stmt->execute();
            $cart_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0;

            echo json_encode([
                'success' => true,
                'message' => 'Quantité mise à jour',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }

    } catch (PDOException $exception) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>