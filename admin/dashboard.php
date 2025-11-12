<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index_admin.php");
    exit();
}

require_once '../config/db.php';

try {
    // Get statistics
    $stats = [];

    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Categories
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
    $stats['categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Orders and Monthly Revenue
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders");
    $orders = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['orders'] = $orders['total'];
    $stats['revenue'] = $orders['revenue'];

    // Calculate percentage of unfinished orders
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_unfinished
        FROM orders 
        WHERE status IN ('en attente', 'confirmée', 'payée', 'en cours')
    ");
    $stmt->execute();
    $unfinished_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_unfinished'];

    if ($stats['orders'] > 0) {
        $stats['unfinished_percentage'] = round(($unfinished_orders / $stats['orders']) * 100, 1);
    } else {
        $stats['unfinished_percentage'] = 0;
    }
    $stats['unfinished_orders'] = $unfinished_orders;

    // Current Month's Revenue
    $firstDayOfMonth = date('Y-m-01');
    $stmt = $pdo->prepare("
        SELECT 
            SUM(total_amount) as monthly_revenue,
            COUNT(*) as monthly_orders
        FROM orders 
        WHERE order_date >= ?
    ");
    $stmt->execute([$firstDayOfMonth]);
    $monthly_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['monthly_revenue'] = $monthly_stats['monthly_revenue'];
    $stats['monthly_orders'] = $monthly_stats['monthly_orders'];

    // Calculate monthly revenue percentage
    if ($stats['revenue'] > 0) {
        $stats['monthly_percentage'] = round(($stats['monthly_revenue'] / $stats['revenue']) * 100, 2);
    } else {
        $stats['monthly_percentage'] = 0;
    }

    // Total Customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Recent Orders
    $stmt = $pdo->prepare("
        SELECT 
            o.id as order_id,
            o.order_date,
            o.total_amount,
            o.status,
            u.company_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Most Popular Products
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            SUM(oi.quantity) as total_sold,
            SUM(oi.quantity * oi.price_at_purchase) as revenue
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stmt->execute();
    $popular_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent Customers
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.company_name,
            u.email,
            u.phone,
            COUNT(o.id) as order_count
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StackCore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        #content {
            overflow-y: visible;
            overflow-x: visible;

        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            cursor: pointer;
            transform: scale(1.05);
            z-index: 1000;
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
            color: #666;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
        }

        .stat-card i {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stat-card.product i {
            color: #4CAF50;
        }

        .stat-card.category i {
            color: #2196F3;
        }

        .stat-card.order i {
            color: #FF9800;
        }

        .stat-card.customer i {
            color: #9C27B0;
        }

        .stat-card.revenue i {
            color: #FFC107;
        }

        .stat-card.unfinished i {
            color: #FF9800;
        }

        .unfinished-orders {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 0.9em;
        }

        .unfinished-status {
            display: block;
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
            font-style: italic;
        }

        .monthly-stats {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .monthly-orders {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 0.9em;
        }

        .monthly-percentage {
            display: block;
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section h2 {
            margin: 0 0 20px 0;
            color: #333;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .table tr:hover {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-en_attente {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-confirmee {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-payee {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-annulee {
            background-color: #fce4ec;
            color: #c62828;
        }

        .status-livree {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .trend-arrow {
            font-size: 0.8em;
            margin-left: 5px;
        }

        .trend-up {
            color: #4CAF50;
        }

        .trend-down {
            color: #f44336;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="stat-card product">
            <i class="fas fa-box"></i>
            <h3>Produits</h3>
            <div class="number"><?php echo number_format($stats['products']); ?></div>
        </div>

        <div class="stat-card category">
            <i class="fas fa-list"></i>
            <h3>Catégories</h3>
            <div class="number"><?php echo number_format($stats['categories']); ?></div>
        </div>

        <div class="stat-card order">
            <i class="fas fa-shopping-cart"></i>
            <h3>Commandes</h3>
            <div class="number"><?php echo number_format($stats['orders']); ?></div>
        </div>

        <div class="stat-card customer">
            <i class="fas fa-users"></i>
            <h3>Clients</h3>
            <div class="number"><?php echo number_format($stats['customers']); ?></div>
        </div>

        <div class="stat-card unfinished">
            <i class="fas fa-hourglass-half"></i>
            <h3>Commandes en Cours</h3>
            <div class="number"><?php echo number_format($stats['unfinished_orders']); ?></div>
            <div class="monthly-stats">
                <span class="unfinished-orders"><?php echo $stats['unfinished_percentage']; ?>% des commandes</span>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $stats['unfinished_percentage']; ?>%"></div>
                </div>
                <span class="unfinished-status">En attente, confirmée, payée, ou en cours</span>
            </div>
        </div>

        <div class="stat-card revenue">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Revenue Mois</h3>
            <div class="number"><?php echo '$' . number_format($stats['monthly_revenue'], 2); ?></div>
            <div class="monthly-stats">
                <span class="monthly-orders"><?php echo number_format($stats['monthly_orders']); ?> commandes</span>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $stats['monthly_percentage']; ?>%"></div>
                </div>
                <span class="monthly-percentage"><?php echo $stats['monthly_percentage']; ?>% du total</span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Dernières Commandes</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($order['company_name']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span
                                    class="status-badge status-<?php echo strtolower(str_replace(' ', '_', $order['status'])); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>Produits les plus populaires</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Vendus</th>
                        <th>Revenu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popular_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['total_sold']); ?></td>
                            <td>$<?php echo number_format($product['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>Nouveaux Clients</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Email</th>
                        <th>Commandes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo $customer['order_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>