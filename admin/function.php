<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "if0_39394855_stackcore_db";
$conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);

require_once '../config/db.php';

function modifierTProd($id, $colonne, $value)
{

    try {
        global $pdo;
        // require_once '../config/db.php';
        // Vérifier que la colonne est autorisée
        $colonnes_autorisees = ['name', 'description', 'price', 'stock', 'min_order_quantity', 'is_on_promotion', 'promotion_price', 'views'];
        if (!in_array($colonne, $colonnes_autorisees)) {
            throw new Exception("Colonne non autorisée");
        }

        if ($colonne === 'is_on_promotion') {
            $value = ($value == 'Y') ? 1 : 0;
        }


        $requete = "UPDATE products SET {$colonne} = ? WHERE id = ?";
        $stmt = $pdo->prepare($requete);
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
        global $pdo;
        // require_once '../config/db.php';


        $requete = "UPDATE product_category SET category_id = (SELECT id FROM categories WHERE name=?) WHERE product_id = ?";
        $stmt = $pdo->prepare($requete);
        $stmt->bindParam(1, $value);
        $stmt->bindParam(2, $id);

        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    }
}

function televiserMedia($idProduit)
{
    $uploadDir = 'images/';
    $tailleMaxForImg = 2 * 1024 * 1024; // 2 Mo
    $tailleMaxForVideo = 10 * 1024 * 1024; // 10 Mo
    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'mp4'];

    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // var_dump($_FILES['medias']);
    if (!empty($_FILES['medias']['name'][0])) {

        $nbFichiers = count($_FILES['medias']['name']);

        for ($i = 0; $i < $nbFichiers; $i++) {

            $tmpName = $_FILES['medias']['tmp_name'][$i];
            $name = $_FILES['medias']['name'][$i];
            $size = $_FILES['medias']['size'][$i];
            $error = $_FILES['medias']['error'][$i];

            if ($error === 0) {
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (!in_array($extension, $extensionsAutorisees)) {
                    throw new Exception("❌ Le fichier <strong>$name</strong> a une extension non autorisée.<br>");
                }
                $type = '';
                if ($extension === 'mp4') {
                    if ($size > $tailleMaxForVideo) {
                        $type = 'video';
                        throw new Exception("❌ Le fichier <strong>$name</strong> dépasse la taille autorisée (2 Mo).<br>");
                    }
                    $nouveauNom = uniqid("video_", true) . '.' . $extension;
                } else {
                    if ($size > $tailleMaxForImg) {
                        $type = 'image';
                        throw new Exception("❌ Le fichier <strong>$name</strong> dépasse la taille autorisée (2 Mo).<br>");
                    }
                    $nouveauNom = uniqid("img_", true) . '.' . $extension;
                }
                $chemin = $uploadDir . $nouveauNom;
                $isMain = ($i === 0) ? 1 : 0;

                if (move_uploaded_file($tmpName, $chemin)) {
                    $stmt = $GLOBALS['conn']->prepare("INSERT INTO product_media (product_id, media_url,media_type, is_main) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$idProduit, $chemin, $type, $isMain]);
                    continue;
                } else {
                    throw new Exception("❌ Erreur lors de l'envoi du fichier <strong>$name</strong>.<br>");
                }
            } else {
                throw new Exception("⚠️ Une erreur est survenue avec le fichier <strong>$name</strong>.<br>");
            }
        }

    }
}

function ajouterTProd($nom, $description, $prix, $stock, $quantite_min, $promotion, $prix_promo, $views, $categories)
{
    try {
        // Vérifier que tous les champs obligatoires sont remplis
        if (empty($nom) || empty($description) || empty($prix) || empty($stock) || empty($quantite_min) || empty($categories)) {
            throw new Exception("Tous les champs obligatoires doivent être remplis");
        }

        // Convertir les valeurs numériques
        $prix = floatval($prix);
        $stock = intval($stock);
        $quantite_min = intval($quantite_min);
        $promotion = intval($promotion);
        $views = intval($views);
        if ($promotion === 1 && empty($prix_promo)) {
            throw new Exception("Le prix promotionnel est requis si le produit est en promotion");
        }
        if (!empty($prix_promo)) {
            $prix_promo = floatval($prix_promo);
        }

        // Préparer la requête d'insertion du produit
        $requete = "INSERT INTO products (name, description, price, stock, min_order_quantity, is_on_promotion, promotion_price, views) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $GLOBALS['conn']->prepare($requete);
        $stmt->bindParam(1, $nom);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $prix);
        $stmt->bindParam(4, $stock);
        $stmt->bindParam(5, $quantite_min);
        $stmt->bindParam(6, $promotion);
        $stmt->bindParam(7, $prix_promo);
        $stmt->bindParam(8, $views);

        $stmt->execute();

        // Récupérer l'ID du produit nouvellement créé
        $idProduit = $GLOBALS['conn']->lastInsertId();

        // Insérer les catégories
        if (!empty($categories)) {
            $categoriesArray = explode(',', $categories);
            foreach ($categoriesArray as $categorie) {
                $categorie = trim($categorie);
                if (!empty($categorie)) {
                    // Vérifier si la catégorie existe déjà
                    $stmt = $GLOBALS['conn']->prepare("SELECT id FROM categories WHERE name = ?");
                    $stmt->execute([$categorie]);
                    $category = $stmt->fetch();

                    if ($category) {
                        // Si la catégorie existe, utiliser son ID
                        $categoryId = $category['id'];
                    }

                    // Ajouter la relation produit-catégorie
                    $stmt = $GLOBALS['conn']->prepare("INSERT INTO product_category (product_id, category_id) VALUES (?, ?)");
                    $stmt->execute([$idProduit, $categoryId]);
                }
            }
        }

        // insérer les media au dossiers images
        televiserMedia($idProduit);

        return true;
    } catch (PDOException $e) {
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    } catch (Exception $e) {
        throw $e;
    }
}

