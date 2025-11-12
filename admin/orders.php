<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index_admin.php");
    exit();
}

require_once '../config/db.php';

try {
    $tri = $_GET['tri'] ?? 'order_id';
    $asc = $_GET['asc'] ?? 'asc';
    // Récupérer toutes les commandes avec détails
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
        SUM(oi.quantity * oi.price_at_purchase) AS calculated_total
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY $tri $asc
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Statistiques
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue
        FROM (
            SELECT 
                o.id,
                SUM(oi.quantity * oi.price_at_purchase) as total_amount
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
        ) as order_totals
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Orders - StackCore</title>
    <style>
        .orders-section {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }

        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .orders-table tr:hover {
            background: #f8f9fa;
        }

        .order-actions a {
            margin-right: 10px;
            color: #6c757d;
            text-decoration: none;
        }

        .order-actions a:hover {
            color: #007bff;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-processing {
            background-color: #17a2b8;
            color: white;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            width: 300px;
        }

        @media (max-width: 768px) {
            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }

        .remarque {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .delivery-status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .delivery-status-badge.status-pending {
            background-color: #f0f0f0;
            color: #666;
        }

        .delivery-status-badge.status-shipped {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .delivery-status-badge.status-delivered {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .delivery-status-badge.status-cancelled {
            background-color: #fce4ec;
            color: #c62828;
        }

        #orderModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            height: 90%;
            background-color: rgb(161, 161, 161);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.9);
            border-radius: 10px;
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        #orderModal .modal-content {
            width: 100%;
            height: 100%;
            margin: 20px;

        }

        #orderModal .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
            color: white;
        }

        #orderModal .close:hover {
            color: #ccc;
        }
    </style>
</head>

