<?php
class Database {
    private $host = "localhost";
    private $db_name = "buku";
    private $username = "bukuuser";
    private $password = "password123";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            echo "Koneksi database error: " . $e->getMessage();
            return null;
        }
    }
}
?> 
