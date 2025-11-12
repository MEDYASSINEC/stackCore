<?php
// api_product_images.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration de la base de données
require_once 'config/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_product_image':
        getProductImage($pdo);
        break;
    case 'get_multiple_images':
        getMultipleImages($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}

function getProductImage($pdo)
{
    $product_id = $_GET['product_id'] ?? 0;

    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'ID produit manquant']);
        return;
    }

    try {
        // Récupérer l'image principale ou la première image disponible
        $stmt = $pdo->prepare("
            SELECT media_url, media_type, is_main 
            FROM product_media 
            WHERE product_id = ? AND media_type = 'image' 
            ORDER BY is_main DESC, id ASC 
            LIMIT 1
        ");
        $stmt->execute([$product_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            echo json_encode([
                'success' => true,
                'image_url' => $image['media_url'],
                'is_main' => (bool) $image['is_main']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Aucune image trouvée',
                'image_url' => null
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
    }
}

function getMultipleImages($pdo)
{
    $product_ids = $_GET['product_ids'] ?? '';

    if (!$product_ids) {
        echo json_encode(['success' => false, 'message' => 'IDs produits manquants']);
        return;
    }

    // Convertir la chaîne d'IDs en tableau
    $ids = explode(',', $product_ids);
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids); // Supprimer les valeurs nulles

    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'IDs produits invalides']);
        return;
    }

    try {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        // Récupérer l'image principale pour chaque produit
        $stmt = $pdo->prepare("
            SELECT DISTINCT 
                pm1.product_id,
                pm1.media_url,
                pm1.is_main
            FROM product_media pm1
            WHERE pm1.product_id IN ($placeholders) 
            AND pm1.media_type = 'image'
            AND pm1.id = (
                SELECT pm2.id 
                FROM product_media pm2 
                WHERE pm2.product_id = pm1.product_id 
                AND pm2.media_type = 'image'
                ORDER BY pm2.is_main DESC, pm2.id ASC 
                LIMIT 1
            )
        ");
        $stmt->execute($ids);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organiser les résultats par product_id
        $result = [];
        foreach ($images as $image) {
            $result[$image['product_id']] = [
                'image_url' => $image['media_url'],
                'is_main' => (bool) $image['is_main']
            ];
        }

        echo json_encode([
            'success' => true,
            'images' => $result
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
    }
}
?>