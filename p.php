<?php
require_once 'config/db.php';

try {
    // Secure sorting: only allow valid columns
    $allowedSort = ['id', 'name', 'price', 'stock', 'created_at', 'views'];
    $tri = $_GET['tri'] ?? 'id';
    if (!in_array($tri, $allowedSort)) {
        $tri = 'id';
    }

    $stmt = $conn->prepare("SELECT * FROM products ORDER BY $tri");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get media
    $stmt = $conn->prepare("SELECT product_id, media_url, is_main FROM product_media");
    $stmt->execute();
    $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get product categories
    $stmt = $conn->prepare("
        SELECT pc.product_id, c.name 
        FROM categories c 
        JOIN product_category pc ON c.id = pc.category_id
    ");
    $stmt->execute();
    $productCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all categories
    $stmt = $conn->prepare("SELECT name FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    exit("Database error.");
}

// ------------------ FUNCTIONS ------------------

function modifierProduit(PDO $conn, $id, $colonne, $value)
{
    $allowedColumns = [
        'name', 'description', 'price', 'stock',
        'min_order_quantity', 'is_on_promotion', 'promotion_price', 'views'
    ];

    if (!in_array($colonne, $allowedColumns)) {
        throw new Exception("Colonne non autorisée");
    }

    if ($colonne === 'is_on_promotion') {
        $value = ($value === 'Y' || $value === '1') ? 1 : 0;
    }

    $sql = "UPDATE products SET $colonne = :value WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':value', $value);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

function modifierCategorieProduit(PDO $conn, $id, $categoryName)
{
    $sql = "
        UPDATE product_category 
        SET category_id = (SELECT id FROM categories WHERE name = :name) 
        WHERE product_id = :id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':name', $categoryName);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

function trierProduits(PDO $conn, $tri)
{
    $allowedSort = ['id', 'name', 'price', 'stock', 'created_at', 'views'];
    if (!in_array($tri, $allowedSort)) {
        throw new Exception("Tri non autorisé");
    }

    $sql = "SELECT * FROM products ORDER BY $tri ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ------------------ API HANDLER ------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';
    $colonne = $_POST['colonne'] ?? '';
    $value = $_POST['value'] ?? '';
    $tri = $_POST['tri'] ?? '';

    try {
        if ($action === 'modifier' && $table === 'product' && $id && $colonne) {
            if (modifierProduit($conn, $id, $colonne, $value)) {
                echo json_encode(['success' => true, 'message' => 'Modification réussie']);
            }
        } elseif ($action === 'modifier' && $table === 'product_category' && $id && $value) {
            if (modifierCategorieProduit($conn, $id, $value)) {
                echo json_encode(['success' => true, 'message' => 'Catégorie mise à jour']);
            }
        } elseif ($action === 'tri' && $tri) {
            $products = trierProduits($conn, $tri);
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            throw new Exception("Paramètres invalides");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des produits</title>
</head>
<body>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Media</th>
            <th>Quantité min.</th>
            <th>Promotion</th>
            <th>Prix promo</th>
            <th>Vues</th>
            <th>Créé le</th>
        </tr>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['id']) ?></td>
                <td>
                    <?php
                    foreach ($medias as $media) {
                        if ($media['product_id'] == $product['id'] && $media['is_main']) {
                            echo '<img src="' . htmlspecialchars($media['media_url']) . '" alt="image" width="50">';
                        }
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td>
                    <?php
                    foreach ($productCategories as $cat) {
                        if ($cat['product_id'] == $product['id']) {
                            echo htmlspecialchars($cat['name']);
                        }
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($product['price']) ?></td>
                
                <td><?= htmlspecialchars($product['stock']) ?></td>
                <td>
                    <?php
                    foreach ($medias as $media) {
                        if ($media['product_id'] == $product['id']) {
                            echo htmlspecialchars(str_replace('images/', '', $media['media_url'])) . '<br>';
                        }
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($product['min_order_quantity']) ?></td>
                <td><?= $product['is_on_promotion'] ? 'Y' : 'N' ?></td>
                <td><?= htmlspecialchars($product['promotion_price']) ?></td>
                <td><?= htmlspecialchars($product['views']) ?></td>
                <td><?= htmlspecialchars($product['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>