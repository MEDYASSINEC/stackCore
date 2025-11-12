<?php
// ajax/clear_cart.php - Vider le panier


// ajax/get_cart_count.php - Obtenir le nombre d'articles dans le panier

// pages/cart.php - Page du panier
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
$cart_items = getCartItems($user_id, $db);
$cart_total = getCartTotal($user_id, $db);
$cart_count = getCartCount($user_id, $db);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - E-commerce B2B</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Mon Panier</h1>
            <div class="text-sm text-gray-600">
                <span class="cart-counter-text"><?php echo $cart_count; ?></span> article(s)
            </div>
        </div>

        <?php if (empty($cart_items)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-shopping-cart text-6xl"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-600 mb-2">Votre panier est vide</h2>
            <p class="text-gray-500 mb-6">Découvrez nos produits et ajoutez-les à votre panier</p>
            <a href="index.php"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                <i class="fas fa-shopping-bag mr-2"></i>Voir nos produits
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Liste des articles -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold">Articles dans votre panier</h2>
                        <button class="clear-cart text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash mr-1"></i>Vider le panier
                        </button>
                    </div>

                    <div class="cart-items-container">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item border-b border-gray-100 p-6 flex items-center space-x-4">
                            <!-- Image du produit -->
                            <div class="flex-shrink-0">
                                <img src="<?php echo $item['media_url'] ?: 'https://via.placeholder.com/80x80'; ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    class="w-20 h-20 object-cover rounded-lg">
                            </div>

                            <!-- Détails du produit -->
                            <div class="flex-grow">
                                <h3 class="font-semibold text-gray-800 mb-1">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </h3>
                                <p class="text-gray-600 text-sm mb-2">
                                    Prix unitaire:
                                    <?php if ($item['is_on_promotion']): ?>
                                    <span
                                        class="line-through text-gray-400"><?php echo number_format($item['price'], 2); ?>€</span>
                                    <span
                                        class="text-red-600 font-semibold"><?php echo number_format($item['promotion_price'], 2); ?>€</span>
                                    <?php else: ?>
                                    <span class="font-semibold"><?php echo number_format($item['price'], 2); ?>€</span>
                                    <?php endif; ?>
                                </p>

                                <!-- Contrôles de quantité -->
                                <div class="flex items-center space-x-3">
                                    <label class="text-sm text-gray-600">Quantité:</label>
                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button
                                            class="quantity-decrease px-3 py-1 text-gray-600 hover:bg-gray-100">-</button>
                                        <input type="number"
                                            class="quantity-update quantity-input w-16 text-center py-1 border-none focus:outline-none"
                                            value="<?php echo $item['quantity']; ?>"
                                            data-cart-item-id="<?php echo $item['id']; ?>" min="1" max="999">
                                        <button
                                            class="quantity-increase px-3 py-1 text-gray-600 hover:bg-gray-100">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Prix total et actions -->
                            <div class="flex-shrink-0 text-right">
                                <div class="font-semibold text-lg text-gray-800 mb-2">
                                    <?php
                                            $item_price = $item['is_on_promotion'] ? $item['promotion_price'] : $item['price'];
                                            $item_total = $item_price * $item['quantity'];
                                            echo number_format($item_total, 2) . '€';
                                            ?>
                                </div>
                                <button class="remove-from-cart text-red-600 hover:text-red-800 text-sm"
                                    data-cart-item-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Résumé de la commande -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-lg font-semibold mb-4">Résumé de la commande</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Sous-total</span>
                            <span class="cart-subtotal"><?php echo number_format($cart_total, 2); ?>€</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Livraison</span>
                            <span>Calculée à l'étape suivante</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span class="cart-total"><?php echo number_format($cart_total, 2); ?>€</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="checkout.php"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300 block text-center font-semibold">
                            <i class="fas fa-credit-card mr-2"></i>Procéder au paiement
                        </a>
                        <a href="index.php"
                            class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition duration-300 block text-center">
                            <i class="fas fa-arrow-left mr-2"></i>Continuer les achats
                        </a>
                    </div>

                    <!-- Code promo -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="font-semibold mb-3">Code promo</h3>
                        <div class="flex space-x-2">
                            <input type="text" id="promo-code" placeholder="Entrez votre code"
                                class="flex-grow px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button id="apply-promo"
                                class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition duration-300">
                                Appliquer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="js/cart-favorites.js"></script>
</body>

</html>