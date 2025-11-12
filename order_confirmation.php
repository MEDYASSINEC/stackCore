<?php
// pages/order_confirmation.php - Page de confirmation de commande
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

include_once 'config/db.php';

// Utilisation de votre connexion PDO
$db = $pdo;

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

// Récupérer les détails de la commande
$query = "SELECT o.*, u.company_name, u.email, i.invoice_number, i.total_ht, i.total_ttc
          FROM orders o
          JOIN users u ON o.user_id = u.id
          LEFT JOIN invoices i ON o.id = i.order_id
          WHERE o.id = ? AND o.user_id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Récupérer les articles de la commande
$items_query = "SELECT oi.*, p.name, p.is_on_promotion, pm.media_url
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_main = 1
                WHERE oi.order_id = ?
                ORDER BY oi.id";

$items_stmt = $db->prepare($items_query);
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le code promo utilisé si applicable
$discount_info = null;
if ($order['discount_code_id']) {
    $discount_query = "SELECT code, discount_percent FROM discount_codes WHERE id = ?";
    $discount_stmt = $db->prepare($discount_query);
    $discount_stmt->execute([$order['discount_code_id']]);
    $discount_info = $discount_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande confirmée - E-commerce B2B</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header de confirmation -->
        <div class="bg-green-100 border border-green-400 rounded-lg p-6 mb-8 text-center">
            <div class="text-green-600 mb-4">
                <i class="fas fa-check-circle text-6xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-green-800 mb-2">Commande confirmée !</h1>
            <p class="text-green-700">Merci pour votre commande. Nous la traitons actuellement.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Détails de la commande -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-receipt mr-3 text-blue-600"></i>
                    Détails de la commande
                </h2>

                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="font-medium">Numéro de commande:</span>
                        <span class="text-blue-600 font-mono">#<?php echo $order['id']; ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="font-medium">Date de commande:</span>
                        <span><?php echo date('d/m/Y à H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="font-medium">Statut:</span>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <?php if ($order['tracking_number']): ?>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium">Numéro de suivi:</span>
                            <span class="font-mono text-blue-600"><?php echo $order['tracking_number']; ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['estimated_delivery_date']): ?>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium">Livraison estimée:</span>
                            <span><?php echo date('d/m/Y', strtotime($order['estimated_delivery_date'])); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['invoice_number']): ?>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium">Numéro de facture:</span>
                            <span class="font-mono"><?php echo $order['invoice_number']; ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between py-2 font-semibold text-lg">
                        <span>Total TTC:</span>
                        <span class="text-green-600"><?php echo number_format($order['total_amount'], 2); ?>€</span>
                    </div>
                </div>
            </div>

            <!-- Adresse de livraison -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt mr-3 text-blue-600"></i>
                    Adresse de livraison
                </h2>

                <div class="space-y-2">
                    <div class="font-medium"><?php echo htmlspecialchars($order['company_name']); ?></div>
                    <div class="text-gray-600"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></div>
                    <div class="text-gray-600"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
            </div>
        </div>

        <!-- Articles commandés -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-box mr-3 text-blue-600"></i>
                Articles commandés
            </h2>

            <div class="space-y-4">
                <?php foreach ($order_items as $item): ?>
                    <div class="border border-gray-200 rounded-lg p-4 flex items-center space-x-4">
                        <img src="<?php echo $item['media_url'] ?: 'https://via.placeholder.com/80x80'; ?>"
                            alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover rounded-lg">

                        <div class="flex-grow">
                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="text-sm text-gray-600">
                                Quantité: <?php echo $item['quantity']; ?> ×
                                <?php echo number_format($item['price_at_purchase'], 2); ?>€
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="font-semibold text-lg">
                                <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?>€
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Résumé financier -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="max-w-sm ml-auto">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Total HT:</span>
                            <span><?php echo number_format($order['total_ht'], 2); ?>€</span>
                        </div>
                        <div class="flex justify-between">
                            <span>TVA (20%):</span>
                            <span><?php echo number_format($order['total_ttc'] - $order['total_ht'], 2); ?>€</span>
                        </div>
                        <?php if ($discount_info): ?>
                            <div class="flex justify-between text-green-600">
                                <span>Remise (<?php echo $discount_info['code']; ?>):</span>
                                <span>-<?php echo $discount_info['discount_percent']; ?>%</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between font-semibold text-lg pt-2 border-t">
                            <span>Total TTC:</span>
                            <span><?php echo number_format($order['total_amount'], 2); ?>€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-xl font-semibold mb-4">Prochaines étapes</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-envelope text-blue-600 mr-2"></i>
                        <span class="font-medium">Confirmation par email</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        Un email de confirmation avec les détails de votre commande a été envoyé à votre adresse email.
                    </p>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-file-invoice text-green-600 mr-2"></i>
                        <span class="font-medium">Facture</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        Votre facture sera générée et envoyée une fois votre commande expédiée.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mt-6">
                <a href="orders.php"
                    class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-300 text-center">
                    <i class="fas fa-list mr-2"></i>Voir mes commandes
                </a>
                <a href="index.php"
                    class="flex-1 bg-gray-100 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-200 transition duration-300 text-center">
                    <i class="fas fa-shopping-bag mr-2"></i>Continuer les achats
                </a>
                <button onclick="window.print()"
                    class="flex-1 bg-gray-800 text-white py-3 px-6 rounded-lg hover:bg-gray-900 transition duration-300">
                    <i class="fas fa-print mr-2"></i>Imprimer
                </button>
            </div>
        </div>

        <!-- Informations de contact -->
        <div class="bg-gray-100 rounded-lg p-6 mt-8 text-center">
            <h3 class="font-semibold mb-2">Une question sur votre commande ?</h3>
            <p class="text-gray-600 mb-4">
                Notre équipe est à votre disposition pour vous aider.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="contact.php"
                    class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-phone mr-2"></i>Nous contacter
                </a>
                <a href="support.php"
                    class="bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-300">
                    <i class="fas fa-headset mr-2"></i>Support client
                </a>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body {
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .bg-white {
                box-shadow: none !important;
            }
        }
    </style>
</body>

</html>