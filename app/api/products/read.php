<?php
// Headers
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Content-Type: application/json');

// Include necessary files
include_once __DIR__ . '/../../config/Database.php';
// Later, we would include a Product model here:
// include_once __DIR__ . '/../../models/Product.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Check if connection was successful
if (!$db) {
    // Output error message and exit
    http_response_code(500); // Internal Server Error
    echo json_encode(
        array('message' => 'Database connection failed. Check server logs or Database.php configuration.')
    );
    exit();
}

// Product query (basic example)
// Later, this logic would be in a Product model
$query = 'SELECT id, sku, name, category, supplier_name, item_number, description, cost_price, sell_price, reorder_level, current_quantity, barcode
          FROM products
          ORDER BY id DESC';

try {
    $stmt = $db->prepare($query);
    $stmt->execute();

    $num = $stmt->rowCount();

    // Check if any products
    if ($num > 0) {
        $products_arr = array();
        // $products_arr['data'] = array(); // Optional: if you want to nest products under a 'data' key

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Extract row
            // this will make $row['name'] to $name
            // extract($row); // Not using extract for clarity here

            $product_item = array(
                'id' => $row['id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'category' => $row['category'],
                'supplier_name' => $row['supplier_name'],
                'item_number' => $row['item_number'],
                'description' => $row['description'],
                'cost_price' => (float)$row['cost_price'], // Ensure numeric types are correct
                'sell_price' => (float)$row['sell_price'],
                'reorder_level' => (int)$row['reorder_level'],
                'current_quantity' => (int)$row['current_quantity'],
                'barcode' => $row['barcode']
            );

            // Push to "data"
            // array_push($products_arr['data'], $product_item);
            array_push($products_arr, $product_item); // Pushing directly to the root array
        }

        // Turn to JSON & output
        http_response_code(200); // OK
        echo json_encode($products_arr);

    } else {
        // No Products
        http_response_code(200); // OK, but no content (or 404 if preferred for "resource not found")
        echo json_encode(
            // array('message' => 'No products found.')
            array() // Return an empty array as per common REST practice for empty collections
        );
    }
} catch (PDOException $e) {
    // Error during query execution
    http_response_code(500); // Internal Server Error
    echo json_encode(
        array('message' => 'Query failed: ' . $e->getMessage())
    );
}

?>
