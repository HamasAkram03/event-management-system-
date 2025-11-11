<?php
// Start session for all pages that include this
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection class
class Database {
    private $host = "localhost";        // MySQL host
    private $db_name = "students_events"; // Your database name
    private $username = "root";         // Your MySQL username
    private $password = "";             // Your MySQL password
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }

        return $this->conn;
    }
}

// Create connection
$database = new Database();
$conn = $database->getConnection();
?>
