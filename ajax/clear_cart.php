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

    try {
        $delete_query = "DELETE FROM cart_items WHERE user_id = :user_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':user_id', $user_id);

        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Panier vidé avec succès'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du vidage du panier']);
        }

    } catch (PDOException $exception) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>