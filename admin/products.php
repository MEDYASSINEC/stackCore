<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index_admin.php");
    exit();
}

require_once '../config/db.php';

try {

    // get products
    $tri = $_GET['tri'] ?? 'id';
    $asc = $_GET['asc'] ?? 'asc';
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY $tri $asc");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // get media

    $stmt2 = $pdo->prepare("SELECT product_id, media_url , is_main FROM product_media");
    $stmt2->execute();
    $medias = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // get product & categories

    $stmt3 = $pdo->prepare("SELECT pc.product_id, c.name FROM categories as c JOIN product_category as pc ON c.id = pc.category_id");
    $stmt3->execute();
    $product_categories = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // get categories
    $stmt4 = $pdo->prepare("SELECT name FROM categories");
    $stmt4->execute();
    $categories = $stmt4->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Document</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .search-container {
            margin-bottom: 20px;
            width: 300px;
        }

        .search-container input {
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            width: 300px;
        }

        #products {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: fit-content;
        }

        /* #products table {
        width: 100%;
        border-collapse: collapse;
    }

    #products tr {
        border-bottom: 1px solid #ccc;
    }

    #products th {
        text-align: start;
        padding: 2px 8px;
        font-weight: 100;
        color: gray;
        white-space: nowrap;
        cursor: pointer;
    }

    #products td {
        padding: 10px 8px;
        white-space: nowrap;
    } */

        #products td img {
            height: 20px;
        }

        #products td:last-child {
            display: flex;
            justify-content: center;
            gap: 2px;
        }

        #products i {
            cursor: pointer;
            margin: 2px;
        }

        #products a:first-of-type i {
            color: blue;
        }

        #products a:last-of-type i {
            color: red;
        }

        /* pour les champ de modification */

        #products textarea {
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px;
            box-sizing: border-box;

        }

        #products input {
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px;
            box-sizing: border-box;
        }

        /* Style pour le formulaire d'ajout */
        .add-product-form {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: none;
        }

        .add-product-form h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <section id="products">
        <h2>Products</h2>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="customerSearch" placeholder="Search customers...">
        </div>

        <button class="btn-primary" onclick="showForm(this)">Ajouter un produit</button>
        <div class="add-product-form">
            <h3>Ajouter un produit</h3>
            <form id="add-product" method="POST" enctype="multipart/form-data" action="function.php">
                <input type="hidden" name="action" value="ajouter">

                <div class="form-group">
                    <label for="nom">Nom du produit</label>
                    <input type="text" id="nom" name="nom">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="prix">Prix</label>
                    <input type="number" id="prix" name="prix" step="0.01">
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0">
                </div>

                <div class="form-group">
                    <label for="quantite_min">Quantité minimale</label>
                    <input type="number" id="quantite_min" name="quantite_min" min="1" value="1">
                </div>

                <div class="form-group">
                    <label for="promotion">En promotion</label>
                    <select id="promotion" name="promotion">
                        <option value="0">Non</option>
                        <option value="1">Oui</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="prix_promo">Prix promotionnel</label>
                    <input type="number" id="prix_promo" name="prix_promo" step="0.01" disabled required>
                </div>

                <div class="form-group">
                    <label for="media">Media</label>
                    <input type="file" id="media" name="medias[]" multiple>
                </div>

                <div class="form-group">
                    <label for="views">Nombre de vues</label>
                    <input type="number" id="views" name="views" min="0" value="0">
                </div>

                <div class="form-group">
                    <label for="categories">Catégories</label>
                    <select id="categories" name="categories[]" multiple>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Ajouter le produit</button>
                <button type="button" class="btn-secondary" onclick="hideForm(this)">Annuler</button>
            </form>
        </div>
        <table class="products-table">
            <thead>
                <tr>
                    <th class="tri" data-colonne="id">ID</th>
                    <th>Image</th>
                    <th class="tri" data-colonne="name">Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="tri" data-colonne="price">Price</th>
                    <th class="tri" data-colonne="stock">Stock</th>
                    <th>Media</th>
                    <th class="tri" data-colonne="min_order_quantity">Min Order Quantity</th>
                    <th class='tri' data-colonne="is_on_promotion">En Promotion</th>
                    <th class="tri" data-colonne="promotion_price">Promotion Prix</th>
                    <th class="tri" data-colonne="views">Views</th>
                    <th class="tri" data-colonne="created_at">Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr data-id="<?php echo $product['id']; ?>">
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="<?php
                            foreach ($medias as $media) {
                                if ($media['product_id'] == $product['id'] && $media['is_main']) {
                                    echo "../images/" . $media['media_url'];
                                }
                            } ?>" alt="image">
                        </td>
                        <td class="T_product textarea" data-colonne="name"><?php echo $product['name']; ?></td>
                        <td class="T_product textarea" data-colonne="description"><?php echo $product['description']; ?>
                        </td>
                        <td class="select T_categorie">
                            <?php
                            foreach ($product_categories as $category) {
                                if ($category['product_id'] == $product['id']) {
                                    echo $category['name'] . '<br>';
                                }
                            }
                            ?>
                        </td>
                        <td class="T_product input" data-colonne="price"><?php echo $product['price']; ?></td>
                        <td class="T_product input" data-colonne="stock"><?php echo $product['stock']; ?></td>
                        <td class="textarea"><?php
                        foreach ($medias as $media) {
                            if ($media['product_id'] == $product['id']) {
                                $url = str_replace('images/', '', $media['media_url']);
                                echo $url . '<br>';
                            }
                        }
                        ?></td>
                        <td class="T_product input" data-colonne="min_order_quantity">
                            <?php echo $product['min_order_quantity']; ?>
                        </td>
                        <td class="T_product input" data-colonne="is_on_promotion">
                            <?php echo $product['is_on_promotion'] == 1 ? 'Y' : 'N'; ?>
                        </td>
                        <td class="T_product input" data-colonne="promotion_price">
                            <?php echo $product['promotion_price']; ?>
                        </td>
                        <td class="T_product input" data-colonne="views"><?php echo $product['views']; ?></td>
                        <td><?php echo $product['created_at']; ?></td>
                        <td class="delete-product" data-id="<?php echo $product['id']; ?>">
                            <a href="#"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <input type="hidden" id="tri" value="<?php echo $_GET['tri'] ?? 'id'; ?>">
    <input type="hidden" id="asc" value="<?php echo $_GET['asc'] ?? 'asc'; ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showForm(button) {
            $('.add-product-form').show();
            $(button).hide();
        }

        function hideForm(button) {
            $('.add-product-form').hide();
            $('button[onclick="showForm(this)"]').show();
        }

        $(document).ready(function () {
            $('#customerSearch').on('keyup', function () {
                var value = $(this).val().toLowerCase().trim();
                $('.products-table tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            // Confirmation de suppression
            $(".delete-product").on('click', function (e) {
                e.preventDefault();
                const productId = $(this).data('id');
                if (confirm('confirmer la supprision du produit de l\'id : ' + productId)) {
                    $.ajax({
                        url: 'function.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            table: 'produits',
                            id: productId
                        },
                        success: function (response) {
                            response = JSON.parse(response);
                            if (response.success) {
                                $('#products').load('products.php');
                            } else {
                                alert('Erreur lors de la suppression');
                            }
                        },
                        error: function () {
                            alert('Erreur lors de la suppression');
                        }
                    });
                }
            });


            $('#promotion').change(function () {
                $('#prix_promo').prop('disabled', $('#promotion').val() == 0);

            })
            // Gestion du formulaire d'ajout
            $('add-product').on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: 'function.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response, satuts) {

                        // Parser la réponse JSON envoyée par PHP
                        // console.log(response)
                        const result = JSON.parse(response);


                        // Vérifier si le champ "success" est vrai
                        if (result.success) {
                            alert('✅ Produit ajouté avec succès');

                            // Recharger une partie de la page (par ex. liste produits)
                            $('#products').load(
                                'products.php'
                            ); // Tu peux cibler une section avec un sélecteur plus précis
                        } else {
                            // alert('Erreur: ' + result.message);
                        }

                    },
                    error: function (xhr, status, error) {
                        alert("Erreur: " + error);
                    }
                });

            });


            // Gestion du tri et des modifications existantes...
            let text;
            // met le texte du cellule dans un input
            // pour les champs input
            $('.input').dblclick(function () {
                text = $(this).text().trim();
                $(this).html(`<input type='text' value='${text}'>`);
                $(this).children('input').focus();

            })
            // pour les champs textarea
            $('.textarea').dblclick(function () {
                text = $(this).text().trim();
                $(this).html(`<textarea type='text'>${text}</textarea>`);
                $(this).children('textarea').focus();
            })
            // pour les champs select
            $('.select').dblclick(function () {
                text = $(this).text().trim();
                console.log(text)
                $(this).html(
                    `<select >
                        <?php
                        foreach ($categories as $category) {
                            echo "<option>{$category['name']}</option>";
                        }
                        ?>
                    </select>`
                )
                $(this).children('select').children('option').each(function () {
                    if ($(this).text() === text.trim()) {
                        $(this).prop('selected', true);
                    }
                })
                $(this).children('select').focus();
            })
            // applique les modifications
            $(document).on('blur', 'td input , td textarea, td select', function () {
                let newText = $(this).val().trim();
                let td = $(this).closest('td');
                console.log(text);
                console.log(newText);
                let confirmation = (text === newText || newText == '') ? false : confirm(
                    "Enregister les modification?");
                if (newText.trim() !== '' && confirmation) {
                    if ($(td).hasClass("T_product")) {
                        let action = 'modifier';
                        let table = 'product';
                        let idProd = $(this).closest('tr').data('id');
                        let colonne = td.data('colonne');
                        newText = (colonne === 'is_on_promotion') ? newText.toUpperCase() : newText;

                        $.ajax({
                            url: 'function.php',
                            type: 'POST',
                            data: {
                                action: action,
                                table: table,
                                id: idProd,
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
                    } else if ($(td).hasClass('T_categorie')) {
                        let action = 'modifier';
                        let table = 'product_category';
                        let idProd = $(this).closest('tr').data('id');

                        $.ajax({
                            url: 'function.php',
                            type: 'POST',
                            data: {
                                action: action,
                                table: table,
                                id: idProd,
                                value: newText
                            },
                            success: function (response) {
                                alert("Réponse du serveur: " + response);
                            },
                            error: function (xhr, start, error) {
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

                reloadContent('products.php', tri, asc);

            })
        });
    </script>
</body>

</html>