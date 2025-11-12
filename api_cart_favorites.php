<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration de la base de données
require_once 'config/db.php';
// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Fonction pour obtenir l'ID utilisateur (à adapter selon votre système d'authentification)
function getUserId()
{
    // Si l'utilisateur est connecté, retourner son ID
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }

    // Sinon, utiliser l'ID de session comme identifiant temporaire
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['temp_user_id'] = 'temp_' . session_id();
    }

    return $_SESSION['temp_user_id'];
}

// Fonction pour nettoyer les données
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fonction pour envoyer une réponse JSON
function sendResponse($success, $message = '', $data = [])
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    $userId = getUserId();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        handleGetRequest($pdo, $userId);
    } elseif ($method === 'POST') {
        handlePostRequest($pdo, $userId);
    } else {
        sendResponse(false, 'Méthode non autorisée');
    }

} catch (PDOException $e) {
    error_log("Erreur base de données: " . $e->getMessage());
    sendResponse(false, 'Erreur de base de données');
} catch (Exception $e) {
    error_log("Erreur générale: " . $e->getMessage());
    sendResponse(false, 'Erreur serveur');
}

function handleGetRequest($pdo, $userId)
{
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_counters':
            getCounters($pdo, $userId);
            break;

        case 'get_status':
            getProductsStatus($pdo, $userId);
            break;

        case 'get_cart':
            getCart($pdo, $userId);
            break;

        case 'get_favorites':
            getFavorites($pdo, $userId);
            break;

        default:
            sendResponse(false, 'Action non reconnue');
    }
}

function handlePostRequest($pdo, $userId)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        sendResponse(false, 'Données JSON invalides');
    }

    $action = $input['action'] ?? '';

    switch ($action) {
        case 'add_to_cart':
            addToCart($pdo, $userId, $input);
            break;

        case 'remove_from_cart':
            removeFromCart($pdo, $userId, $input);
            break;

        case 'update_cart_quantity':
            updateCartQuantity($pdo, $userId, $input);
            break;

        case 'clear_cart':
            clearCart($pdo, $userId);
            break;

        case 'toggle_favorite':
            toggleFavorite($pdo, $userId, $input);
            break;

        case 'remove_favorite':
            removeFavorite($pdo, $userId, $input);
            break;

        default:
            sendResponse(false, 'Action non reconnue');
    }
}

function getCounters($pdo, $userId)
{
    try {
        // Compter les favoris - utilise la table 'favorites'
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $favoritesCount = $stmt->fetch()['count'];

        // Compter les articles dans le panier - utilise la table 'cart_items'
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cartCount = $stmt->fetch()['count'] ?? 0;

        sendResponse(true, '', [
            'favorites_count' => (int) $favoritesCount,
            'cart_count' => (int) $cartCount
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors du chargement des compteurs');
    }
}

function getProductsStatus($pdo, $userId)
{
    try {
        // Récupérer les favoris - utilise la table 'favorites'
        $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $favorites = [];
        while ($row = $stmt->fetch()) {
            $favorites[$row['product_id']] = true;
        }

        // Récupérer le panier - utilise la table 'cart_items'
        $stmt = $pdo->prepare("SELECT product_id FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = [];
        while ($row = $stmt->fetch()) {
            $cart[$row['product_id']] = true;
        }

        sendResponse(true, '', [
            'favorites' => $favorites,
            'cart' => $cart
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors du chargement des statuts');
    }
}

function addToCart($pdo, $userId, $input)
{
    $productId = (int) ($input['product_id'] ?? 0);
    $quantity = (int) ($input['quantity'] ?? 1);

    if (!$productId || $quantity <= 0) {
        sendResponse(false, 'Données invalides');
    }

    try {
        // Vérifier si le produit existe déjà dans le panier
        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Mettre à jour la quantité
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$newQuantity, $userId, $productId]);
            $message = 'Quantité mise à jour dans le panier';
        } else {
            // Ajouter nouveau produit
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $productId, $quantity]);
            $message = 'Produit ajouté au panier';
        }

        // Récupérer le nouveau total
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cartCount = $stmt->fetch()['count'] ?? 0;

        sendResponse(true, $message, [
            'cart_count' => (int) $cartCount
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors de l\'ajout au panier');
    }
}

function removeFromCart($pdo, $userId, $input)
{
    $productId = (int) ($input['product_id'] ?? 0);

    if (!$productId) {
        sendResponse(false, 'ID produit invalide');
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);

        if ($stmt->rowCount() > 0) {
            // Récupérer le nouveau total
            $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
            $cartCount = $stmt->fetch()['count'] ?? 0;

            sendResponse(true, 'Produit retiré du panier', [
                'cart_count' => (int) $cartCount
            ]);
        } else {
            sendResponse(false, 'Produit non trouvé dans le panier');
        }

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors de la suppression');
    }
}

