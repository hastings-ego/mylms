<?php
/**
 * Database Configuration
 * Connects to SQLite database for lightweight, portable deployment.
 */
class Database {
    private $db_path = __DIR__ . "/../database.sqlite";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Create database file if it doesn't exist
            if(!file_exists($this->db_path)) {
                touch($this->db_path);
            }
            
            $this->conn = new PDO("sqlite:" . $this->db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if necessary (Migrations block)
            $this->migrate();
            
        } catch(PDOException $exception) {
            echo "Database Connection Error: " . $exception->getMessage();
        }

        return $this->conn;
    }
    
    private function migrate() {
        // Basic example of auto-migration
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            grade_level VARCHAR(50) DEFAULT 'unspecified',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($query);
    }
}
?>
