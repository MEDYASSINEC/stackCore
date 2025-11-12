<?php
// ajax/apply_discount.php - Vérification et application des codes promo
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_POST) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

include_once '../config/db.php';

// Utilisation de votre connexion PDO
$db = $pdo;

$user_id = $_SESSION['user_id'];
$discount_code = trim($_POST['discount_code']);
$order_total = floatval($_POST['order_total']);

if (empty($discount_code)) {
    echo json_encode(['success' => false, 'message' => 'Code promo requis']);
    exit;
}

try {
    // Vérification du code promo
    $query = "SELECT * FROM discount_codes 
              WHERE code = ? 
              AND valid_from <= CURDATE() 
              AND valid_until >= CURDATE() 
              AND (usage_limit IS NULL OR used_count < usage_limit)
              AND (min_order_amount IS NULL OR ? >= min_order_amount)
              AND (user_id IS NULL OR user_id = ?)";

    $stmt = $db->prepare($query);
    $stmt->execute([$discount_code, $order_total, $user_id]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        echo json_encode([
            'success' => true,
            'discount_percent' => $promo['discount_percent'],
            'message' => 'Code promo valide'
        ]);
    } else {
        // Vérifier les raisons spécifiques de l'échec
        $debug_query = "SELECT *, 
                        CASE 
                            WHEN valid_from > CURDATE() THEN 'not_started'
                            WHEN valid_until < CURDATE() THEN 'expired'
                            WHEN usage_limit IS NOT NULL AND used_count >= usage_limit THEN 'limit_reached'
                            WHEN min_order_amount IS NOT NULL AND ? < min_order_amount THEN 'min_amount_not_met'
                            WHEN user_id IS NOT NULL AND user_id != ? THEN 'user_specific'
                            ELSE 'not_found'
                        END as error_reason
                        FROM discount_codes WHERE code = ?";

        $debug_stmt = $db->prepare($debug_query);
        $debug_stmt->execute([$order_total, $user_id, $discount_code]);
        $debug_result = $debug_stmt->fetch(PDO::FETCH_ASSOC);

        $error_messages = [
            'not_started' => 'Ce code promo n\'est pas encore valide',
            'expired' => 'Ce code promo a expiré',
            'limit_reached' => 'Ce code promo a atteint sa limite d\'utilisation',
            'min_amount_not_met' => 'Montant minimum non atteint pour ce code promo',
            'user_specific' => 'Ce code promo n\'est pas valide pour votre compte',
            'not_found' => 'Code promo invalide'
        ];

        $message = isset($debug_result['error_reason']) && isset($error_messages[$debug_result['error_reason']])
            ? $error_messages[$debug_result['error_reason']]
            : 'Code promo invalide';

        echo json_encode(['success' => false, 'message' => $message]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la vérification du code']);
}
?>