<body>
    <div class="orders-section">
        <h2>Orders</h2>
        <!-- Stats Card -->
        <div class="stats-card">
            <div>
                <h3>Total Orders: <?php echo $stats['total_orders']; ?></h3>
                <h4>Total Revenue: $<?php echo number_format($stats['total_revenue'], 2); ?></h4>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="orderSearch" placeholder="Search orders...">
        </div>

        <!-- Orders Table -->
        <table class="orders-table">
            <thead>
                <tr>
                    <th class="tri" data-colonne="order_id">ID</th>
                    <th>Customer</th>
                    <th class="tri" data-colonne="status">Status</th>
                    <th class="tri" data-colonne="delivery_status">Delivery Status</th>
                    <th class="tri" data-colonne="total_amount">Total Amount</th>
                    <th>Items</th>
                    <th class="tri" data-colonne="order_date">Created At</th>
                    <th>Remarque</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr data-id="<?php echo $order['order_id']; ?>">
                        <td><?php echo $order['order_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['company_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($order['email']); ?></small><br>
                            <small><?php echo htmlspecialchars($order['phone']); ?></small>
                        </td>
                        <td class="select" data-select="status">
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="select" data-select="delivery_status">
                            <span class="delivery-status-badge status-<?php echo strtolower($order['delivery_status']); ?>">
                                <?php echo ucfirst($order['delivery_status']); ?>
                            </span>
                        </td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT p.name, oi.quantity, oi.price_at_purchase
                                FROM order_items oi
                                LEFT JOIN products p ON oi.product_id = p.id
                                WHERE oi.order_id = ?
                            ");
                            $stmt->execute([$order['order_id']]);
                            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($items as $item): ?>
                                <?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>) -
                                $<?php echo number_format($item['price_at_purchase'], 2); ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td class="remarque"><?php echo htmlspecialchars($order['remarque'] ?? '________'); ?></td>
                        <td class="order-actions">
                            <a href="#" class="view-order" data-id="<?php echo $order['order_id']; ?>">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="delete-order" data-id="<?php echo $order['order_id']; ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour afficher les détails de la commande -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
        </div>
    </div>

    <input type="hidden" id="tri" value="<?php echo $_GET['tri'] ?? 'id'; ?>">
    <input type="hidden" id="asc" value="<?php echo $_GET['asc'] ?? 'asc'; ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Search functionality
        $(document).ready(function () {
            $('#orderSearch').on('keyup', function () {
                var value = $(this).val().toLowerCase().trim();
                $('.orders-table tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().trim().indexOf(value) > -1)
                });
            });


            // Modal
            var modal = document.getElementById("orderModal");
            var modalContent = document.getElementsByClassName("modal-content")[0];
            var span = document.getElementsByClassName("close")[0];

            span.onclick = function () {
                modal.style.display = "none";
            }

            window.onclick = function (event) {
                console.log(modal);
                console.log(modalContent);
                console.log(event.target);
                console.log(this);
                if (!modal.contains(event.target)) {
                    modal.style.display = "none";
                }
            }
            // modal.onmouseenter = function(event) {
            //     modal.style.backgroundColor = "rgba(0, 225, 255, 0.5)";
            // }
            // modal.onmouseleave = function(event) {
            //     modal.style.backgroundColor = "rgba(255, 0, 0, 0.5)";
            // }

            // View Order Details
            $('.view-order').on('click', function (e) {
                e.preventDefault();
                const orderId = $(this).data('id');

                $.ajax({
                    url: 'function.php',
                    type: 'POST',
                    data: {
                        action: 'view_order',
                        id: orderId
                    },
                    success: function (response) {
                        $('#orderDetails').html(response);
                        modal.style.display = "block";
                    },
                    error: function () {
                        alert('Error fetching order details');
                    }
                });
            });



            // Delete Order
            $('.delete-order').on('click', function (e) {
                e.preventDefault();
                const orderId = $(this).data('id');

                if (confirm('Are you sure you want to delete this order?')) {
                    $.ajax({
                        url: 'function.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            table: 'orders',
                            id: orderId
                        },
                        success: function (response) {
                            response = JSON.parse(response);
                            console.log(response);
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error deleting order');
                            }
                        },
                        error: function () {
                            alert('Error deleting order');
                        }
                    });
                }
            });
            // modifications des champs
            // pour les champs select
            let text;
            $('.select').dblclick(function () {
                text = $(this).text().trim();
                console.log(text)
                if ($(this).data('select') == 'status') {
                    $(this).html(
                        `<select id="status" name="status">
                        <option value="en attente">En attente</option>
                        <option value="confirmée">Confirmé</option>
                        <option value="payée">Payé</option>
                        <option value="en cours">En cours</option>
                        <option value="expédiée">Expédié</option>
                        <option value="annulée">Annulé</option>
                        <option value="remboursée">Remboursé</option>
                        <option value="livré">Livré</option>
                    </select>`
                    )
                }
                if ($(this).data('select') == 'delivery_status') {
                    $(this).html(
                        `<select id="delivery_status" name="delivery_status">
                        <option value="en préparation">En préparation</option>
                        <option value="annulée">Annulée</option>
                        <option value="expédiée">Expédiée</option>
                        <option value="en transit">En transit</option>
                        <option value="livrée">Livrée</option>
                        <option value="retournée">Retournée</option>
                        <option value="échec de livraison">Échec de livraison</option>
                    </select>`
                    )
                }
                $(this).children('select').children('option').each(function () {
                    if ($(this).text() === text.trim()) {
                        $(this).prop('selected', true);
                    }
                })
                $(this).children('select').focus();
            })
            // applique les modifications
            $(document).on('blur', 'td select', function () {
                let newText = $(this).val().trim();
                let td = $(this).closest('td');
                console.log(text);
                console.log(newText);
                let confirmation = (text === newText || newText == '') ? false : confirm(
                    "Enregister les modification?");
                if (newText.trim() !== '' && confirmation) {
                    if ($(td).hasClass("select")) {
                        let action = 'modifier_order';
                        let table = 'orders';
                        let idOrder = $(this).closest('tr').data('id');
                        let colonne = td.data('select');
                        newText = (colonne === 'is_on_promotion') ? newText.toUpperCase() : newText;

                        $.ajax({
                            url: 'function.php',
                            type: 'POST',
                            data: {
                                action: action,
                                table: table,
                                id: idOrder,
                                colonne: colonne,
                                value: newText
                            },
                            success: function (response) {
                                alert("Réponse du serveur: " + response);
                            },
                            error: function (xhr, status, error) {
                                console.error("Erreur: " + error);
                                alert("Une erreur est survenue lors de la modification");
                            }
                        });
                    }
                } else {
                    newText = text;
                }
                td.text(newText);
            });
            // Gestion du tri
            let asc = $('#asc').val();
            let tri = $('#tri').val();
            $(".tri").click(function () {
                let colonne = $(this).data('colonne');
                asc = (asc === 'asc' && tri === colonne) ? 'desc' : 'asc';
                tri = colonne;

                reloadContent('orders.php', tri, asc);

            })
        });
    </script>
</body>

</html>