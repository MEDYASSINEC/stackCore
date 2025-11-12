<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - E-commerce B2B</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .registration-container:hover {
            transform: translateY(-5px);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            background: #e1e5e9;
            border-radius: 2px;
            margin: 5px 0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak {
            background: #ff4757;
            width: 25%;
        }

        .strength-fair {
            background: #ffa502;
            width: 50%;
        }

        .strength-good {
            background: #2ed573;
            width: 75%;
        }

        .strength-strong {
            background: #20bf6b;
            width: 100%;
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .register-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #ffe6e6;
            color: #d63031;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
        }

        .success-message {
            background: #e6f7e6;
            color: #00b894;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .registration-container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="registration-container">
        <div class="logo">
            <h1>StackCore</h1>
            <p>Inscription Entreprise</p>
        </div>

        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <form id="registrationForm">
            <div class="form-group">
                <label for="companyName">Nom de l'entreprise *</label>
                <input type="text" id="companyName" name="companyName" required>
            </div>

            <div class="form-group">
                <label for="email">Email professionnel *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" id="password" name="password" required>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span id="strengthText">Saisissez un mot de passe</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirmer le mot de passe *</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone" placeholder="+33 1 23 45 67 89">
            </div>

            <div class="form-group">
                <label for="address">Adresse de l'entreprise</label>
                <textarea id="address" name="address" placeholder="Adresse complète de votre entreprise"></textarea>
            </div>

            <button type="submit" class="register-btn" id="registerBtn">
                Créer mon compte
            </button>
        </form>

        <div class="login-link">
            Déjà inscrit ? <a href="login.php">Se connecter</a>
        </div>
    </div>

    <script>
        // Validation de la force du mot de passe
        function checkPasswordStrength(password) {
            let strength = 0;
            let text = '';
            const strengthFill = document.getElementById('strengthFill');

            if (password.length >= 8) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;

            strengthFill.className = 'strength-fill';

            switch (strength) {
                case 0:
                case 1:
                    strengthFill.classList.add('strength-weak');
                    text = 'Très faible';
                    break;
                case 2:
                    strengthFill.classList.add('strength-weak');
                    text = 'Faible';
                    break;
                case 3:
                    strengthFill.classList.add('strength-fair');
                    text = 'Moyen';
                    break;
                case 4:
                    strengthFill.classList.add('strength-good');
                    text = 'Bon';
                    break;
                case 5:
                    strengthFill.classList.add('strength-strong');
                    text = 'Très fort';
                    break;
            }

            document.getElementById('strengthText').textContent = text;
            return strength;
        }

        // Événements
        document.getElementById('password').addEventListener('input', function () {
            checkPasswordStrength(this.value);
        });

        document.getElementById('registrationForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                company_name: formData.get('companyName'),
                email: formData.get('email'),
                password: formData.get('password'),
                confirmPassword: formData.get('confirmPassword'),
                phone: formData.get('phone'),
                address: formData.get('address')
            };

            // Validation côté client
            if (data.password !== data.confirmPassword) {
                showError('Les mots de passe ne correspondent pas');
                return;
            }

            if (checkPasswordStrength(data.password) < 3) {
                showError(
                    'Le mot de passe doit être plus fort (au moins 8 caractères avec majuscules, minuscules et chiffres)'
                );
                return;
            }

            // Désactiver le bouton et afficher le loading
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span>Inscription en cours...';

            try {
                const response = await fetch('api/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    showSuccess(result.message + ' Vous pouvez maintenant vous connecter.');
                    document.getElementById('registrationForm').reset();
                    document.getElementById('strengthFill').className = 'strength-fill';
                    document.getElementById('strengthText').textContent = 'Saisissez un mot de passe';
                } else {
                    showError(result.message);
                }

            } catch (error) {
                showError('Erreur de connexion. Veuillez réessayer.');
                console.error('Erreur:', error);
            }

            // Réactiver le bouton
            btn.disabled = false;
            btn.innerHTML = 'Créer mon compte';
        });

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            document.getElementById('successMessage').style.display = 'none';

            // Scroll vers le message d'erreur
            errorDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            document.getElementById('errorMessage').style.display = 'none';

            // Scroll vers le message de succès
            successDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    </script>
</body>

</html>