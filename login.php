<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - StackCore</title>
    <link rel="icon" href="images/logoS.png" type="image/png">
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
        }

        .login-left {
            flex: 1;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(45deg,
                    transparent,
                    transparent 2px,
                    rgba(255, 255, 255, 0.1) 2px,
                    rgba(255, 255, 255, 0.1) 4px);
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .login-left p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-group input::placeholder {
            color: #aab0b6;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #667eea;
            font-size: 1.1rem;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #5a6fd8;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #5a6fd8;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            color: #666;
            font-size: 0.95rem;
        }

        .signup-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #5a6fd8;
            text-decoration: underline;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            display: none;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            20%,
            40%,
            60%,
            80% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }
        }

        .success-message {
            background: #efe;
            color: #363;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #363;
            display: none;
        }

        .loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }

            .login-left {
                padding: 40px 30px;
                text-align: center;
            }

            .login-left h1 {
                font-size: 2rem;
            }

            .login-right {
                padding: 40px 30px;
            }

            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <h1>Bienvenue</h1>
            <p>Connectez-vous à votre espace professionnel pour accéder à notre catalogue de produits, gérer vos
                commandes et bénéficier de tarifs préférentiels.</p>
        </div>

        <div class="login-right">
            <form class="login-form" id="loginForm">
                <h2>Connexion</h2>

                <div class="error-message" id="errorMessage"></div>
                <div class="success-message" id="successMessage"></div>

                <div class="form-group">
                    <label for="email">Adresse email professionnelle</label>
                    <input type="email" id="email" name="email" placeholder="votre.email@entreprise.com"
                        value="<?= $_COOKIE['emailAuthStC'] ?? "" ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="Votre mot de passe"
                            value="<?= $_COOKIE['passwordStC'] ?? "" ?>" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-password" onclick="showForgotPassword()">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">Se connecter</button>

                <div class="signup-link">
                    Pas encore de compte ? <a href="registration.php" onclick="showSignup()">Créer un compte
                        entreprise</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Configuration API
        // const API_BASE_URL = '/api'; // Ajustez selon votre configuration
        let loginAttempts = 0;
        const maxAttempts = 3;

        // Gestion du formulaire de connexion
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            handleLogin();
        });

        async function handleLogin() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            const loginBtn = document.getElementById('loginBtn');

            // Validation basique
            if (!email || !password) {
                showError('Veuillez remplir tous les champs.');
                return;
            }

            if (!isValidEmail(email)) {
                showError('Veuillez entrer une adresse email valide.');
                return;
            }

            // Vérification des tentatives de connexion
            if (loginAttempts >= maxAttempts) {
                showError('Trop de tentatives de connexion. Veuillez réessayer dans quelques minutes.');
                return;
            }

            // Animation de chargement
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;

            try {
                // Appel API pour vérifier les identifiants
                const response = await fetch(`auth/userLogin.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        remember: remember,
                        ip_address: await getClientIP() // Pour l'historique de connexion
                    })
                });

                const data = await response.json();
                console.log(data.success);
                console.log(data.message);

                if (response.ok && data.success) {
                    // Connexion réussie
                    showSuccess('Connexion réussie ! Redirection...');

                    // Stocker les informations de session (remplace localStorage)
                    window.userSession = {
                        user_id: data.user.id,
                        email: data.user.email,
                        company_name: data.user.company_name,
                        loginTime: new Date().toISOString()
                    };

                    setTimeout(() => {
                        // Redirection vers le dashboard
                        window.location.href = 'index.php';
                    }, 1000);

                    loginAttempts = 0;
                } else {
                    // Échec de connexion
                    loginAttempts++;
                    const remainingAttempts = maxAttempts - loginAttempts;

                    if (data.message) {
                        showError(data.message + (remainingAttempts > 0 ?
                            ` ${remainingAttempts} tentatives restantes.` : ''));
                    } else {
                        showError(`Email ou mot de passe incorrect. ${remainingAttempts} tentatives restantes.`);
                    }
                }
            } catch (error) {
                console.error('Erreur de connexion:', error);
                showError('Erreur de connexion. Veuillez réessayer.');
            } finally {
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
            }
        }

        // Fonction pour obtenir l'IP du client (simplifié)
        async function getClientIP() {
            try {
                const response = await fetch('https://api.ipify.org?format=json');
                const data = await response.json();
                return data.ip;
            } catch (error) {
                return 'unknown';
            }
        }

        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            successDiv.style.display = 'none';
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';

            // Auto-hide après 5 secondes
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            errorDiv.style.display = 'none';
            successDiv.textContent = message;
            successDiv.style.display = 'block';
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showForgotPassword() {
            alert(
                'Fonctionnalité de récupération de mot de passe à implémenter.\n\nEn général, cela enverrait un email de réinitialisation.');
        }

        function showSignup() {
            alert('Redirection vers la page d\'inscription entreprise à implémenter.');
        }

        // Animation au chargement de la page
        window.addEventListener('load', function () {
            document.querySelector('.login-container').style.opacity = '1';
            document.querySelector('.login-container').style.transform = 'translateY(0)';
        });

        // Gestion de la touche Entrée
        document.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && !e.target.closest('.login-btn')) {
                handleLogin();
            }
        });

        // Initialisation
        console.log('Page de connexion chargée');
        console.log('API Backend requis pour la vérification des utilisateurs');

        // Gestion de la déconnexion automatique si session expirée
        window.addEventListener('beforeunload', function () {
            if (window.userSession && !document.getElementById('remember').checked) {
                // Nettoyer la session si "se souvenir de moi" n'est pas coché
                delete window.userSession;
            }
        });
    </script>
</body>

</html>