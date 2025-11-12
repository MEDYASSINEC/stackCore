<?php
require_once '../config/db.php';

// Toujours renvoyer du JSON
header('Content-Type: application/json');

// Lire les données JSON envoyées par fetch
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email'], $data['password'])) {
    if (!empty($data['email']) && !empty($data['password'])) {
        $requete = "SELECT id, email, company_name FROM users WHERE email = ? and  password_hash = ?";
        $stmt = $pdo->prepare($requete);
        $stmt->execute([$data['email'], $data['password']]);
        $result = $stmt->fetch();
        if ($result) {
            session_start();
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['email'] = $result['email'];
            $_SESSION['company_name'] = ['company_name'];
            if (isset($data['remember']) && $data['remember']) {
                setcookie("emailAuthStC", $data['email']);
                setcookie("passwordStC", $data['password']);
            }
            echo json_encode(['success' => true, 'message' => 'Connexion réussie', 'user' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}