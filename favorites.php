<?php
// pages/favorites.php - Page des favoris
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'config/database.php';
include_once 'helpers/cart_functions.php';

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$favorite_products = getFavoriteProducts($user_id, $db);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris - E-commerce B2B</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Mes Favoris</h1>
            <div class="text-sm text-gray-600">
                <?php echo count($favorite_products); ?> produit(s) favori(s)
            </div>
        </div>

        <?php if (empty($favorite_products)): ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-heart text-6xl"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">Aucun favori pour le moment</h2>
                <p class="text-gray-500 mb-6">Ajoutez des produits à vos favoris pour les retrouver facilement</p>
                <a href="index.php#news"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-shopping-bag mr-2"></i>Découvrir nos produits
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($favorite_products as $product): ?>
                    <div
                        class="product-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <!-- Image du produit -->
                        <div class="relative">
                            <img src="<?php echo "images/" . $product['media_url']; ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">

                            <!-- Badge promotion -->
                            <?php if ($product['is_on_promotion']): ?>
                                <div
                                    class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                    PROMO
                                </div>
                            <?php endif; ?>

                            <!-- Bouton favori -->
                            <button
                                class="favorite-btn absolute top-2 right-2 p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition duration-300"
                                data-product-id="<?php echo $product['id']; ?>" title="Retirer des favoris">
                                <i class="fas fa-heart text-red-500"></i>
                            </button>
                        </div>

                        <!-- Contenu de la carte -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>

                            <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>

                            <!-- Prix -->
                            <div class="mb-4">
                                <?php if ($product['is_on_promotion']): ?>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-red-600">
                                            <?php echo number_format($product['promotion_price'], 2); ?>€
                                        </span>
                                        <span class="text-sm text-gray-500 line-through">
                                            <?php echo number_format($product['price'], 2); ?>€
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-gray-800">
                                        <?php echo number_format($product['price'], 2); ?>€
                                    </span>
                                <?php endif; ?>

                                <div class="text-xs text-gray-500 mt-1">
                                    Min. <?php echo $product['min_order_quantity']; ?> unité(s)
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="mb-4">
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="text-green-600 text-sm">
                                        <i class="fas fa-check-circle mr-1"></i>En stock (<?php echo $product['stock']; ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-red-600 text-sm">
                                        <i class="fas fa-times-circle mr-1"></i>Rupture de stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <?php if ($product['stock'] > 0): ?>
                                    <button
                                        class="add-to-cart-btn flex-grow bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300 text-sm font-semibold"
                                        data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-cart mr-1"></i>Ajouter
                                    </button>
                                <?php else: ?>
                                    <button
                                        class="flex-grow bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed text-sm font-semibold"
                                        disabled>
                                        <i class="fas fa-ban mr-1"></i>Indisponible
                                    </button>
                                <?php endif; ?>

                                <a href="product-detail.php?id=<?php echo $product['id']; ?>"
                                    class="bg-gray-200 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-300 transition duration-300">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/cart-favorites.js"></script>
</body>

</html>