<?php
// Database setup script

$dbFile = 'database.sqlite';
$db = null;

try {
    $db = new PDO('sqlite:' . $dbFile);
    // Set error mode to exceptions
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected to the database: $dbFile\n";
} catch (PDOException $e) {
    echo "Failed to connect to the database: " . $e->getMessage() . "\n";
    exit; // Exit if connection fails
}

// Further database operations will go here

    // SQL to create the products table
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sku TEXT,
        name TEXT,
        category TEXT,
        supplier_name TEXT,
        item_number TEXT,
        description TEXT,
        cost_price REAL,
        sell_price REAL,
        reorder_level INTEGER,
        current_quantity INTEGER,
        barcode TEXT
    );";

    try {
        $db->exec($createTableSQL);
        echo "Table 'products' created successfully or already exists.\n";
    } catch (PDOException $e) {
        echo "Error creating table 'products': " . $e->getMessage() . "\n";
        exit; // Exit if table creation fails
    }

} catch (PDOException $e) {
    echo "Failed to connect to the database: " . $e->getMessage() . "\n";
    exit; // Exit if connection fails
}

// Close the connection (optional for SQLite, as it closes when script ends)
// $db = null;

?>
