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

    if ($cart_item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID invalide']);
        exit;
    }

    try {
        // Vérifier que l'article appartient à l'utilisateur
        $check_query = "SELECT * FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':cart_item_id', $cart_item_id);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Article non trouvé dans votre panier']);
            exit;
        }

        // Supprimer l'article
        $delete_query = "DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':cart_item_id', $cart_item_id);
        $delete_stmt->bindParam(':user_id', $user_id);

        if ($delete_stmt->execute()) {
            // Obtenir le nouveau nombre d'articles dans le panier
            $count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = :user_id";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->bindParam(':user_id', $user_id);
            $count_stmt->execute();
            $cart_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0;

            echo json_encode([
                'success' => true,
                'message' => 'Article retiré du panier',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }

    } catch (PDOException $exception) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

?>