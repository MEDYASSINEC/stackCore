<?php
// ajax/cart_actions.php - Actions AJAX pour le panier
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

include_once '../config/db.php';
include_once '../helpers/cart_functions.php';

// Utilisation de votre connexion PDO
$db = $pdo;
$user_id = $_SESSION['user_id'];

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Action non spécifiée']);
    exit;
}

switch ($_POST['action']) {
    case 'add':
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);

        $result = addToCart($user_id, $product_id, $quantity, $db);

        if ($result['success']) {
            $result['cart_count'] = getCartCount($user_id, $db);
            $result['cart_total'] = getCartTotal($user_id, $db);
        }

        echo json_encode($result);
        break;

    case 'update':
        $cart_item_id = intval($_POST['cart_item_id']);
        $quantity = intval($_POST['quantity']);

        $result = updateCartItemQuantity($cart_item_id, $quantity, $user_id, $db);

        if ($result['success']) {
            $result['cart_count'] = getCartCount($user_id, $db);
            $result['cart_total'] = getCartTotal($user_id, $db);
        }

        echo json_encode($result);
        break;

    case 'remove':
        $cart_item_id = intval($_POST['cart_item_id']);

        $result = removeFromCart($cart_item_id, $user_id, $db);

        if ($result['success']) {
            $result['cart_count'] = getCartCount($user_id, $db);
            $result['cart_total'] = getCartTotal($user_id, $db);
        }

        echo json_encode($result);
        break;

    case 'clear':
        $result = clearCart($user_id, $db);

        if ($result['success']) {
            $result['cart_count'] = 0;
            $result['cart_total'] = 0;
        }

        echo json_encode($result);
        break;

    case 'get_count':
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount($user_id, $db),
            'cart_total' => getCartTotal($user_id, $db)
        ]);
        break;

    case 'validate_stock':
        $validation = validateCartStock($user_id, $db);
        echo json_encode([
            'success' => $validation['valid'],
            'errors' => $validation['errors'],
            'message' => $validation['valid'] ? 'Stock validé' : 'Problèmes de stock détectés'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
        break;
}
?>