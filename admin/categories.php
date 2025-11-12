<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index_admin.php");
    exit();
}

require_once '../config/db.php';

// Gérer les actions (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'ajouter':
                $name = $_POST['name'] ?? '';
                if (!empty($name)) {
                    // Vérifier si la catégorie existe déjà
                    $check = $conn->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
                    $check->execute([$name]);
                    $exists = $check->fetchColumn();

                    if ($exists) {
                        echo "<p class='error'>La catégorie existe déjà.</p>";
                    } else {
                        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                        $stmt->execute([$name]);
                    }
                }
                break;

            case 'modifier':
                $id = $_POST['id'] ?? '';
                $name = $_POST['name'] ?? '';
                if (!empty($id) && !empty($name)) {
                    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
                    $stmt->execute([$name, $id]);
                }
                break;

            case 'supprimer':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                }
                break;
        }
    } catch (PDOException $e) {
        die("Erreur: " . $e->getMessage());
    }
}

// Récupérer toutes les catégories avec le nombre de produits
try {
    $tri = $_GET['tri'] ?? 'id';
    $asc = $_GET['asc'] ?? 'asc';
    $stmt = $pdo->prepare("
        SELECT 
            c.*, 
            COUNT(pc.product_id) as nombre_produits
        FROM categories c
        LEFT JOIN product_category pc ON c.id = pc.category_id
        GROUP BY c.id
        ORDER BY $tri $asc
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - StackCore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #content h1 {
            color: #333;
            margin-bottom: 20px;
        }

        #modal {
            display: none;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        /* Style pour la colonne nombre de produits */
        td:nth-child(3) {
            /* text-align: center; */
            font-weight: bold;
            /* color: #007bff; */
        }

        th {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #007bff;
        }

        .actions a:hover {
            color: #0056b3;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .fa-trash {
            color: red;
        }

        .fa-trash:hover {
            color: rgb(255, 0, 0, 0.8);
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Gestion des Catégories</h1>

        <!-- Formulaire d'ajout -->
        <form id="form-ajout" method="POST">
            <input type="hidden" name="action" value="ajouter">
            <div class="form-group">
                <label for="name">Nom de la catégorie:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>

        <!-- Liste des catégories -->
        <table>
            <thead>
                <tr>
                    <th data-colonne="id">ID</th>
                    <th data-colonne="name">Nom</th>
                    <th data-colonne="nombre_produits">Nombre de produits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><?php echo $category['name']; ?></td>
                        <td><?php echo $category['nombre_produits'] ?? 0; ?></td>
                        <td class="actions">
                            <a href="#" class="btn-edit" data-id="<?php echo $category['id']; ?>"
                                data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="btn-delete" data-id="<?php echo $category['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour la modification -->
    <div id="modal">
        <div class="modal-content">
            <h3>Modifier la catégorie</h3>
            <form id="form-modif" method="POST">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" id="id-modif" name="id">
                <div class="form-group">
                    <label for="name-modif">Nom:</label>
                    <input type="text" id="name-modif" name="name" required>
                </div>
                <button type="submit" class="btn btn-primary">Modifier</button>
                <button type="button" class="btn btn-danger" id="btn-close">Annuler</button>
            </form>
        </div>
    </div>
    <input type="hidden" id="tri" value="<?php echo $tri ?? 'id'; ?>">
    <input type="hidden" id="asc" value="<?php echo $asc ?? 'asc'; ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Gestion du modal
        var modal = document.getElementById('modal');
        var btnClose = document.getElementById('btn-close');

        btnClose.onclick = function () {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Gestion de la modification
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.onclick = function (e) {
                e.preventDefault();
                let id = this.dataset.id;
                let name = this.dataset.name;

                document.getElementById('id-modif').value = id;
                document.getElementById('name-modif').value = name;
                document.getElementById('name-modif').focus();
                modal.style.display = 'block';
            }
        });

        // Gestion de la suppression
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.onclick = function (e) {
                e.preventDefault();
                if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                    let id = this.dataset.id;
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });
        $(document).ready(function () {
            let asc = $('#asc').val();
            let tri = $('#tri').val();
            $('th:not(th:last-child)').click(function () {
                if (tri === $(this).data('colonne')) {
                    asc = (asc === 'asc') ? 'desc' : 'asc';
                } else {
                    asc = 'asc';
                }
                tri = $(this).data('colonne');
                reloadContent('categories.php', tri, asc);
            });
        });
    </script>
</body>

</html>