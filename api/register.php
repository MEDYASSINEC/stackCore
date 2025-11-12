<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '../config/database.php';
    include_once '../classes/User.php';

    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Récupérer les données POST
    $data = json_decode(file_get_contents("php://input"));

    if (
        !empty($data->company_name) &&
        !empty($data->email) &&
        !empty($data->password) &&
        !empty($data->confirmPassword)
    ) {

        // Vérifier que les mots de passe correspondent
        if ($data->password !== $data->confirmPassword) {
            http_response_code(400);
            echo json_encode(array("message" => "Les mots de passe ne correspondent pas"));
            exit();
        }

        // Valider le mot de passe
        $password_errors = $user->validatePassword($data->password);
        if (!empty($password_errors)) {
            http_response_code(400);
            echo json_encode(array("message" => implode(", ", $password_errors)));
            exit();
        }

        // Vérifier si l'email existe déjà
        $user->email = $data->email;
        if ($user->emailExists()) {
            http_response_code(400);
            echo json_encode(array("message" => "Cette adresse email est déjà utilisée"));
            exit();
        }

        // Définir les propriétés de l'utilisateur
        $user->company_name = $data->company_name;
        $user->email = $data->email;
        $user->password_hash = password_hash($data->password, PASSWORD_DEFAULT);
        $user->address = isset($data->address) ? $data->address : null;
        $user->phone = isset($data->phone) ? $data->phone : null;

        // Créer l'utilisateur
        if ($user->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Inscription réussie"));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer l'utilisateur"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes"));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
}