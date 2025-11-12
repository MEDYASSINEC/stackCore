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
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID produit invalide']);
        exit;
    }

    try {
        // Vérifier si le produit existe
        $product_check = "SELECT id FROM products WHERE id = :product_id";
        $product_stmt = $db->prepare($product_check);
        $product_stmt->bindParam(':product_id', $product_id);
        $product_stmt->execute();

        if ($product_stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }

        // Vérifier si le produit est déjà dans les favoris
        $check_query = "SELECT * FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->bindParam(':product_id', $product_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // Retirer des favoris
            $delete_query = "DELETE FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->bindParam(':user_id', $user_id);
            $delete_stmt->bindParam(':product_id', $product_id);

            if ($delete_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Retiré des favoris',
                    'action' => 'removed',
                    'is_favorite' => false
                ]);
            }
        } else {
            // Ajouter aux favoris
            $insert_query = "INSERT INTO favorites (user_id, product_id) VALUES (:user_id, :product_id)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':product_id', $product_id);

            if ($insert_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ajouté aux favoris',
                    'action' => 'added',
                    'is_favorite' => true
                ]);
            }
        }

    } catch (PDOException $exception) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>