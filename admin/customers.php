<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index_admin.php");
    exit();
}

require_once '../config/db.php';

try {
    // Récupérer tous les clients
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_customers FROM users");
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
    <title>Customers - StackCore</title>
    <style>
        .customers-section {
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

        .customers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .customers-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
        }

        .customers-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .customers-table tr:hover {
            background: #f8f9fa;
        }

        .customer-actions a {
            margin-right: 10px;
            color: #6c757d;
            text-decoration: none;
        }

        .customer-actions a:hover {
            color: #007bff;
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

        /* Responsive design */
        @media (max-width: 768px) {
            .customers-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="customers-section">
        <h2>Customers</h2>

        <!-- Stats Card -->
        <div class="stats-card">
            <h3>Total Customers: <?php echo $stats['total_customers']; ?></h3>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="customerSearch" placeholder="Search customers...">
        </div>

        <!-- Customers Table -->
        <table class="customers-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['address']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($customer['created_at'])); ?></td>
                        <td class="customer-actions">
                            <a href="#" class="delete-customer" data-id="<?php echo $customer['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Search functionality
        $(document).ready(function () {
            $('#customerSearch').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('.customers-table tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });

        // Delete customer confirmation
        $('.delete-customer').on('click', function (e) {
            e.preventDefault();
            const customerId = $(this).data('id');
            if (confirm('Are you sure you want to delete this customer?')) {
                $.ajax({
                    url: 'function.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        table: 'customers',
                        id: customerId
                    },
                    success: function (response) {
                        response = response;
                        alert(response);
                        if (response) {
                            location.reload();
                        } else {
                            alert('Error deleting customer');
                        }
                    },
                    error: function () {
                        alert('Error deleting customer');
                    }
                });
            }
        });

        // Edit customer functionality
        $('.edit-customer').on('click', function (e) {
            e.preventDefault();
            const customerId = $(this).data('id');
            // TODO: Implement edit customer functionality
            alert('Edit customer functionality will be implemented in future updates');
        });
    </script>
</body>

</html>