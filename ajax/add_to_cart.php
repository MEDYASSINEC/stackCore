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
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit;
    }

    try {
        // Vérifier si le produit existe et obtenir les détails
        $query = "SELECT id, name, stock, min_order_quantity FROM products WHERE id = :product_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }

        // Vérifier la quantité minimale de commande
        if ($quantity < $product['min_order_quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Quantité minimale requise: ' . $product['min_order_quantity']
            ]);
            exit;
        }

        // Vérifier le stock disponible
        if ($quantity > $product['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Stock insuffisant. Stock disponible: ' . $product['stock']
            ]);
            exit;
        }

        // Vérifier si le produit est déjà dans le panier
        $check_query = "SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->bindParam(':product_id', $product_id);
        $check_stmt->execute();

        $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Mettre à jour la quantité
            $new_quantity = $existing_item['quantity'] + $quantity;

            // Vérifier le stock pour la nouvelle quantité
            if ($new_quantity > $product['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Stock insuffisant pour cette quantité totale'
                ]);
                exit;
            }

            $update_query = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':quantity', $new_quantity);
            $update_stmt->bindParam(':id', $existing_item['id']);

            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Quantité mise à jour dans le panier',
                    'action' => 'updated'
                ]);
            }
        } else {
            // Ajouter un nouvel élément au panier
            $insert_query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':product_id', $product_id);
            $insert_stmt->bindParam(':quantity', $quantity);

            if ($insert_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Produit ajouté au panier',
                    'action' => 'added'
                ]);
            }
        }

        // Obtenir le nombre total d'articles dans le panier
        $count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = :user_id";
        $count_stmt = $db->prepare($count_query);
        $count_stmt->bindParam(':user_id', $user_id);
        $count_stmt->execute();
        $cart_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0;

        // Ajouter le nombre d'articles à la réponse
        $response = json_decode(ob_get_contents(), true);
        $response['cart_count'] = $cart_count;

        ob_clean();
        echo json_encode($response);

    } catch (PDOException $exception) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>