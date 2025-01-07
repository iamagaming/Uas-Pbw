<?php
class MySQL {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'novel_budiono';
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->database",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Create users table if not exists
function createUsersTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    try {
        $conn->exec($sql);
    } catch(PDOException $e) {
        echo "Error creating table: " . $e->getMessage();
    }
}

// Initialize database and tables
$mysql = new MySQL();
$conn = $mysql->getConnection();
createUsersTable($conn);
?> 