<?php
// config/database.php
class Database
{
    private $host = 'sql203.infinityfree.com';
    private $db_name = 'if0_39394855_stackcore_db';
    private $username = 'if0_39394855';
    private $password = 'b1Zogl8tXJXx';
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// classes/User.php
class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $company_name;
    public $email;
    public $password_hash;
    public $address;
    public $phone;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function emailExists()
    {
        $query = "SELECT id, company_name, email FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->company_name = $row['company_name'];
            return true;
        }
        return false;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET company_name=:company_name, email=:email, password_hash=:password_hash, 
                      address=:address, phone=:phone";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->company_name = htmlspecialchars(strip_tags($this->company_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        // Lier les valeurs
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function validatePassword($password)
    {
        $errors = array();

        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }

        return $errors;
    }
}

// api/register.php

?>