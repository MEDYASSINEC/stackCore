<?php
class Database
{
    private $host = "sql203.infinityfree.com";
    private $db_name = "if0_39394855_stackcore_db";
    private $username = "if0_39394855";
    private $password = "b1Zogl8tXJXx";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>