<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StackCore - Équipements Professionnels</title>
    <link rel="icon" href="images/logoS.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Variables CSS pour la cohérence */
        :root {
            /* Palette de couleurs principale */
            --primary-green: #AEC8A4;
            --primary-green-dark: #8FAA83;
            --primary-green-darker: #3B3B1A;
            --secondary-green: #E7EFC7;

            /* Couleurs neutres */
            --white: #ffffff;
            --gray-50: #f8f9fa;
            --gray-100: #f1f2f6;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;

            /* Couleurs d'accent */
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --text-muted: #999999;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;

            /* Ombres */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 25px rgba(0, 0, 0, 0.15);
            --shadow-header: rgba(0, 0, 0, 0.16) 0px 1px 4px;

            /* Espacements */
            --spacing-xs: 5px;
            --spacing-sm: 10px;
            --spacing-md: 15px;
            --spacing-lg: 20px;
            --spacing-xl: 30px;
            --spacing-xxl: 40px;

            /* Bordures */
            --border-radius-sm: 5px;
            --border-radius-md: 10px;
            --border-radius-lg: 15px;
            --border-radius-xl: 20px;
            --border-radius-pill: 25px;
            --border-radius-circle: 50%;

            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
        }

        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Base */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background-color: var(--gray-50);
            font-size: 16px;
        }

        /* ---------------------------------------------------- */
        .demo-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            position: relative;
        }

        .demo-container h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5em;
        }

        .demo-container p {
            color: #666;
            font-size: 1.2em;
            line-height: 1.6;
        }

        /* Widget de support client */
        .support-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .chat-toggle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ffd700, #ffb300);
            border: none;
            border-radius: 50%;
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 24px;
            color: white;
        }

        .chat-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(255, 193, 7, 0.6);
        }

        .chat-toggle.active {
            transform: rotate(45deg);
        }

        .support-menu {
            position: absolute;
            bottom: 80px;
            right: 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 25px;
            min-width: 280px;
            transform: translateY(20px) scale(0.8);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .support-menu.active {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
        }

        .welcome-message {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            position: relative;
        }

        .welcome-message::after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 30px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid #764ba2;
        }

        .welcome-message h3 {
            font-size: 1.1em;
            margin-bottom: 8px;
        }

        .welcome-message p {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .support-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .support-option {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #ffd700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 0.95em;
        }

        .support-option:hover {
            background: #ffb300;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(255, 179, 0, 0.3);
        }

        .support-option i {
            margin-right: 12px;
            font-size: 1.2em;
            width: 20px;
            text-align: center;
        }

        .contact-modal,
        .feedback-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .contact-modal.active,
        .feedback-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .contact-form,
        .feedback-form {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transform: scale(0.8);
            transition: transform 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .contact-modal.active .contact-form,
        .feedback-modal.active .feedback-form {
            transform: scale(1);
        }

        .contact-header,
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .contact-header h3,
        .feedback-header h3 {
            color: #333;
            font-size: 1.5em;
            margin: 0;
        }

        .close-contact,
        .close-feedback {
            background: none;
            border: none;
            font-size: 1.8em;
            cursor: pointer;
            color: #666;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-contact:hover,
        .close-feedback:hover {
            background: #f0f0f0;
            color: #333;
        }

        .rating-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .star {
            font-size: 32px;
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .star:hover,
        .star.active {
            color: #ffd700;
            transform: scale(1.2);
        }

        .rating-text {
            text-align: center;
            margin-top: 10px;
            font-weight: 600;
            color: #667eea;
            min-height: 20px;
        }

        .feedback-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }

        .category-chip {
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9em;
            background: white;
        }

        .category-chip:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .category-chip.selected {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95em;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            outline: none;
            background: #f8f9fa;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .submit-button {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .required {
            color: #e74c3c;
        }

        .chat-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .chat-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .chat-window {
            background: white;
            width: 90%;
            max-width: 500px;
            height: 600px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }

        .chat-modal.active .chat-window {
            transform: scale(1);
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header h3 {
            font-size: 1.2em;
        }

        .close-chat {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .close-chat:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
        }

        .message.bot .message-content {
            background: white;
            margin-right: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .message-content {
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-input {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #eee;
            border-radius: 25px;
            outline: none;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .message-input:focus {
            border-color: #667eea;
        }

        .send-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .send-button:hover {
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .support-menu {
                right: -10px;
                min-width: 250px;
            }

            .chat-window,
            .contact-form,
            .feedback-form {
                width: 95%;
                height: 80vh;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .feedback-categories {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Animations */
        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-5px);
            }

            60% {
                transform: translateY(-3px);
            }
        }

        .chat-toggle.bounce {
            animation: bounce 2s infinite;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        /* Header */
        header {
            background-color: var(--white);
            padding: 1rem 0;
            box-shadow: var(--shadow-header);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        /* Barre de recherche */
        .search-container {
            display: flex;
            gap: var(--spacing-sm);
            flex: 1;
            max-width: 500px;
            margin: 0 var(--spacing-lg);
        }

        .search-box {
            flex: 1;
            padding: 12px var(--spacing-md);
            border: 2px solid var(--secondary-green);
            border-radius: var(--border-radius-pill);
            font-size: 1rem;
            background-color: var(--secondary-green);
            transition: var(--transition-normal);
            outline: none;
        }

        .search-box:focus {
            border-color: var(--primary-green);
            background-color: var(--white);
            box-shadow: 0 0 0 3px rgba(174, 200, 164, 0.2);
        }

        .search-btn {
            background: var(--primary-green);
            color: var(--white);
            border: none;
            padding: 12px var(--spacing-lg);
            border-radius: var(--border-radius-pill);
            cursor: pointer;
            transition: var(--transition-normal);
            font-weight: 500;
        }

        .search-btn:hover {
            background: var(--primary-green-dark);
            transform: translateY(-1px);
        }

        .search-btn:active {
            transform: translateY(0);
        }

        /* Actions utilisateur */
        .user-actions {
            display: flex;
            gap: var(--spacing-md);
            align-items: center;
        }

        .action-btn {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid var(--primary-green);
            padding: 8px var(--spacing-md);
            border-radius: var(--border-radius-xl);
            cursor: pointer;
            transition: var(--transition-normal);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            background: var(--primary-green);
            color: var(--white);
            transform: translateY(-1px);
        }

        .action-btn i {
            font-size: 16px;
        }

        .cart-count,
        .fav-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: var(--white);
            border-radius: var(--border-radius-circle);
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .connexion {
            background: var(--primary-green);
            padding: 8px var(--spacing-lg);
            border-radius: var(--border-radius-pill);
            transition: var(--transition-normal);
        }

        .connexion:hover {
            background: var(--primary-green-dark);
            transform: translateY(-1px);
        }

        .connexion a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
        }

        /* Section filtres */
        .filters-section {
            background: var(--white);
            padding: var(--spacing-lg) 0;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: var(--spacing-lg);
        }

        .filters-container {
            display: flex;
            gap: var(--spacing-lg);
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .filter-group label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            transition: var(--transition-normal);
            outline: none;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(174, 200, 164, 0.2);
        }

        .price-range {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }

        /* Sections produits */
        .product-section {
            margin: var(--spacing-xxl) 0;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--spacing-lg);
            position: relative;
            padding-bottom: var(--spacing-sm);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-green-dark));
            border-radius: 2px;
        }

        /* Grille de produits */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-lg);
        }

        /* Cartes produits */
        .product-card {
            background: var(--white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition-normal);
            position: relative;
            border: 1px solid var(--gray-200);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-green);
        }

        /* Images produits */
        .product-image {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-slow);
        }

        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .image-placeholder,
        .fallback-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: var(--gray-100);
            color: var(--gray-500);
            font-size: 3rem;
            position: absolute;
            top: 0;
            left: 0;
        }

        .image-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--gray-600);
            font-size: 14px;
        }

        /* Badge promotion */
        .promotion-badge {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
            background: var(--danger);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-lg);
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Informations produit */
        .product-info {
            padding: var(--spacing-lg);
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.4rem;
            color: var(--success);
            font-weight: 700;
            margin-bottom: var(--spacing-sm);
        }

        .original-price {
            text-decoration: line-through;
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 400;
            margin-left: var(--spacing-sm);
        }

        .product-stock {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: var(--spacing-md);
        }

        /* Actions produits */
        .product-actions {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }

        /* Boutons */
        .btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition-normal);
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            outline: none;
        }

        .btn:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .btn-primary {
            background: var(--info);
            color: var(--white);
            flex: 1;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-500);
            color: var(--white);
            flex: 1;
        }

        .btn-secondary:hover {
            background: var(--gray-600);
            transform: translateY(-1px);
        }

        .btn-favorite {
            background: transparent;
            color: var(--danger);
            border: 2px solid var(--danger);
            border-radius: var(--border-radius-circle);
            width: 40px;
            height: 40px;
            font-size: 18px;
            padding: 0;
            min-width: auto;
        }

        .btn-favorite:hover,
        .btn-favorite.active {
            background: var(--danger);
            color: var(--white);
            transform: scale(1.1);
        }

        /* Messages d'alerte */
        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            margin: var(--spacing-sm) 0;
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: var(--success);
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: var(--danger);
        }

        /* Chargement */
        .loading {
            text-align: center;
            padding: var(--spacing-xxl);
            font-size: 1.2rem;
            color: var(--text-secondary);
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid var(--gray-200);
            border-top: 4px solid var(--primary-green);
            border-radius: var(--border-radius-circle);
            animation: spin 1s linear infinite;
            margin-right: var(--spacing-sm);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Footer */
        footer {
            background: var(--text-primary);
            color: var(--white);
            padding: var(--spacing-xxl) 0 var(--spacing-lg);
            margin-top: 60px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-xl);
        }

        .footer-section h3 {
            margin-bottom: var(--spacing-md);
            color: var(--primary-green);
            font-weight: 600;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: var(--transition-normal);
        }

        .footer-section ul li a:hover {
            color: var(--white);
            padding-left: var(--spacing-xs);
        }

        .footer-bottom {
            text-align: center;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid #34495e;
            color: #bdc3c7;
            font-size: 14px;
        }

        .banner {
            background: url('bg.jpg') center/cover no-repeat;
            color: #fff;
            text-align: center;
            padding: 3rem 1rem;
        }

        .banner h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .banner button {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            background: white;
            color: black;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .banner button:hover {
            background: white;
            color: black;
        }

        .support {
            position: absolute;
            right: 20px;
            bottom: 100px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 0 var(--spacing-md);
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: var(--spacing-md);
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .search-container {
                width: 100%;
                max-width: none;
                margin: 0;
            }

            .user-actions {
                width: 100%;
                justify-content: center;
            }

            .filters-container {
                flex-direction: column;
                align-items: stretch;
                gap: var(--spacing-sm);
            }

            .filter-group {
                width: 100%;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: var(--spacing-sm);
            }

            .section-title {
                font-size: 1.5rem;
            }

            .product-actions {
                flex-direction: column;
            }

            .btn-favorite {
                align-self: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 var(--spacing-sm);
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-info {
                padding: var(--spacing-md);
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src='images/logo.png' alt="logo">
                </div>

                <div class="search-container">
                    <input type="text" class="search-box" id="searchInput" placeholder="Rechercher des produits...">
                    <button class="search-btn" onclick="searchProducts()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div class="user-actions">
                    <button class="action-btn" onclick="toggleFavorites()">
                        <i class="fas fa-heart"></i>
                        <span class="fav-count" id="favCount">0</span>
                    </button>
                    <button class="action-btn" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </button>
                </div>
                <?php
                if (!isset($_SESSION['user_id'])) {
                    echo "<div class='connexion'>
                    <a href='login.php'>Connexion</a>
                </div>";
                } else {
                    echo "<div class='connexion'>
                    <a href='logout.php'>Deconnexion</a>
                </div>";
                }
                ?>

            </div>
        </div>
    </header>
    <section class="banner" aria-label="Promotion principale StackCore">
        <h2>Bienvenue sur StackCore</h2>
        <p>Découvrez nos meilleures offres en Informatique et Bureautique. Technologie et performance à portée de
            clic !</p>
        <button onclick="location.href='#featured'">Voir les produits</button>
    </section>
    <section class="filters-section">
        <div class="container">
            <div class="filters-container">
                <div class="filter-group">
                    <label>Catégorie</label>
                    <select id="categoryFilter">
                        <option value="">Toutes les catégories</option>
                        <option value="Informatique">Informatique</option>
                        <option value="Bureautique">Bureautique</option>
                        <option value="Mobilier">Mobilier</option>
                        <option value="Fournitures Scolaires">Fournitures Scolaires</option>
                        <option value="Périphériques">Périphériques</option>
                        <option value="Accessoires">Accessoires</option>
                        <option value="Papeterie">Papeterie</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Prix</label>
                    <div class="price-range">
                        <input type="number" id="minPrice" placeholder="Min €" min="0">
                        <span>-</span>
                        <input type="number" id="maxPrice" placeholder="Max €" min="0">
                    </div>
                </div>

                <div class="filter-group">
                    <label>Trier par</label>
                    <select id="sortBy">
                        <option value="recent">Plus récents</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="popular">Popularité</option>
                        <option value="name">Nom A-Z</option>
                    </select>
                </div>

                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Appliquer
                </button>
            </div>
        </div>
    </section>

    <main class="container">
        <!-- Messages d'alerte -->
        <div id="alertContainer"></div>
        <section class="product-section" id="featured">
            <h2 class="section-title">
                <i class="fas fa-th-large"></i> Tous nos produits
            </h2>
            <div class="products-grid" id="allProducts">
                <div class="loading">
                    <div class="spinner"></div>
                    Chargement de tous les produits...
                </div>
            </div>
        </section>
        <!-- Produits en promotion -->
        <section class="product-section">
            <h2 class="section-title">
                <i class="fas fa-fire"></i> Promotions en cours
            </h2>
            <div class="products-grid" id="promotionProducts">
                <div class="loading">
                    <div class="spinner"></div>
                    Chargement des promotions...
                </div>
            </div>
        </section>

        <!-- Produits populaires -->
        <section class="product-section">
            <h2 class="section-title">
                <i class="fas fa-star"></i> Produits populaires
            </h2>
            <div class="products-grid" id="popularProducts">
                <div class="loading">
                    <div class="spinner"></div>
                    Chargement des produits populaires...
                </div>
            </div>
        </section>

        <!-- Produits récents -->
        <section class="product-section" id="news">
            <h2 class="section-title">
                <i class="fas fa-clock"></i> Nouveautés
            </h2>
            <div class="products-grid" id="recentProducts">
                <div class="loading">
                    <div class="spinner"></div>
                    Chargement des nouveautés...
                </div>
            </div>
        </section>
    </main>
    <!-- Widget de support client -->
    <div class="support">
        <div class="support-widget">
            <button class="chat-toggle bounce" id="chatToggle">
                💬
            </button>

            <div class="support-menu" id="supportMenu">
                <div class="welcome-message">
                    <h3>Bienvenue ! 👋</h3>
                    <p>Comment puis-je vous aider aujourd'hui ?</p>
                </div>

                <div class="support-options">
                    <button class="support-option" onclick="openChat()">
                        <i>💬</i>
                        Chat en direct
                    </button>

                    <a href="https://wa.me/1234567890?text=Bonjour%20!%20J%27aimerais%20avoir%20des%20informations%20sur%20vos%20produits%20de%20mobilier.%20Pouvez-vous%20m%27aider%20?"
                        class="support-option" target="_blank">
                        <i>📱</i>
                        WhatsApp
                    </a>

                    <button class="support-option" onclick="showContactForm()">
                        <i>📧</i>
                        Contactez-nous
                    </button>

                    <!-- <button class="support-option" onclick="showFeedbackForm()">
                        <i>⭐</i>
                        Donner votre avis
                    </button> -->
                </div>
            </div>
        </div>

        <!-- Modal d'avis -->
        <!-- <div class="feedback-modal" id="feedbackModal">
            <div class="feedback-form">
                <div class="feedback-header">
                    <h3>⭐ Donnez votre avis</h3>
                    <button class="close-feedback" onclick="closeFeedback()">×</button>
                </div>

                <form id="feedbackForm" onsubmit="submitFeedbackForm(event)">
                    <div class="form-group">
                        <label class="form-label">Évaluez votre expérience <span class="required">*</span></label>
                        <div class="rating-container">
                            <span class="star" data-rating="1">⭐</span>
                            <span class="star" data-rating="2">⭐</span>
                            <span class="star" data-rating="3">⭐</span>
                            <span class="star" data-rating="4">⭐</span>
                            <span class="star" data-rating="5">⭐</span>
                        </div>
                        <div class="rating-text" id="ratingText"></div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Que souhaitez-vous évaluer ?</label>
                        <div class="feedback-categories">
                            <div class="category-chip" data-category="produits">Produits</div>
                            <div class="category-chip" data-category="service">Service client</div>
                            <div class="category-chip" data-category="livraison">Livraison</div>
                            <div class="category-chip" data-category="site">Site web</div>
                            <div class="category-chip" data-category="prix">Prix</div>
                            <div class="category-chip" data-category="global">Expérience globale</div>
                        </div>
                        <input type="hidden" name="categories" id="selectedCategories">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Votre nom</label>
                            <input type="text" class="form-input" name="customerName" placeholder="Optionnel">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" name="customerEmail" placeholder="Optionnel">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Votre commentaire <span class="required">*</span></label>
                        <textarea class="form-textarea" name="comment"
                            placeholder="Partagez votre expérience avec nous..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Recommanderiez-vous nos services ?</label>
                        <select class="form-select" name="recommendation">
                            <option value="">Sélectionnez une option</option>
                            <option value="certainement">Certainement</option>
                            <option value="probablement">Probablement</option>
                            <option value="peut-etre">Peut-être</option>
                            <option value="probablement-pas">Probablement pas</option>
                            <option value="certainement-pas">Certainement pas</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-button">
                        Envoyer mon avis
                    </button>
                </form>
            </div>
        </div> -->
        <div class="contact-modal" id="contactModal">
            <div class="contact-form">
                <div class="contact-header">
                    <h3>📧 Contactez-nous</h3>
                    <button class="close-contact" onclick="closeContact()">×</button>
                </div>

                <form id="contactForm" onsubmit="submitContactForm(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Prénom <span class="required">*</span></label>
                            <input type="text" class="form-input" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nom <span class="required">*</span></label>
                            <input type="text" class="form-input" name="lastName" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" class="form-input" name="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-input" name="phone">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sujet de votre demande <span class="required">*</span></label>
                        <select class="form-select" name="subject" required>
                            <option value="">Sélectionnez un sujet</option>
                            <option value="info-produit">Information sur un produit</option>
                            <option value="devis">Demande de devis</option>
                            <option value="livraison">Question sur la livraison</option>
                            <option value="sav">Service après-vente</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Votre message <span class="required">*</span></label>
                        <textarea class="form-textarea" name="message" placeholder="Décrivez votre demande en détail..."
                            required></textarea>
                    </div>

                    <button type="submit" class="submit-button">
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
        <div class="chat-modal" id="chatModal">
            <div class="chat-window">
                <div class="chat-header">
                    <h3>Chat en direct</h3>
                    <button class="close-chat" onclick="closeChat()">×</button>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <div class="message bot">
                        <div class="message-content">
                            Bonjour ! Je suis là pour vous aider. Comment puis-je vous renseigner sur nos produits ?
                        </div>
                    </div>
                </div>

                <div class="chat-input">
                    <input type="text" class="message-input" id="messageInput" placeholder="Tapez votre message...">
                    <button class="send-button" onclick="sendMessage()">
                        ➤
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>StackCore</h3>
                    <p>Votre partenaire de confiance pour l'équipement professionnel et les fournitures de bureau.</p>
                    <p><i class="fas fa-phone"></i> +212567980023</p>
                    <p><i class="fas fa-envelope"></i> contact@stackcore.ma</p>
                </div>

                <div class="footer-section">
                    <h3>Catégories</h3>
                    <ul>
                        <li><a href="#">Ordinateurs & IT</a></li>
                        <li><a href="#">Écrans & Affichage</a></li>
                        <li><a href="#">Audio & Vidéo</a></li>
                        <li><a href="#">Périphériques</a></li>
                        <li><a href="#">Réseau</a></li>
                        <li><a href="#">Fournitures Bureau</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="#">Livraison rapide</a></li>
                        <li><a href="#">Support technique</a></li>
                        <li><a href="#">Garantie étendue</a></li>
                        <li><a href="#">Devis personnalisé</a></li>
                        <li><a href="#">Formation</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Compte</h3>
                    <ul>
                        <li><a href="#">Mon compte</a></li>
                        <li><a href="#">Mes commandes</a></li>
                        <li><a href="#">Mes favoris</a></li>
                        <li><a href="#">Support client</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 StackCore. Tous droits réservés. | <a href="#">Mentions légales</a> | <a href="#">CGV</a>
                    | <a href="#">Politique de confidentialité</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Variables globales
        let allProductsData = [];

        // Initialisation
        document.addEventListener('DOMContentLoaded', function () {
            loadCounters();
            loadPromotionProducts();
            loadPopularProducts();
            loadRecentProducts();
            loadAllProducts();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Recherche en temps réel
            document.getElementById('searchInput').addEventListener('input', debounce(searchProducts, 300));

            // Filtres
            document.getElementById('categoryFilter').addEventListener('change', applyFilters);
            document.getElementById('sortBy').addEventListener('change', applyFilters);
            document.getElementById('minPrice').addEventListener('input', debounce(applyFilters, 500));
            document.getElementById('maxPrice').addEventListener('input', debounce(applyFilters, 500));
        }

        // Debounce function pour optimiser les performances
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Charger les compteurs depuis PHP
        async function loadCounters() {
            try {
                const response = await fetch('api_cart_favorites.php?action=get_counters');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('favCount').textContent = data.favorites_count;
                    document.getElementById('cartCount').textContent = data.cart_count;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des compteurs:', error);
            }
        }

        // Charger les images des produits via AJAX
        async function loadProductImages(productIds) {
            if (!productIds || productIds.length === 0) {
                return {};
            }

            try {
                const idsString = productIds.join(',');
                const response = await fetch(
                    `api_product_images.php?action=get_multiple_images&product_ids=${idsString}`);
                const data = await response.json();

                if (data.success) {
                    return data.images;
                } else {
                    console.error('Erreur lors du chargement des images:', data.message);
                    return {};
                }
            } catch (error) {
                console.error('Erreur lors du chargement des images:', error);
                return {};
            }
        }

        // Chargement des produits via AJAX
        async function loadProducts(type, containerId) {
            try {
                const response = await fetch(`api_products.php?type=${type}`);
                const data = await response.json();

                if (data.success) {
                    displayProducts(data.products, containerId);
                    if (type === 'all') {
                        allProductsData = data.products;
                    }
                } else {
                    document.getElementById(containerId).innerHTML = '<p>Erreur lors du chargement des produits.</p>' + data.message;
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById(containerId).innerHTML = '<p>Erreur de connexion.</p>';
            }
        }

        function loadPromotionProducts() {
            loadProducts('promotion', 'promotionProducts');
        }

        function loadPopularProducts() {
            loadProducts('popular', 'popularProducts');
        }

        function loadRecentProducts() {
            loadProducts('recent', 'recentProducts');
        }

        function loadAllProducts() {
            loadProducts('all', 'allProducts');
        }

        // Affichage des produits avec chargement dynamique des images
        async function displayProducts(products, containerId) {
            const container = document.getElementById(containerId);

            if (products.length === 0) {
                container.innerHTML = '<p class="loading">Aucun produit trouvé.</p>';
                return;
            }

            // Récupérer les statuts favoris et panier depuis PHP
            let favoritesStatus = {};
            let cartStatus = {};

            try {
                const response = await fetch('api_cart_favorites.php?action=get_status');
                const data = await response.json();

                if (data.success) {
                    favoritesStatus = data.favorites || {};
                    cartStatus = data.cart || {};
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statuts:', error);
            }

            // Charger les images pour tous les produits
            const productIds = products.map(product => product.id);
            const productImages = await loadProductImages(productIds);

            const productsHtml = products.map(product => {
                const isFavorite = favoritesStatus[product.id] || false;
                const inCart = cartStatus[product.id] || false;
                const productImage = productImages[product.id];

                return `
            <div class="product-card" data-product-id="${product.id}">
                ${product.is_on_promotion == '1' ? '<div class="promotion-badge">PROMO</div>' : ''}
                
                <div class="product-image">
                    ${productImage && productImage.image_url ?
                        `<img src="images/${productImage.image_url}" alt="${product.name}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                         <div class="fallback-icon" style="display:none;"><i class="fas fa-box"></i></div>` :
                        `<div class="fallback-icon"><i class="fas fa-box"></i></div>`
                    }
                </div>
                
                <div class="product-info">
                    <h3 class="product-name">${product.name}</h3>
                    <div class="product-price">
                        ${product.is_on_promotion == '1' ?
                        `${product.promotion_price} Dhs <span class="original-price">${product.price}€</span>` :
                        `${product.price} Dhs`
                    }
                    </div>
                    <div class="product-stock">
                        Stock: ${product.stock} unités
                        ${product.min_order_quantity > 1 ? ` | Min: ${product.min_order_quantity}` : ''}
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn btn-primary" onclick="addToCart(${product.id}, '${product.name}', ${product.is_on_promotion ? product.promotion_price : product.price}, ${product.min_order_quantity || 1})">
                            <i class="fas fa-cart-plus"></i> ${inCart ? 'Ajouté' : 'Panier'}
                        </button>
                        <button class="btn-favorite ${isFavorite ? 'active' : ''}" onclick="toggleFavorite(${product.id})">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
            }).join('');

            container.innerHTML = productsHtml;
        }

        // Recherche de produits
        async function searchProducts() {
            const query = document.getElementById('searchInput').value.trim();

            if (query.length === 0) {
                loadAllProducts();
                return;
            }

            try {
                const response = await fetch(`api_products.php?type=search&query=${encodeURIComponent(query)}`);
                const data = await response.json();

                if (data.success) {
                    displayProducts(data.products, 'allProducts');
                }
            } catch (error) {
                console.error('Erreur de recherche:', error);
            }
        }

        // Application des filtres
        async function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            const sortBy = document.getElementById('sortBy').value;

            const params = new URLSearchParams({
                type: 'filter',
                category: category,
                min_price: minPrice,
                max_price: maxPrice,
                sort_by: sortBy
            });

            try {
                const response = await fetch(`api_products.php?${params}`);
                const data = await response.json();

                if (data.success) {
                    displayProducts(data.products, 'allProducts');
                }
            } catch (error) {
                console.error('Erreur de filtrage:', error);
            }
        }

        // Gestion des favoris via AJAX
        async function toggleFavorite(productId) {
            try {
                const response = await fetch('api_cart_favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'toggle_favorite',
                        product_id: productId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Mise à jour visuelle
                    const button = document.querySelector(`[data-product-id="${productId}"] .btn-favorite`);
                    if (button) {
                        if (data.is_favorite) {
                            button.classList.add('active');
                        } else {
                            button.classList.remove('active');
                        }
                    }

                    // Mise à jour du compteur
                    document.getElementById('favCount').textContent = data.favorites_count;

                    // Afficher un message
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de la modification des favoris', 'error');
                }
            } catch (error) {
                console.error('Erreur AJAX favoris:', error);
                showAlert('Erreur de connexion', 'error');
            }
        }

        // Gestion du panier via AJAX
        async function addToCart(productId, productName, price, minQuantity = 1) {
            try {
                const response = await fetch('api_cart_favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_to_cart',
                        product_id: productId,
                        product_name: productName,
                        price: price,
                        quantity: minQuantity
                    })
                });

                const data = await response.json();
                if (data.success) {
                    // Mise à jour du compteur
                    document.getElementById('cartCount').textContent = data.cart_count;

                    // Feedback visuel
                    const button = document.querySelector(`[data-product-id="${productId}"] .btn-primary`);
                    if (button) {
                        const originalText = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
                        button.style.background = '#27ae60';

                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.style.background = '';
                        }, 2000);
                    }

                    // Afficher un message
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message || 'Erreur lors de l\'ajout au panier', 'error');
                }
            } catch (error) {
                console.error('Erreur AJAX panier:', error);
                showAlert('Erreur de connexion', 'error');
            }
        }

        // Afficher des alertes
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;

            alertContainer.appendChild(alertDiv);

            // Supprimer l'alerte après 5 secondes
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Navigation vers favoris/panier
        function toggleFavorites() {
            // Redirection vers page favoris
            window.location.href = 'favorites.php';
        }

        function toggleCart() {
            // Redirection vers page panier
            window.location.href = 'cart.php';
        }
        // Chat
        let isMenuOpen = false;

        const chatToggle = document.getElementById('chatToggle');
        const supportMenu = document.getElementById('supportMenu');
        const chatModal = document.getElementById('chatModal');
        const contactModal = document.getElementById('contactModal');
        const feedbackModal = document.getElementById('feedbackModal');
        const messageInput = document.getElementById('messageInput');
        const chatMessages = document.getElementById('chatMessages');

        // Toggle du menu support
        chatToggle.addEventListener('click', function () {
            isMenuOpen = !isMenuOpen;

            if (isMenuOpen) {
                supportMenu.classList.add('active');
                chatToggle.classList.add('active');
                chatToggle.classList.remove('bounce');
            } else {
                supportMenu.classList.remove('active');
                chatToggle.classList.remove('active');
            }
        });

        // Fermer le menu en cliquant ailleurs
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.support-widget')) {
                supportMenu.classList.remove('active');
                chatToggle.classList.remove('active');
                isMenuOpen = false;
            }
        });

        // Ouvrir le formulaire de contact
        function showContactForm() {
            contactModal.classList.add('active');
            supportMenu.classList.remove('active');
            chatToggle.classList.remove('active');
            isMenuOpen = false;
        }

        // Fermer le formulaire de contact
        function closeContact() {
            contactModal.classList.remove('active');
        }

        // Soumettre le formulaire de contact
        function submitContactForm(event) {
            event.preventDefault();

            // Animation de chargement
            const submitButton = event.target.querySelector('.submit-button');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Envoi en cours...';
            submitButton.disabled = true;

            // Simuler l'envoi (remplacer par votre logique d'envoi)
            setTimeout(() => {
                alert('Merci pour votre message ! Nous vous répondrons dans les plus brefs délais.');
                closeContact();
                event.target.reset();
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }, 2000);
        }

        // Ouvrir le chat
        function openChat() {
            chatModal.classList.add('active');
            supportMenu.classList.remove('active');
            chatToggle.classList.remove('active');
            isMenuOpen = false;
            messageInput.focus();
        }

        // Fermer le chat
        function closeChat() {
            chatModal.classList.remove('active');
        }

        // Envoyer un message
        function sendMessage() {
            const message = messageInput.value.trim();
            if (message) {
                // Ajouter le message de l'utilisateur
                addMessage(message, 'user');
                messageInput.value = '';

                // Simuler une réponse automatique
                setTimeout(() => {
                    const responses = [
                        "Merci pour votre message ! Un de nos conseillers va vous répondre dans quelques instants.",
                        "Je comprends votre demande. Puis-je avoir plus de détails pour mieux vous aider ?",
                        "Excellente question ! Laissez-moi vous trouver les informations les plus précises.",
                        "Je suis là pour vous aider. Pouvez-vous me préciser le produit qui vous intéresse ?"
                    ];
                    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                    addMessage(randomResponse, 'bot');
                }, 1000);
            }
        }

        // Ajouter un message au chat
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = text;

            if (sender === 'user') {
                contentDiv.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                contentDiv.style.color = 'white';
                contentDiv.style.marginLeft = '40px';
                contentDiv.style.marginRight = '0';
            }

            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Envoyer avec Entrée
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Fermer le modal en cliquant sur le fond
        chatModal.addEventListener('click', function (e) {
            if (e.target === chatModal) {
                closeChat();
            }
        });

        // Fermer le modal de contact en cliquant sur le fond
        contactModal.addEventListener('click', function (e) {
            if (e.target === contactModal) {
                closeContact();
            }
        });

        // Fermer le modal d'avis en cliquant sur le fond
        feedbackModal.addEventListener('click', function (e) {
            if (e.target === feedbackModal) {
                closeFeedback();
            }
        });

        // Gestion du système de notation par étoiles
        let currentRating = 0;
        const stars = document.querySelectorAll('.star');
        const ratingText = document.getElementById('ratingText');
        const ratingValue = document.getElementById('ratingValue');

        const ratingTexts = {
            1: 'Très insatisfait',
            2: 'Insatisfait',
            3: 'Neutre',
            4: 'Satisfait',
            5: 'Très satisfait'
        };

        stars.forEach(star => {
            star.addEventListener('click', function () {
                currentRating = parseInt(this.dataset.rating);
                updateRating();
            });

            star.addEventListener('mouseenter', function () {
                const hoverRating = parseInt(this.dataset.rating);
                highlightStars(hoverRating);
                ratingText.textContent = ratingTexts[hoverRating];
            });
        });

        document.querySelector('.rating-container').addEventListener('mouseleave', function () {
            highlightStars(currentRating);
            ratingText.textContent = currentRating ? ratingTexts[currentRating] : '';
        });

        function updateRating() {
            highlightStars(currentRating);
            ratingText.textContent = ratingTexts[currentRating];
            ratingValue.value = currentRating;
        }

        function highlightStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        function resetRating() {
            currentRating = 0;
            highlightStars(0);
            ratingText.textContent = '';
            ratingValue.value = '';
        }

        // Gestion des catégories d'avis
        const categoryChips = document.querySelectorAll('.category-chip');
        const selectedCategoriesInput = document.getElementById('selectedCategories');
        let selectedCategories = [];

        categoryChips.forEach(chip => {
            chip.addEventListener('click', function () {
                const category = this.dataset.category;

                if (selectedCategories.includes(category)) {
                    selectedCategories = selectedCategories.filter(c => c !== category);
                    this.classList.remove('selected');
                } else {
                    selectedCategories.push(category);
                    this.classList.add('selected');
                }

                selectedCategoriesInput.value = selectedCategories.join(',');
            });
        });

        function resetCategories() {
            selectedCategories = [];
            categoryChips.forEach(chip => chip.classList.remove('selected'));
            selectedCategoriesInput.value = '';
        }

        // Autres fonctions restantes
        // Animation d'attention périodique
        setInterval(() => {
            if (!isMenuOpen && !chatModal.classList.contains('active') && !contactModal.classList.contains(
                'active') && !feedbackModal.classList.contains('active')) {
                chatToggle.classList.add('bounce');
                setTimeout(() => {
                    chatToggle.classList.remove('bounce');
                }, 2000);
            }
        }, 15000);
    </script>
</body>

</html>