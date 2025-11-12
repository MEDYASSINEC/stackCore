<?php
session_start();

// Configuration de la base de données
$host = 'sql203.infinityfree.com';
$dbname = 'if0_39394855_stackcore_db';
$username = 'if0_39394855';
$password = 'b1Zogl8tXJXx';

// En-têtes pour les requêtes AJAX
header('Content-Type: application/json');
require_once 'config/db.php';

// Fonction pour enregistrer une tentative de connexion échouée
function logFailedAttempt($pdo, $email, $ip, $userAgent)
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO failed_login_attempts (email, ip_address, user_agent) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$email, $ip, $userAgent]);
    } catch (Exception $e) {
        // Log silencieux en cas d'erreur
        error_log("Erreur lors de l'enregistrement de la tentative échouée: " . $e->getMessage());
    }
}

// Fonction pour vérifier les tentatives de connexion
function checkFailedAttempts($pdo, $email, $ip)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempts 
        FROM failed_login_attempts 
        WHERE (email = ? OR ip_address = ?) 
        AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$email, $ip]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['attempts'];
}

// Fonction pour enregistrer une connexion réussie
function logSuccessfulLogin($pdo, $userId, $ip, $userAgent)
{
    try {
        // Enregistrer dans user_logins
        $stmt = $pdo->prepare("
            INSERT INTO user_logins (user_id, ip_address, user_agent) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $ip, $userAgent]);

        // Mettre à jour last_login dans users
        $stmt = $pdo->prepare("
            UPDATE users SET last_login = NOW() WHERE id = ?
        ");
        $stmt->execute([$userId]);

    } catch (Exception $e) {
        error_log("Erreur lors de l'enregistrement de la connexion: " . $e->getMessage());
    }
}

// Fonction pour créer une session utilisateur
function createUserSession($pdo, $userId, $ip, $userAgent)
{
    try {
        // Supprimer les anciennes sessions de cet utilisateur
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Créer une nouvelle session
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $sessionToken, $expiresAt, $ip, $userAgent]);

        return $sessionToken;
    } catch (Exception $e) {
        error_log("Erreur lors de la création de la session: " . $e->getMessage());
        return false;
    }
}

// Validation et traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

// Récupération des données
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Validation côté serveur
$errors = [];

if (empty($email)) {
    $errors[] = 'L\'email est requis';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format d\'email invalide';
}

if (empty($password)) {
    $errors[] = 'Le mot de passe est requis';
} elseif (strlen($password) < 3) {
    $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ]);
    exit;
}

// Vérifier les tentatives de connexion échouées
$failedAttempts = checkFailedAttempts($pdo, $email, $ip);
if ($failedAttempts >= 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Trop de tentatives de connexion échouées. Veuillez réessayer dans 15 minutes.'
    ]);
    exit;
}

try {
    // Rechercher l'utilisateur
    $stmt = $pdo->prepare("
        SELECT id, company_name, email, password_hash 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Utilisateur non trouvé
        logFailedAttempt($pdo, $email, $ip, $userAgent);

        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ]);
        exit;
    }

    // Vérifier le mot de passe
    if (!password_verify($password, $user['password_hash'])) {
        // Mot de passe incorrect
        logFailedAttempt($pdo, $email, $ip, $userAgent);

        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ]);
        exit;
    }

    // Connexion réussie
    logSuccessfulLogin($pdo, $user['id'], $ip, $userAgent);

    // Créer une session utilisateur
    $sessionToken = createUserSession($pdo, $user['id'], $ip, $userAgent);

    // Définir les variables de session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['company_name'] = $user['company_name'];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['login_time'] = time();

    // Enregistrer l'activité utilisateur
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_activities (user_id, action, details, ip_address) 
            VALUES (?, 'login', 'Connexion réussie', ?)
        ");
        $stmt->execute([$user['id'], $ip]);
    } catch (Exception $e) {
        // Log silencieux
        error_log("Erreur lors de l'enregistrement de l'activité: " . $e->getMessage());
    }

    // Nettoyer les anciennes tentatives de connexion échouées pour cet utilisateur
    try {
        $stmt = $pdo->prepare("
            DELETE FROM failed_login_attempts 
            WHERE email = ? AND attempted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$email]);
    } catch (Exception $e) {
        error_log("Erreur lors du nettoyage des tentatives échouées: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'company_name' => $user['company_name']
        ],
        'redirect' => 'index.php'
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO lors de la connexion: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne. Veuillez réessayer.'
    ]);
} catch (Exception $e) {
    error_log("Erreur générale lors de la connexion: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne. Veuillez réessayer.'
    ]);
}
?>