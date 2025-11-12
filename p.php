<?php

require_once 'config/db.php';

try {

    // get products
    $tri = $_GET['tri'] ?? 'id';
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY $tri");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // get media

    $stmt2 = $conn->prepare("SELECT product_id, media_url , is_main FROM product_media");
    $stmt2->execute();
    $medias = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // get product & categories

    $stmt3 = $conn->prepare("SELECT pc.product_id, c.name FROM categories as c JOIN product_category as pc ON c.id = pc.category_id");
    $stmt3->execute();
    $product_categories = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // get categories
    $stmt4 = $conn->prepare("SELECT name FROM categories");
    $stmt4->execute();
    $categories = $stmt4->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "stackCore_db";
$conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);

function modifierTProd($id, $colonne, $value)
{

    try {
        require_once 'db.php';
        // Vérifier que la colonne est autorisée
        $colonnes_autorisees = ['name', 'description', 'price', 'stock', 'min_order_quantity', 'is_on_promotion', 'promotion_price', 'views'];
        if (!in_array($colonne, $colonnes_autorisees)) {
            throw new Exception("Colonne non autorisée");
        }

        if ($colonne === 'is_on_promotion') {
            $value = ($value == 'Y') ? 1 : 0;
        }


        $requete = "UPDATE products SET {$colonne} = ? WHERE id = ?";
        $stmt = $conn->prepare($requete);
        $stmt->bindParam(1, $value);
        $stmt->bindParam(2, $id);

        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    }
}

function modifierTProdC($id, $value)
{
    try {
        require_once 'db.php';

        $requete = "UPDATE product_category SET category_id = (SELECT id FROM categories WHERE name=?) WHERE product_id = ?";
        $stmt = $conn->prepare($requete);
        $stmt->bindParam(1, $value);
        $stmt->bindParam(2, $id);

        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    }
}

function trierProduits($tri)
{
    try {
        require_once 'db.php';


        $requete = "SELECT * FROM products ORDER BY {$tri} ASC";
        $stmt = $conn->prepare($requete);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';
    $colonne = $_POST['colonne'] ?? '';
    $value = $_POST['value'] ?? '';
    $tri = $_POST['tri'] ?? '';

    try {
        if ($action === 'modifier' && $table === 'product' && !empty($id) && !empty($colonne) && !empty($value)) {
            if (modifierTProd($id, $colonne, $value)) {
                echo json_encode(['success' => true, 'message' => 'Modification réussie']);
            }
        } elseif ($action === 'modifier' && $table === 'product_category' && !empty($id) && !empty($value)) {
            if (modifierTProdC($id, $value)) {
                echo json_encode(['success' => true, 'message' => 'Modification réussie']);
            }
        } elseif ($action === 'tri' && !empty($tri)) {
            $products = trierProduits($tri);
            // echo json_encode($products);
        } else {
            throw new Exception("Paramètres invalides");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <table>
        <tr>
            <th class="tri" data-colonne="id">ID</th>
            <th>Image</th>
            <th class="tri" data-colonne="name">Name</th>
            <th>Description</th>
            <th>Category</th>
            <th class="tri" data-colonne="price">Price</th>
            <th class="tri" data-colonne="stock">Stock</th>
            <th>Media</th>
            <th class="tri" data-colonne="min_order_quantity">Min Order Quantity</th>
            <th class='tri' data-colonne="is_on_promotion">En Promotion</th>
            <th class="tri" data-colonne="promotion_price">Promotion Prix</th>
            <th class="tri" data-colonne="views">Views</th>
            <th class="tri" data-colonne="created_at">Created At</th>
        </tr>
        <?php foreach ($products as $product): ?>
            <tr data-id="<?php echo $product['id']; ?>">
                <td><?php echo $product['id']; ?></td>
                <td>
                    <img src="<?php
                    foreach ($medias as $media) {
                        if ($media['product_id'] == $product['id'] && $media['is_main']) {
                            echo $media['media_url'];
                        }
                    } ?>" alt="image">
                </td>
                <td class="T_product textarea" data-colonne="name"><?php echo $product['name']; ?></td>
                <td class="T_product textarea" data-colonne="description"><?php echo $product['description']; ?></td>
                <td class="select T_categorie">
                    <?php
                    foreach ($product_categories as $category) {
                        if ($category['product_id'] == $product['id']) {
                            echo $category['name'] . '<br>';
                        }
                    }
                    ?>
                </td>
                <td class="T_product input" data-colonne="price"><?php echo $product['price']; ?></td>
                <td class="T_product input" data-colonne="stock"><?php echo $product['stock']; ?></td>
                <td class="textarea"><?php
                foreach ($medias as $media) {
                    if ($media['product_id'] == $product['id']) {
                        $url = str_replace('images/', '', $media['media_url']);
                        echo $url . '<br>';
                    }
                }
                ?></td>
                <td class="T_product input" data-colonne="min_order_quantity"><?php echo $product['min_order_quantity']; ?>
                </td>
                <td class="T_product input" data-colonne="is_on_promotion">
                    <?php echo $product['is_on_promotion'] == 1 ? 'Y' : 'N'; ?></td>
                <td class="T_product input" data-colonne="promotion_price"><?php echo $product['promotion_price']; ?></td>
                <td class="T_product input" data-colonne="views"><?php echo $product['views']; ?></td>
                <td><?php echo $product['created_at']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>