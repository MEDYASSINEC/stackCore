// js/cart-favorites.js
$(document).ready(function() {

    // Ajouter au panier
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('product-id');
        const quantity = $(this).closest('.product-card').find('.quantity-input').val() || 1;
        const button = $(this);

        // Désactiver le bouton pendant la requête
        button.prop('disabled', true).text('Ajout...');

        $.ajax({
            url: 'ajax/add_to_cart.php',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Afficher un message de succès
                    showNotification(response.message, 'success');

                    // Mettre à jour le compteur du panier
                    if (response.cart_count) {
                        updateCartCounter(response.cart_count);
                    }

                    // Animation sur le bouton
                    button.addClass('success').text('Ajouté !');
                    setTimeout(function() {
                        button.removeClass('success').text('Ajouter au panier');
                    }, 2000);

                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Erreur lors de l\'ajout au panier', 'error');
                console.error('Erreur AJAX:', error);
            },
            complete: function() {
                // Réactiver le bouton
                button.prop('disabled', false);
                if (!button.hasClass('success') && !button.text().includes('Ajouté')) {
                    button.text('Ajouter au panier');
                }
            }
        });
    });

    // Ajouter/Retirer des favoris
    $('.favorite-btn').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('product-id');
        const button = $(this);
        const icon = button.find('i');

        $.ajax({
            url: 'ajax/add_to_favorites.php',
            type: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');

                    // Mettre à jour l'icône
                    if (response.is_favorite) {
                        icon.removeClass('far').addClass('fas text-red-500');
                        button.attr('title', 'Retirer des favoris');
                    } else {
                        icon.removeClass('fas text-red-500').addClass('far');
                        button.attr('title', 'Ajouter aux favoris');
                    }

                    // Animation
                    button.addClass('animate-pulse');
                    setTimeout(function() {
                        button.removeClass('animate-pulse');
                    }, 500);

                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Erreur lors de la gestion des favoris', 'error');
                console.error('Erreur AJAX:', error);
            }
        });
    });

    // Retirer du panier (pour la page panier)
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();

        const cartItemId = $(this).data('cart-item-id');
        const row = $(this).closest('.cart-item');

        if (confirm('Êtes-vous sûr de vouloir retirer cet article du panier ?')) {
            $.ajax({
                url: 'ajax/remove_from_cart.php',
                type: 'POST',
                data: {
                    cart_item_id: cartItemId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');

                        // Supprimer la ligne avec animation
                        row.fadeOut(300, function() {
                            $(this).remove();
                            updateCartDisplay();
                        });

                        // Mettre à jour le compteur
                        updateCartCounter(response.cart_count);

                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Erreur lors de la suppression', 'error');
                    console.error('Erreur AJAX:', error);
                }
            });
        }
    });

    // Mettre à jour la quantité dans le panier
    $('.quantity-update').on('change', function() {
        const cartItemId = $(this).data('cart-item-id');
        const quantity = $(this).val();
        const input = $(this);

        if (quantity < 1) {
            $(this).val(1);
            return;
        }

        $.ajax({
            url: 'ajax/update_cart_quantity.php',
            type: 'POST',
            data: {
                cart_item_id: cartItemId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    updateCartCounter(response.cart_count);
                    updateCartDisplay();
                } else {
                    showNotification(response.message, 'error');
                    // Remettre l'ancienne valeur
                    input.val(input.data('original-value'));
                }
            },
            error: function(xhr, status, error) {
                showNotification('Erreur lors de la mise à jour', 'error');
                input.val(input.data('original-value'));
                console.error('Erreur AJAX:', error);
            }
        });
    });

    // Sauvegarder la valeur originale des inputs de quantité
    $('.quantity-update').each(function() {
        $(this).data('original-value', $(this).val());
    });

    // Fonction pour afficher les notifications
    function showNotification(message, type = 'info') {
        const notificationClass = type === 'success' ? 'bg-green-500' :
            type === 'error' ? 'bg-red-500' : 'bg-blue-500';

        const notification = $(`
            <div class="fixed top-4 right-4 z-50 p-4 text-white rounded-lg shadow-lg ${notificationClass} transform translate-x-full transition-transform duration-300">
                <div class="flex items-center">
                    <span>${message}</span>
                    <button class="ml-4 text-white hover:text-gray-200" onclick="$(this).parent().parent().remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);

        $('body').append(notification);

        // Animer l'entrée
        setTimeout(function() {
            notification.removeClass('translate-x-full');
        }, 10);

        // Auto-supprimer après 5 secondes
        setTimeout(function() {
            notification.addClass('translate-x-full');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
    }

    // Mettre à jour le compteur du panier
    function updateCartCounter(count) {
        $('.cart-counter').text(count);
        if (count > 0) {
            $('.cart-counter').removeClass('hidden').addClass('animate-bounce');
            setTimeout(function() {
                $('.cart-counter').removeClass('animate-bounce');
            }, 1000);
        } else {
            $('.cart-counter').addClass('hidden');
        }
    }

    // Mettre à jour l'affichage du panier (totaux, etc.)
    function updateCartDisplay() {
        // Recalculer les totaux si on est sur la page panier
        if ($('.cart-total').length > 0) {
            let total = 0;
            $('.cart-item').each(function() {
                const price = parseFloat($(this).find('.item-price').data('price'));
                const quantity = parseInt($(this).find('.quantity-update').val());
                total += price * quantity;
            });

            $('.cart-total').text(total.toFixed(2) + ' €');
        }
    }

    // Gestion des quantités avec boutons + et -
    $('.quantity-decrease').on('click', function() {
        const input = $(this).siblings('.quantity-input');
        const currentValue = parseInt(input.val());
        const minValue = parseInt(input.attr('min')) || 1;

        if (currentValue > minValue) {
            input.val(currentValue - 1);
            input.trigger('change');
        }
    });

    $('.quantity-increase').on('click', function() {
        const input = $(this).siblings('.quantity-input');
        const currentValue = parseInt(input.val());
        const maxValue = parseInt(input.attr('max')) || 999;

        if (currentValue < maxValue) {
            input.val(currentValue + 1);
            input.trigger('change');
        }
    });

    // Validation des inputs de quantité
    $('.quantity-input').on('input', function() {
        const value = parseInt($(this).val());
        const min = parseInt($(this).attr('min')) || 1;
        const max = parseInt($(this).attr('max')) || 999;

        if (value < min) {
            $(this).val(min);
        } else if (value > max) {
            $(this).val(max);
        }
    });

    // Ajouter au panier depuis la page produit avec quantité personnalisée
    $('.add-to-cart-detailed').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('product-id');
        const quantity = $('#product-quantity').val() || 1;
        const button = $(this);

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ajout...');

        $.ajax({
            url: 'ajax/add_to_cart.php',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    updateCartCounter(response.cart_count);

                    button.html('<i class="fas fa-check"></i> Ajouté !').addClass('bg-green-500');
                    setTimeout(function() {
                        button.html('<i class="fas fa-shopping-cart"></i> Ajouter au panier')
                            .removeClass('bg-green-500');
                    }, 3000);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Erreur lors de l\'ajout au panier', 'error');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });

    // Vider tout le panier
    $('.clear-cart').on('click', function(e) {
        e.preventDefault();

        if (confirm('Êtes-vous sûr de vouloir vider complètement votre panier ?')) {
            $.ajax({
                url: 'ajax/clear_cart.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Panier vidé avec succès', 'success');
                        $('.cart-items-container').fadeOut(300, function() {
                            $(this).html('<div class="text-center py-8"><p class="text-gray-500">Votre panier est vide</p></div>').fadeIn();
                        });
                        updateCartCounter(0);
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Erreur lors du vidage du panier', 'error');
                }
            });
        }
    });

});