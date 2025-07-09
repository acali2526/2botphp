<?php

class Database {
    // Database Parameters
    private $dbFile; // Path to the SQLite database file
    private $conn;

    public function __construct() {
        // The path to database.sqlite from the perspective of this file's location.
        // If Database.php is in app/config/, and database.sqlite is in the root,
        // the path is two levels up.
        $this->dbFile = __DIR__ . '/../../database.sqlite';
    }

    // Get Database Connection
    public function connect() {
        $this->conn = null;

        // SQLite DSN
        $dsn = 'sqlite:' . $this->dbFile;

        try {
            $this->conn = new PDO($dsn);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set default fetch mode to associative array for convenience
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Optional: Enable foreign key constraints for SQLite if needed later
            // $this->conn->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            // It's generally better to log errors than to echo them directly in a class like this,
            // but for simplicity in this stage, we'll echo.
            // In a real app, you might throw a custom exception or log to a file.
            echo 'Connection Error: ' . $e->getMessage();
            // Optionally, re-throw the exception if you want the caller to handle it
            // throw $e;
            return null; // Indicate connection failure
        }

        return $this->conn;
    }
}

// Test (can be removed later)
// $db = new Database();
// $connection = $db->connect();
// if ($connection) {
//     echo "Successfully connected to " . realpath($db->dbFile) . "\n";
// } else {
//     echo "Failed to connect to database.\n";
// }

?>