function updateCartQuantity($pdo, $userId, $input)
{
    $productId = (int) ($input['product_id'] ?? 0);
    $quantity = (int) ($input['quantity'] ?? 0);

    if (!$productId || $quantity <= 0) {
        sendResponse(false, 'Données invalides');
    }

    try {
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $productId]);

        if ($stmt->rowCount() > 0) {
            // Récupérer le nouveau total
            $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
            $cartCount = $stmt->fetch()['count'] ?? 0;

            sendResponse(true, 'Quantité mise à jour', [
                'cart_count' => (int) $cartCount
            ]);
        } else {
            sendResponse(false, 'Produit non trouvé dans le panier');
        }

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors de la mise à jour');
    }
}

function clearCart($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);

        sendResponse(true, 'Panier vidé', [
            'cart_count' => 0
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors du vidage du panier');
    }
}

function toggleFavorite($pdo, $userId, $input)
{
    $productId = (int) ($input['product_id'] ?? 0);

    if (!$productId) {
        sendResponse(false, 'ID produit invalide');
    }

    try {
        // Vérifier si le produit est déjà en favori
        $stmt = $pdo->prepare("SELECT user_id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Retirer des favoris
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $message = 'Retiré des favoris';
            $isFavorite = false;
        } else {
            // Ajouter aux favoris
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$userId, $productId]);
            $message = 'Ajouté aux favoris';
            $isFavorite = true;
        }

        // Récupérer le nouveau total
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $favoritesCount = $stmt->fetch()['count'];

        sendResponse(true, $message, [
            'is_favorite' => $isFavorite,
            'favorites_count' => (int) $favoritesCount
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors de la modification des favoris');
    }
}

function removeFavorite($pdo, $userId, $input)
{
    $productId = (int) ($input['product_id'] ?? 0);

    if (!$productId) {
        sendResponse(false, 'ID produit invalide');
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);

        if ($stmt->rowCount() > 0) {
            // Récupérer le nouveau total
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
            $stmt->execute([$userId]);
            $favoritesCount = $stmt->fetch()['count'];

            sendResponse(true, 'Retiré des favoris', [
                'favorites_count' => (int) $favoritesCount
            ]);
        } else {
            sendResponse(false, 'Produit non trouvé dans les favoris');
        }

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors de la suppression');
    }
}

function getCart($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT ci.product_id, ci.quantity, p.name as product_name, p.price,
                   (p.price * ci.quantity) as total,
                   ci.created_at
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ? 
            ORDER BY ci.created_at DESC
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        // Calculer le total général
        $totalAmount = array_sum(array_column($cartItems, 'total'));
        $totalQuantity = array_sum(array_column($cartItems, 'quantity'));

        sendResponse(true, '', [
            'items' => $cartItems,
            'total_amount' => $totalAmount,
            'total_quantity' => $totalQuantity,
            'items_count' => count($cartItems)
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors du chargement du panier');
    }
}

function getFavorites($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT f.product_id, p.name as product_name, p.price
            FROM favorites f
            JOIN products p ON f.product_id = p.id
            WHERE f.user_id = ? 
        ");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll();

        sendResponse(true, '', [
            'favorites' => $favorites,
            'count' => count($favorites)
        ]);

    } catch (PDOException $e) {
        sendResponse(false, 'Erreur lors du chargement des favoris');
    }
}
?>