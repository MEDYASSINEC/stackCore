<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration de la base de données
class Database
{
    private $host = 'sql203.infinityfree.com';
    private $db_name = 'if0_39394855_stackcore_db';
    private $username = 'if0_39394855';  // Ajustez selon votre configuration
    private $password = 'b1Zogl8tXJXx';      // Ajustez selon votre configuration
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            error_log("Erreur de connexion: " . $exception->getMessage());
        }
        return $this->conn;
    }
}


class ProductsAPI
{
    private $conn;
    private $table_name = "products";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Récupérer les produits en promotion
    public function getPromotionProducts($limit = 8)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN product_category pc ON p.id = pc.product_id
                  LEFT JOIN categories c ON pc.category_id = c.id
                  WHERE p.is_on_promotion = 1 AND p.stock > 0
                  ORDER BY p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les produits populaires (basé sur les vues)
    public function getPopularProducts($limit = 8)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN product_category pc ON p.id = pc.product_id
                  LEFT JOIN categories c ON pc.category_id = c.id
                  WHERE p.stock > 0
                  ORDER BY p.views DESC, p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les produits récents
    public function getRecentProducts($limit = 8)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN product_category pc ON p.id = pc.product_id
                  LEFT JOIN categories c ON pc.category_id = c.id
                  WHERE p.stock > 0
                  ORDER BY p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les produits avec filtres
    public function getAllProducts($filters = [])
    {
        $conditions = ["p.stock > 0"];
        $params = [];

        // Filtre par catégorie
        if (!empty($filters['category'])) {
            $conditions[] = "c.name LIKE :category";
            $params[':category'] = '%' . $filters['category'] . '%';
        }

        // Filtre par prix minimum
        if (!empty($filters['min_price'])) {
            $conditions[] = "(CASE WHEN p.is_on_promotion = 1 THEN p.promotion_price ELSE p.price END) >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        // Filtre par prix maximum
        if (!empty($filters['max_price'])) {
            $conditions[] = "(CASE WHEN p.is_on_promotion = 1 THEN p.promotion_price ELSE p.price END) <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        // Construction de la clause ORDER BY
        $orderBy = "p.created_at DESC";
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'price_asc':
                    $orderBy = "(CASE WHEN p.is_on_promotion = 1 THEN p.promotion_price ELSE p.price END) ASC";
                    break;
                case 'price_desc':
                    $orderBy = "(CASE WHEN p.is_on_promotion = 1 THEN p.promotion_price ELSE p.price END) DESC";
                    break;
                case 'popular':
                    $orderBy = "p.views DESC, p.created_at DESC";
                    break;
                case 'name':
                    $orderBy = "p.name ASC";
                    break;
                case 'recent':
                default:
                    $orderBy = "p.created_at DESC";
                    break;
            }
        }

        $whereClause = implode(" AND ", $conditions);

        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN product_category pc ON p.id = pc.product_id
                  LEFT JOIN categories c ON pc.category_id = c.id
                  WHERE " . $whereClause . "
                  ORDER BY " . $orderBy . "
                  LIMIT 50";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Recherche de produits
    public function searchProducts($query, $limit = 20)
    {
        $searchQuery = "SELECT p.*, c.name as category_name 
                        FROM " . $this->table_name . " p
                        LEFT JOIN product_category pc ON p.id = pc.product_id
                        LEFT JOIN categories c ON pc.category_id = c.id
                        WHERE p.stock > 0 
                        AND (p.name LIKE :query OR p.description LIKE :query OR c.name LIKE :query)
                        ORDER BY 
                            CASE 
                                WHEN p.name LIKE :exact_query THEN 1
                                WHEN p.name LIKE :start_query THEN 2
                                WHEN p.description LIKE :start_query THEN 3
                                ELSE 4
                            END,
                            p.views DESC
                        LIMIT :limit";

        $stmt = $this->conn->prepare($searchQuery);

        $searchTerm = '%' . $query . '%';
        $exactTerm = $query;
        $startTerm = $query . '%';

        $stmt->bindParam(':query', $searchTerm);
        $stmt->bindParam(':exact_query', $exactTerm);
        $stmt->bindParam(':start_query', $startTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour le nombre de vues d'un produit
    public function incrementViews($productId)
    {
        $query = "UPDATE " . $this->table_name . " SET views = views + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $productId);
        return $stmt->execute();
    }

    // Récupérer les catégories
    public function getCategories()
    {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialisation des données de test (à exécuter une seule fois)
function initializeTestData($conn)
{
    // Vérifier si les données existent déjà
    $checkQuery = "SELECT COUNT(*) as count FROM products";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        return; // Les données existent déjà
    }

    // Insérer les catégories
    $categories = [
        'Ordinateurs et Unités Centrales',
        'Écrans et Affichage',
        'Audio et Vidéo',
        'Périphériques',
        'Réseau et Connectivité',
        'Instruments d\'Écriture',
        'Papeterie',
        'Classement et Archivage',
        'Agrafage et Fixation',
        'Présentations et Affichage',
        'Courrier et Expédition',
        'Accessoires de Bureau',
        'Mobilier et Ergonomie',
        'Nettoyage et Entretien'
    ];

    foreach ($categories as $category) {
        $query = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $category);
        $stmt->execute();
    }
}
// Traitement des requêtes
try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    // Initialiser les données de test si nécessaire
    initializeTestData($db);

    $productsAPI = new ProductsAPI($db);

    $type = $_GET['type'] ?? '';
    $response = ['success' => false, 'products' => []];

    switch ($type) {
        case 'promotion':
            $products = $productsAPI->getPromotionProducts();
            $response = ['success' => true, 'products' => $products];
            break;

        case 'popular':
            $products = $productsAPI->getPopularProducts();
            $response = ['success' => true, 'products' => $products];
            break;

        case 'recent':
            $products = $productsAPI->getRecentProducts();
            $response = ['success' => true, 'products' => $products];
            break;

        case 'all':
            $products = $productsAPI->getAllProducts();
            $response = ['success' => true, 'products' => $products];
            break;

        case 'search':
            $query = $_GET['query'] ?? '';
            if (!empty($query)) {
                $products = $productsAPI->searchProducts($query);
                $response = ['success' => true, 'products' => $products];
            }
            break;

        case 'filter':
            $filters = [
                'category' => $_GET['category'] ?? '',
                'min_price' => $_GET['min_price'] ?? '',
                'max_price' => $_GET['max_price'] ?? '',
                'sort_by' => $_GET['sort_by'] ?? ''
            ];
            $products = $productsAPI->getAllProducts($filters);
            $response = ['success' => true, 'products' => $products];
            break;

        case 'categories':
            $categories = $productsAPI->getCategories();
            $response = ['success' => true, 'categories' => $categories];
            break;

        default:
            $response = ['success' => false, 'message' => 'Type de requête non valide'];
            break;
    }

} catch (Exception $e) {
    error_log("Erreur API: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>