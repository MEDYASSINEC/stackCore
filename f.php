<?php
// config/Database.php
class Database
{
    private $host = 'sql203.infinityfree.com';
    private $dbName = 'if0_39394855_stackcore_db';
    private $username = 'if0_39394855';
    private $password = 'b1Zogl8tXJXx';
    private $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbName};charset=utf8",
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            // Avoid exposing details in production
            error_log("Database connection error: " . $e->getMessage());
            echo "Database connection failed.";
        }

        return $this->conn;
    }
}
?>

<?php
// classes/User.php
class User
{
    private $conn;
    private $tableName = "users";

    public $id;
    public $companyName;
    public $email;
    public $passwordHash;
    public $address;
    public $phone;
    public $createdAt;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function emailExists()
    {
        $query = "SELECT id, company_name, email FROM {$this->tableName} WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->companyName = $row['company_name'];
            return true;
        }

        return false;
    }

    public function create()
    {
        $query = "INSERT INTO {$this->tableName} 
                  (company_name, email, password_hash, address, phone)
                  VALUES (:company_name, :email, :password_hash, :address, :phone)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->companyName = htmlspecialchars(strip_tags($this->companyName));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->passwordHash = htmlspecialchars(strip_tags($this->passwordHash));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        // Bind values
        $stmt->bindParam(":company_name", $this->companyName);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->passwordHash);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);

        return $stmt->execute();
    }

    public function validatePassword($password)
    {
        $errors = [];

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
?>