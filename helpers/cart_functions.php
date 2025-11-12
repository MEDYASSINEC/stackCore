<?php
// helpers/cart_functions.php - Fonctions pour la gestion du panier

/**
 * Récupère les articles du panier d'un utilisateur
 */
function getCartItems($user_id, $db)
{
    $query = "SELECT ci.*, p.name, p.description, p.price, p.is_on_promotion, p.promotion_price, p.stock,
                     pm.media_url
              FROM cart_items ci
              JOIN products p ON ci.product_id = p.id
              LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_main = 1
              WHERE ci.user_id = ?
              ORDER BY ci.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calcule le total du panier
 */
function getCartTotal($user_id, $db)
{
    $query = "SELECT SUM(
                CASE 
                    WHEN p.is_on_promotion = 1 THEN p.promotion_price * ci.quantity
                    ELSE p.price * ci.quantity
                END
              ) as total
              FROM cart_items ci
              JOIN products p ON ci.product_id = p.id
              WHERE ci.user_id = ?";

    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total'] ? floatval($result['total']) : 0;
}

/**
 * Compte le nombre d'articles dans le panier
 */
function getCartCount($user_id, $db)
{
    $query = "SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['count'] ? intval($result['count']) : 0;
}

/**
 * Ajoute un produit au panier
 */
function addToCart($user_id, $product_id, $quantity, $db)
{
    try {
        // Vérifier si le produit existe et est en stock
        $product_query = "SELECT id, name, stock, min_order_quantity FROM products WHERE id = ?";
        $product_stmt = $db->prepare($product_query);
        $product_stmt->execute([$product_id]);
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }

        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Stock insuffisant'];
        }

        if ($quantity < $product['min_order_quantity']) {
            return ['success' => false, 'message' => 'Quantité minimum: ' . $product['min_order_quantity']];
        }

        // Vérifier si le produit est déjà dans le panier
        $check_query = "SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$user_id, $product_id]);
        $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Mettre à jour la quantité
            $new_quantity = $existing_item['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                return ['success' => false, 'message' => 'Stock insuffisant pour cette quantité'];
            }

            $update_query = "UPDATE cart_items SET quantity = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // Ajouter un nouveau produit
            $insert_query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->execute([$user_id, $product_id, $quantity]);
        }

        return ['success' => true, 'message' => 'Produit ajouté au panier'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur lors de l\'ajout au panier'];
    }
}

/**
 * Met à jour la quantité d'un article dans le panier
 */
function updateCartItemQuantity($cart_item_id, $quantity, $user_id, $db)
{
    try {
        // Vérifier que l'article appartient à l'utilisateur
        $check_query = "SELECT ci.product_id, p.stock, p.min_order_quantity
                       FROM cart_items ci
                       JOIN products p ON ci.product_id = p.id
                       WHERE ci.id = ? AND ci.user_id = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$cart_item_id, $user_id]);
        $item = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }

        if ($quantity < $item['min_order_quantity']) {
            return ['success' => false, 'message' => 'Quantité minimum: ' . $item['min_order_quantity']];
        }

        if ($quantity > $item['stock']) {
            return ['success' => false, 'message' => 'Stock insuffisant'];
        }

        $update_query = "UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->execute([$quantity, $cart_item_id, $user_id]);

        return ['success' => true, 'message' => 'Quantité mise à jour'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Supprime un article du panier
 */
function removeFromCart($cart_item_id, $user_id, $db)
{
    try {
        $delete_query = "DELETE FROM cart_items WHERE id = ? AND user_id = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->execute([$cart_item_id, $user_id]);

        if ($delete_stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Article supprimé du panier'];
        } else {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }
}

/**
 * Vide complètement le panier d'un utilisateur
 */
function clearCart($user_id, $db)
{
    try {
        $delete_query = "DELETE FROM cart_items WHERE user_id = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->execute([$user_id]);

        return ['success' => true, 'message' => 'Panier vidé'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur lors du vidage du panier'];
    }
}

/**
 * Vérifie la disponibilité des produits dans le panier avant checkout
 */
function validateCartStock($user_id, $db)
{
    $query = "SELECT ci.id, ci.quantity, ci.product_id, p.name, p.stock, p.min_order_quantity
              FROM cart_items ci
              JOIN products p ON ci.product_id = p.id
              WHERE ci.user_id = ?";

    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $errors = [];

    foreach ($items as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = "Stock insuffisant pour {$item['name']} (disponible: {$item['stock']})";
        }

        if ($item['quantity'] < $item['min_order_quantity']) {
            $errors[] = "Quantité minimum pour {$item['name']}: {$item['min_order_quantity']}";
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'items' => $items
    ];
}
function getFavoriteProducts($user_id, $db)
{
    try {
        $query = "
            SELECT 
                p.id,
                p.name,
                p.description,
                p.price,
                p.stock,
                p.min_order_quantity,
                p.is_on_promotion,
                p.promotion_price,
                p.views,
                pm.media_url,
                p.created_at as product_created_at
            FROM favorites f
            INNER JOIN products p ON f.product_id = p.id
            LEFT JOIN (
                SELECT product_id, media_url,
                       ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY 
                           CASE WHEN is_main = 1 THEN 0 ELSE 1 END, id) as rn
                FROM product_media 
                WHERE media_type = 'image'
            ) pm ON p.id = pm.product_id AND pm.rn = 1
            WHERE f.user_id = :user_id
            ORDER BY p.created_at DESC
        ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Traitement des données pour s'assurer que tous les champs sont présents
        foreach ($favorites as &$product) {
            // Valeurs par défaut si NULL
            $product['media_url'] = $product['media_url'] ?? null;
            $product['is_on_promotion'] = (bool) $product['is_on_promotion'];
            $product['promotion_price'] = $product['promotion_price'] ?? 0;
            $product['min_order_quantity'] = $product['min_order_quantity'] ?? 1;
            $product['stock'] = $product['stock'] ?? 0;
            $product['views'] = $product['views'] ?? 0;
        }

        return $favorites;

    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des favoris: " . $e->getMessage());
        return [];
    }
}
?>