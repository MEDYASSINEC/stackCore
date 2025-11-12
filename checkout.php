<?php
// pages/checkout.php - Page de finalisation de commande
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'config/db.php';
include_once 'helpers/cart_functions.php';

// Utilisation de votre connexion PDO
$db = $pdo;

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id, $db);
$cart_total = getCartTotal($user_id, $db);
$cart_count = getCartCount($user_id, $db);

// Redirection si le panier est vide
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Récupérer les informations utilisateur
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire de commande
if ($_POST && isset($_POST['place_order'])) {
    try {
        $db->beginTransaction();

        // Récupération des données du formulaire
        $delivery_address = $_POST['delivery_address'];
        $payment_method = $_POST['payment_method'];
        $notes = $_POST['notes'] ?? '';
        $discount_code = $_POST['discount_code'] ?? '';

        $final_total = $cart_total;
        $discount_code_id = null;

        // Vérification du code promo si fourni
        if (!empty($discount_code)) {
            $promo_query = "SELECT * FROM discount_codes 
                           WHERE code = ? 
                           AND valid_from <= CURDATE() 
                           AND valid_until >= CURDATE() 
                           AND (usage_limit IS NULL OR used_count < usage_limit)
                           AND (min_order_amount IS NULL OR ? >= min_order_amount)
                           AND (user_id IS NULL OR user_id = ?)";
            $promo_stmt = $db->prepare($promo_query);
            $promo_stmt->execute([$discount_code, $cart_total, $user_id]);
            $promo = $promo_stmt->fetch(PDO::FETCH_ASSOC);

            if ($promo) {
                $discount_amount = ($cart_total * $promo['discount_percent']) / 100;
                $final_total = $cart_total - $discount_amount;
                $discount_code_id = $promo['id'];

                // Mettre à jour le compteur d'utilisation
                $update_promo = "UPDATE discount_codes SET used_count = used_count + 1 WHERE id = ?";
                $update_stmt = $db->prepare($update_promo);
                $update_stmt->execute([$promo['id']]);
            }
        }

        // Création de la commande
        $order_query = "INSERT INTO orders (user_id, total_amount, delivery_address, discount_code_id, status) 
                       VALUES (?, ?, ?, ?, 'confirmée')";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->execute([$user_id, $final_total, $delivery_address, $discount_code_id]);
        $order_id = $db->lastInsertId();

        // Génération du numéro de suivi
        $tracking_number = 'TRK' . str_pad($order_id, 8, '0', STR_PAD_LEFT);
        $estimated_delivery = date('Y-m-d', strtotime('+5 days'));

        // Mise à jour avec les informations de livraison
        $update_order = "UPDATE orders SET tracking_number = ?, estimated_delivery_date = ? WHERE id = ?";
        $update_stmt = $db->prepare($update_order);
        $update_stmt->execute([$tracking_number, $estimated_delivery, $order_id]);

        // Ajout des articles de commande
        foreach ($cart_items as $item) {
            $price = $item['is_on_promotion'] ? $item['promotion_price'] : $item['price'];

            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
                          VALUES (?, ?, ?, ?)";
            $item_stmt = $db->prepare($item_query);
            $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $price]);

            // Mise à jour du stock
            $stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stock_stmt = $db->prepare($stock_query);
            $stock_stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // Génération de la facture
        $invoice_number = 'FAC' . date('Y') . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        $tva_percent = 20.00;
        $total_ht = $final_total / (1 + $tva_percent / 100);
        $total_ttc = $final_total;

        $invoice_query = "INSERT INTO invoices (order_id, invoice_number, tva_percent, total_ht, total_ttc) 
                         VALUES (?, ?, ?, ?, ?)";
        $invoice_stmt = $db->prepare($invoice_query);
        $invoice_stmt->execute([$order_id, $invoice_number, $tva_percent, $total_ht, $total_ttc]);

        // Vider le panier
        $clear_cart = "DELETE FROM cart_items WHERE user_id = ?";
        $clear_stmt = $db->prepare($clear_cart);
        $clear_stmt->execute([$user_id]);

        // Notification de confirmation
        $notification = "Votre commande #$order_id a été confirmée. Numéro de suivi: $tracking_number";
        $notif_query = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
        $notif_stmt = $db->prepare($notif_query);
        $notif_stmt->execute([$user_id, $notification]);

        $db->commit();

        // Redirection vers la page de confirmation
        header("Location: order_confirmation.php?order_id=$order_id");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error_message = "Erreur lors de la création de la commande: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser la commande - E-commerce B2B</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Finaliser votre commande</h1>
            <div class="flex items-center mt-4 text-sm text-gray-600">
                <a href="cart.php" class="hover:text-blue-600">Panier</a>
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="text-blue-600 font-semibold">Commande</span>
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="text-gray-400">Confirmation</span>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Informations de livraison -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Adresse de livraison -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-truck mr-3 text-blue-600"></i>
                        Adresse de livraison
                    </h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Entreprise
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($user['company_name']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse de livraison complète *
                        </label>
                        <textarea name="delivery_address" rows="4" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Adresse complète avec code postal et ville"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email de contact
                            </label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Téléphone
                            </label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                        </div>
                    </div>
                </div>

                <!-- Mode de paiement -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-credit-card mr-3 text-blue-600"></i>
                        Mode de paiement
                    </h2>

                    <div class="space-y-3">
                        <label
                            class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="virement" class="mr-3" checked>
                            <div class="flex-grow">
                                <div class="font-medium">Virement bancaire</div>
                                <div class="text-sm text-gray-600">Paiement par virement - Facture envoyée par email
                                </div>
                            </div>
                            <i class="fas fa-university text-green-600"></i>
                        </label>

                        <label
                            class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="cheque" class="mr-3">
                            <div class="flex-grow">
                                <div class="font-medium">Chèque</div>
                                <div class="text-sm text-gray-600">Paiement par chèque à réception</div>
                            </div>
                            <i class="fas fa-money-check text-blue-600"></i>
                        </label>

                        <label
                            class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="30jours" class="mr-3">
                            <div class="flex-grow">
                                <div class="font-medium">Paiement à 30 jours</div>
                                <div class="text-sm text-gray-600">Pour les clients établis</div>
                            </div>
                            <i class="fas fa-calendar-alt text-orange-600"></i>
                        </label>
                    </div>
                </div>

                <!-- Code promo -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-tag mr-3 text-blue-600"></i>
                        Code promo
                    </h2>

                    <div class="flex space-x-3">
                        <input type="text" name="discount_code" id="discount_code" placeholder="Entrez votre code promo"
                            class="flex-grow px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" id="apply_discount"
                            class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition duration-300">
                            Appliquer
                        </button>
                    </div>
                    <div id="discount_message" class="mt-2 text-sm"></div>
                </div>

                <!-- Notes de commande -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-sticky-note mr-3 text-blue-600"></i>
                        Notes de commande (optionnel)
                    </h2>

                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Instructions spéciales pour la livraison..."></textarea>
                </div>
            </div>

            <!-- Résumé de commande -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-semibold mb-4">Résumé de commande</h2>

                    <!-- Articles -->
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="flex items-center space-x-3 pb-3 border-b border-gray-100">
                            <img src="<?php echo $item['media_url'] ?: 'https://via.placeholder.com/50x50'; ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                class="w-12 h-12 object-cover rounded">
                            <div class="flex-grow">
                                <div class="font-medium text-sm"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="text-xs text-gray-600">Qté: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="text-sm font-semibold">
                                <?php
                                    $item_price = $item['is_on_promotion'] ? $item['promotion_price'] : $item['price'];
                                    echo number_format($item_price * $item['quantity'], 2) . '€';
                                    ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Totaux -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Sous-total</span>
                            <span id="subtotal"><?php echo number_format($cart_total, 2); ?>€</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>TVA (20%)</span>
                            <span id="tva"><?php echo number_format($cart_total * 0.2 / 1.2, 2); ?>€</span>
                        </div>
                        <div id="discount_line" class="flex justify-between text-green-600 hidden">
                            <span>Remise</span>
                            <span id="discount_amount">-0.00€</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Frais de port</span>
                            <span>Offerts</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between text-xl font-bold">
                                <span>Total TTC</span>
                                <span id="final_total"><?php echo number_format($cart_total, 2); ?>€</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de commande -->
                    <button type="submit" name="place_order"
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition duration-300 font-semibold">
                        <i class="fas fa-check mr-2"></i>Confirmer la commande
                    </button>

                    <div class="mt-4 text-xs text-gray-500 text-center">
                        En confirmant votre commande, vous acceptez nos conditions générales de vente
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
    $(document).ready(function() {
        // Application du code promo
        $('#apply_discount').click(function() {
            const code = $('#discount_code').val().trim();
            const subtotal = <?php echo $cart_total; ?>;

            if (!code) {
                $('#discount_message').html(
                    '<span class="text-red-600">Veuillez entrer un code promo</span>');
                return;
            }

            $.ajax({
                url: 'ajax/apply_discount.php',
                method: 'POST',
                data: {
                    discount_code: code,
                    order_total: subtotal
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const discountAmount = (subtotal * response.discount_percent) / 100;
                        const newTotal = subtotal - discountAmount;

                        $('#discount_line').removeClass('hidden');
                        $('#discount_amount').text('-' + discountAmount.toFixed(2) + '€');
                        $('#final_total').text(newTotal.toFixed(2) + '€');
                        $('#discount_message').html(
                            '<span class="text-green-600">Code promo appliqué: -' +
                            response.discount_percent + '%</span>');
                    } else {
                        $('#discount_message').html('<span class="text-red-600">' + response
                            .message + '</span>');
                        $('#discount_line').addClass('hidden');
                        $('#final_total').text(subtotal.toFixed(2) + '€');
                    }
                },
                error: function() {
                    $('#discount_message').html(
                        '<span class="text-red-600">Erreur lors de la vérification du code</span>'
                    );
                }
            });
        });
    });
    </script>
</body>

</html>