<?php
// Database verification script

$dbFile = 'database.sqlite';
$db = null;

echo "Verifying database: $dbFile\n";

if (!file_exists($dbFile)) {
    echo "Database file '$dbFile' does not exist. Please run setup_database.php first.\n";
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected to the database.\n\n";

    // 1. Check if 'products' table exists
    echo "1. Checking if 'products' table exists...\n";
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products';");
    $tableExists = $stmt->fetchColumn();

    if ($tableExists) {
        echo "   [SUCCESS] Table 'products' exists.\n\n";

        // 2. Verify table schema
        echo "2. Verifying 'products' table schema...\n";
        $stmt = $db->query("PRAGMA table_info(products);");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $expectedSchema = [
            'id' => 'INTEGER',
            'sku' => 'TEXT',
            'name' => 'TEXT',
            'category' => 'TEXT',
            'supplier_name' => 'TEXT',
            'item_number' => 'TEXT',
            'description' => 'TEXT',
            'cost_price' => 'REAL',
            'sell_price' => 'REAL',
            'reorder_level' => 'INTEGER',
            'current_quantity' => 'INTEGER',
            'barcode' => 'TEXT',
        ];

        $schemaMatches = true;
        $actualSchema = [];

        if (count($columns) !== count($expectedSchema)) {
            $schemaMatches = false;
            echo "   [FAILURE] Schema mismatch: Incorrect number of columns. Expected " . count($expectedSchema) . ", found " . count($columns) . ".\n";
        } else {
            foreach ($columns as $column) {
                $actualSchema[$column['name']] = $column['type'];
                echo "   Column: {$column['name']}, Type: {$column['type']}, PK: {$column['pk']}\n";
                if (!isset($expectedSchema[$column['name']])) {
                    echo "      [WARNING] Unexpected column '{$column['name']}' found.\n";
                    // We might not strictly fail here, depending on requirements for extra columns.
                    // For this task, we'll consider it a mismatch if not exactly matching.
                    $schemaMatches = false;
                } elseif (strtoupper($expectedSchema[$column['name']]) !== strtoupper($column['type'])) {
                    echo "      [FAILURE] Type mismatch for column '{$column['name']}'. Expected {$expectedSchema[$column['name']]}, got {$column['type']}.\n";
                    $schemaMatches = false;
                }

                // Check primary key for 'id'
                if ($column['name'] === 'id' && $column['pk'] != 1) {
                    echo "      [FAILURE] Column 'id' is not the primary key as expected.\n";
                    $schemaMatches = false;
                }
            }

            // Check for missing expected columns
            foreach ($expectedSchema as $expectedColName => $expectedColType) {
                if (!isset($actualSchema[$expectedColName])) {
                    echo "   [FAILURE] Expected column '{$expectedColName}' is missing.\n";
                    $schemaMatches = false;
                }
            }
        }

        if ($schemaMatches) {
            echo "\n   [SUCCESS] 'products' table schema matches the specifications.\n";
        } else {
            echo "\n   [FAILURE] 'products' table schema does NOT match the specifications.\n";
            echo "   Expected Schema:\n";
            foreach($expectedSchema as $name => $type) {
                echo "      $name: $type\n";
            }
            echo "   Actual Schema Found:\n";
            foreach($actualSchema as $name => $type) {
                echo "      $name: $type\n";
            }
        }

    } else {
        echo "   [FAILURE] Table 'products' does NOT exist. Please run setup_database.php.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} finally {
    // Close the connection
    $db = null;
    echo "\nVerification script finished.\n";
}

?>