function modifierTOrder($id, $colonne, $value)
{
    global $pdo;

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Mettre à jour la commande
        $stmt = $pdo->prepare("UPDATE orders SET {$colonne} = ? WHERE id = ?");
        $stmt->execute([$value, $id]);

        // Valider la transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    }
}
// Fonction pour supprimer une commande
function supprimerTOrder($id)
{
    global $pdo;

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // 1. Supprimer les détails de commande (order_items)
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$id]);

        // 2. Supprimer la facture (invoices)
        $stmt = $pdo->prepare("DELETE FROM invoices WHERE order_id = ?");
        $stmt->execute([$id]);

        // 3. Supprimer la commande (orders)
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);

        // Valider la transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        throw new Exception("Erreur de base de données: " . $e->getMessage());
    }
}

// Fonction pour supprimer un produit
function supprimerTProd($id)
{
    global $pdo;

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Supprimer les médias associés
        $stmt = $pdo->prepare("DELETE FROM product_media WHERE product_id = ?");
        $stmt->execute([$id]);

        // Supprimer les associations catégorie-produit
        $stmt = $pdo->prepare("DELETE FROM product_category WHERE product_id = ?");
        $stmt->execute([$id]);

        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        // Valider la transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        return false;
    }
}

// Fonction pour supprimer un client
function supprimerClient($id)
{
    global $pdo;

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Supprimer les réponses du support (facultatif si pas de FK forte)
        $stmt = $pdo->prepare("DELETE FROM ticket_responses WHERE sender_type = 'client' AND sender_id = ?");
        $stmt->execute([$id]);

        // Supprimer les tickets du support
        $stmt = $pdo->prepare("DELETE FROM support_tickets WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les notifications
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les vues de produits
        $stmt = $pdo->prepare("DELETE FROM product_views WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les questions sur les produits
        $stmt = $pdo->prepare("DELETE FROM product_questions WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les avis produits
        $stmt = $pdo->prepare("DELETE FROM product_reviews WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les favoris
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les articles du panier
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les connexions (logs)
        $stmt = $pdo->prepare("DELETE FROM user_logins WHERE user_id = ?");
        $stmt->execute([$id]);

        // Supprimer les codes promos liés à cet utilisateur
        $stmt = $pdo->prepare("DELETE FROM discount_codes WHERE user_id = ?");
        $stmt->execute([$id]);

        // Récupérer les commandes de l'utilisateur
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ?");
        $stmt->execute([$id]);
        $orderIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($orderIds)) {
            // Supprimer les factures liées aux commandes
            $in = str_repeat('?,', count($orderIds) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM invoices WHERE order_id IN ($in)");
            $stmt->execute($orderIds);

            // Supprimer les items de commandes
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($in)");
            $stmt->execute($orderIds);
        }

        // Supprimer les commandes
        $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->execute([$id]);

        // Enfin : supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        // Valider la transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    $table = trim($_POST['table'] ?? '');
    $id = trim($_POST['id'] ?? '');

    // Gestion de la suppression
    if ($action === 'delete' && !empty($id)) {
        $result = false;

        // Supprimer un client
        if ($table === 'customers') {
            $result = supprimerClient($id);
        }
        // Supprimer un produit
        else if ($table === 'products') {
            $result = supprimerTProd($id);
        }
        // Supprimer une commande
        else if ($table === 'orders') {
            $result = supprimerTOrder($id);
        }

        echo json_encode(['success' => $result]);
        exit;
    }

    // Gestion de l'affichage des détails de la commande
    if ($action === 'view_order' && !empty($id)) {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    o.id AS order_id,
        o.order_date,
        o.status,
        o.delivery_address,
        o.tracking_number,
        o.delivery_status,
        o.estimated_delivery_date,
        o.total_amount,
        o.remarque,
                    u.company_name,
                    u.email,
                    u.phone,
                    u.address,
                    SUM(oi.quantity * oi.price_at_purchase) as total_amount
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.id = ?
                GROUP BY o.id
            ");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                $stmt = $pdo->prepare("
                    SELECT 
                        p.name as product_name,
                        oi.quantity,
                        oi.price_at_purchase,
                        oi.quantity * oi.price_at_purchase as subtotal
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $output = "";
                $output .= "<div class='order-details'>";
                $output .= "<p><strong>Order ID:</strong> " . $order['order_id'] . "</p>";
                $output .= "<p><strong>Customer:</strong> " . htmlspecialchars($order['company_name']) . "</p>";
                $output .= "<p><strong>Email:</strong> " . htmlspecialchars($order['email']) . "</p>";
                $output .= "<p><strong>Phone:</strong> " . htmlspecialchars($order['phone']) . "</p>";
                $output .= "<p><strong>Address:</strong> " . htmlspecialchars($order['address']) . "</p>";
                $output .= "<p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>";
                $output .= "<p><strong>Total Amount:</strong> $" . number_format($order['total_amount'], 2) . "</p>";
                $output .= "<p><strong>Created At:</strong> " . date('Y-m-d H:i', strtotime($order['order_date'])) . "</p>";
                $output .= "<p><strong>Delivery Status:</strong> " . ucfirst($order['delivery_status']) . "</p>";
                $output .= "<p><strong>Delivery Address:</strong> " . htmlspecialchars($order['delivery_address']) . "</p>";
                $output .= "<p><strong>Tracking Number:</strong> " . htmlspecialchars($order['tracking_number']) . "</p>";
                $output .= "<p><strong>Estimated Delivery Date:</strong> " . date('Y-m-d H:i', strtotime($order['estimated_delivery_date'])) . "</p>";
                $output .= "<p><strong>Remarque:</strong> " . htmlspecialchars($order['remarque']) . "</p>";
                $output .= "<h4>Order Items</h4>";
                $output .= "<table class='order-items'>";
                $output .= "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>";

                foreach ($items as $item) {
                    $output .= "<tr>";
                    $output .= "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                    $output .= "<td>x" . $item['quantity'] . "</td>";
                    $output .= "<td>$" . number_format($item['price_at_purchase'], 2) . "</td>";
                    $output .= "<td>$" . number_format($item['subtotal'], 2) . "</td>";
                    $output .= "</tr>";
                }

                $output .= "</table>";

                $output .= "</div>";

                echo $output;
            } else {
                echo "<p>Order not found</p>";
            }
            exit;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }

    $colonne = trim($_POST['colonne'] ?? '');
    $value = trim($_POST['value'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    // {{ ... }}
    $stock = trim($_POST['stock'] ?? '');
    $quantite_min = trim($_POST['quantite_min'] ?? '');
    $is_on_promotion = trim($_POST['promotion'] ?? '');
    $promotion_price = ($is_on_promotion == 1) ? trim($_POST['prix_promo']) : null;
    $views = trim($_POST['views'] ?? 0);
    $categories = $_POST['categories'] ?? [];
    $categories = implode(',', $categories);

    try {
        if ($action === 'modifier' && $table === 'product' && !empty($id) && !empty($colonne) && !empty($value)) {
            if (modifierTProd($id, $colonne, $value)) {
                echo json_encode(['success' => true, 'message' => 'Modification réussie']);
            }
        } elseif ($action === 'modifier' && $table === 'product_category' && !empty($id) && !empty($value)) {
            if (modifierTProdC($id, $value)) {
                echo json_encode(['success' => true, 'message' => 'Modification réussie']);
            }
        } elseif ($action === 'ajouter' && !empty($nom) && !empty($description) && !empty($prix) && !empty($stock) && !empty($quantite_min) && !empty($categories)) {
            if (ajouterTProd($nom, $description, $prix, $stock, $quantite_min, $is_on_promotion, $promotion_price, $views, $categories)) {
                echo json_encode(['success' => true, 'message' => 'Ajout réussie']);
            }
        } elseif ($action === 'modifier_order' && $table === 'orders' && !empty($id) && !empty($colonne) && !empty($value)) {
            if (modifierTOrder($id, $colonne, $value)) {
                echo json_encode(['success' => true, 'message' => 'Modification réussie']);
            }
        } else {
            throw new Exception("Paramètres invalides");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'http_response_code' => '500']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée', 'http_response_code' => '405']);
}

?>