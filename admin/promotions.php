<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index_admin.php");
    exit();
}

require_once '../config/db.php';
// require_once 'function.php';


try {
    // Get all discount codes
    $stmt = $pdo->prepare("
        SELECT 
            dc.*, 
            u.company_name
        FROM discount_codes dc
        LEFT JOIN users u ON dc.user_id = u.id
        ORDER BY dc.valid_until DESC
    ");
    $stmt->execute();
    $discount_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_codes,
            SUM(CASE WHEN used_count < usage_limit THEN 1 ELSE 0 END) as available_codes,
            SUM(CASE WHEN valid_until > NOW() THEN 1 ELSE 0 END) as active_codes
        FROM discount_codes
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get recently used codes
    $stmt = $pdo->prepare("
        SELECT 
            dc.code,
            dc.description,
            COUNT(o.id) as usage_count,
            MAX(o.order_date) as last_used
        FROM discount_codes dc
        LEFT JOIN orders o ON dc.id = o.discount_code_id
        GROUP BY dc.id
        ORDER BY last_used DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recently_used = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get most used codes
    $stmt = $pdo->prepare("
        SELECT 
            dc.code,
            dc.description,
            COUNT(o.id) as usage_count,
            SUM(o.total_amount) as total_discounted
        FROM discount_codes dc
        LEFT JOIN orders o ON dc.id = o.discount_code_id
        GROUP BY dc.id
        ORDER BY usage_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $most_used = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'add_promotion') {
            $code = trim($_POST['code']);
            $description = trim($_POST['description']);
            $discount_percent = trim($_POST['discount_percent']);
            $valid_from = trim($_POST['valid_from']);
            $valid_until = trim($_POST['valid_until']);
            $usage_limit = trim($_POST['usage_limit']);
            $min_order_amount = trim($_POST['min_order_amount']);
            $user_id = trim($_POST['user_id'] ?? null);

            $stmt = $pdo->prepare("
                INSERT INTO discount_codes (
                    code, description, discount_percent, valid_from, 
                    valid_until, usage_limit, min_order_amount, user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $code,
                $description,
                $discount_percent,
                $valid_from,
                $valid_until,
                $usage_limit,
                $min_order_amount,
                $user_id
            ]);
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Promotions - StackCore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-item h3 {
            margin: 0;
            font-size: 1.2em;
            color: #666;
        }

        .stat-item p {
            margin: 5px 0 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .add-promotion {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }

        .add-promotion:hover {
            background-color: #0056b3;
        }

        .promotion-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .promotion-table th,
        .promotion-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .promotion-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .promotion-table tr:hover {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-expired {
            background-color: #fce4ec;
            color: #c62828;
        }

        .status-available {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .edit-btn {
            background: #2196F3;
            color: white;
        }

        .delete-btn {
            background: #f44336;
            color: white;
        }

        .recent-activity {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .recent-activity h2 {
            margin: 0 0 15px;
            color: #333;
        }

        .activity-list {
            list-style: none;
            padding: 0;
        }

        .activity-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            padding: 20px;
        }


        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        /* style du formulaire */
        form {
            width: 90%;
            margin: 10px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: #fff;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-input:valid {
            border-color: #28a745;
        }

        .form-input::placeholder {
            color: #aaa;
            transition: opacity 0.3s ease;
        }

        .form-input:focus::placeholder {
            opacity: 0.7;
        }

        .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: #fff;
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .form-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .checkbox-label {
            color: #666;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        .form-button {
            width: 100%;
            padding: 18px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .form-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .form-button:active {
            transform: translateY(-1px);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .form-input.error {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            animation: slideIn 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-title {
                font-size: 24px;
            }
        }

        /* Style pour les icônes dans les inputs */
        .input-with-icon {
            position: relative;
        }

        .input-with-icon .form-input {
            padding-left: 50px;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 18px;
            pointer-events: none;
        }

        /* Effet de focus pour les labels */
        .floating-label {
            position: relative;
        }

        .floating-label .form-input:focus+.form-label,
        .floating-label .form-input:not(:placeholder-shown)+.form-label {
            transform: translateY(-25px) scale(0.85);
            color: #667eea;
        }

        .floating-label .form-label {
            position: absolute;
            top: 15px;
            left: 20px;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Gestion des Promotions</h1>

        <div class="stats-card">
            <div class="stat-item">
                <h3>Total des Codes</h3>
                <p><?php echo $stats['total_codes']; ?></p>
            </div>
            <div class="stat-item">
                <h3>Codes Disponibles</h3>
                <p><?php echo $stats['available_codes']; ?></p>
            </div>
            <div class="stat-item">
                <h3>Codes Actifs</h3>
                <p><?php echo $stats['active_codes']; ?></p>
            </div>
        </div>

        <button class="add-promotion" onclick="openAddPromotionModal()">
            <i class="fas fa-plus"></i> Ajouter une Promotion
        </button>

        <div class="recent-activity">
            <h2>Activité Récemment</h2>
            <div class="activity-section">
                <h3>Codes les plus utilisés</h3>
                <ul class="activity-list">
                    <?php foreach ($most_used as $code): ?>
                        <li class="activity-item">
                            <span><?php echo htmlspecialchars($code['code']); ?></span>
                            <span><?php echo $code['usage_count']; ?> utilisations</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="activity-section">
                <h3>Derniers Codes Utilisés</h3>
                <ul class="activity-list">
                    <?php foreach ($recently_used as $code): ?>
                        <li class="activity-item">
                            <span><?php echo htmlspecialchars($code['code']); ?></span>
                            <span><?php echo date('d/m/Y', strtotime($code['last_used'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <table class="promotion-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Remise (%)</th>
                    <th>Valide Du</th>
                    <th>Valide Jusqu'à</th>
                    <th>Usage Restant</th>
                    <th>Montant Minimum</th>
                    <th>Entreprise</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discount_codes as $code): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($code['code']); ?></td>
                        <td><?php echo htmlspecialchars($code['description']); ?></td>
                        <td><?php echo $code['discount_percent']; ?>%</td>
                        <td><?php echo date('d/m/Y', strtotime($code['valid_from'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($code['valid_until'])); ?></td>
                        <td><?php echo $code['usage_limit'] - $code['used_count']; ?></td>
                        <td>$<?php echo number_format($code['min_order_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($code['company_name'] ?? 'Tous'); ?></td>
                        <td>
                            <span class="status-badge 
                                <?php
                                echo ($code['valid_until'] < date('Y-m-d')) ? 'status-expired' :
                                    (($code['used_count'] >= $code['usage_limit']) ? 'status-expired' :
                                        'status-active');
                                ?>
                            ">
                                <?php
                                echo ($code['valid_until'] < date('Y-m-d')) ? 'Expiré' :
                                    (($code['used_count'] >= $code['usage_limit']) ? 'Expiré' :
                                        'Actif');
                                ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <button class="edit-btn" onclick="editPromotion(<?php echo $code['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-btn" onclick="deletePromotion(<?php echo $code['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour ajouter/modifier une promotion -->
    <div id="promotionModal" class="modal">
        <div class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h2>Ajouter une Promotion</h2>
                <form id="promotionForm" onsubmit="return submitPromotion(event)">
                    <div class="form-group">
                        <label for="code" class="form-label">Code Promotion:</label>
                        <input type="text" id="code" name="code" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="description" class="form-label">Description:</label>
                        <input type="text" id="description" name="description" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="discount_percent" class="form-label">Pourcentage de Remise:</label>
                        <input type="number" id="discount_percent" name="discount_percent" class="form-input" min="1"
                            max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="valid_from" class="form-label">Valide Du:</label>
                        <input type="date" id="valid_from" name="valid_from" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="valid_until" class="form-label">Valide Jusqu'à:</label>
                        <input type="date" id="valid_until" name="valid_until" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="usage_limit" class="form-label">Limite d'Utilisation:</label>
                        <input type="number" id="usage_limit" name="usage_limit" class="form-input" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="min_order_amount" class="form-label">Montant Minimum:</label>
                        <input type="number" id="min_order_amount" name="min_order_amount" class="form-input"
                            step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="user_id" class="form-label">Entreprise Spécifique (optionnel):</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">Tous les clients</option>
                            <?php
                            $stmt = $pdo->prepare("SELECT id, company_name FROM users ORDER BY company_name");
                            $stmt->execute();
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($users as $user):
                                ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['company_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="form-button submit-btn">Enregistrer</button>
                        <button type="button" class="form-button cancel-btn" onclick="closeModal()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        console.log("hiii")
        function openAddPromotionModal() {
            document.getElementById('promotionModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('promotionModal').style.display = 'none';
        }

        function submitPromotion(event) {
            event.preventDefault();
            const formData = new FormData(event.target);

            fetch('function.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'add_promotion',
                    ...Object.fromEntries(formData)
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });

            closeModal();
            return false;
        }

        function editPromotion(id) {
            // TODO: Implement edit functionality
            alert('Fonctionnalité de modification à implémenter');
        }

        function deletePromotion(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')) {
                fetch('function.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'delete',
                        table: 'discount_codes',
                        id: id
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    });
            }
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('promotionModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>