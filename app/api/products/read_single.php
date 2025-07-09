<?php
// Headers
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');

// Include necessary files
include_once __DIR__ . '/../../config/Database.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Check if connection was successful
if (!$db) {
    http_response_code(503); // Service Unavailable
    echo json_encode(array('message' => 'Database connection failed.'));
    exit();
}

// Get ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$productId || $productId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Product ID is required and must be a positive integer.'));
    exit();
}

// Product query
$query = 'SELECT id, sku, name, category, supplier_name, item_number, description, cost_price, sell_price, reorder_level, current_quantity, barcode
          FROM products
          WHERE id = :id
          LIMIT 1'; // Ensure only one record is fetched

try {
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        // Ensure numeric types are correctly cast if necessary, though PDO::FETCH_ASSOC usually handles SQLite types well.
        // For explicit casting if needed:
        $product['cost_price'] = (float)$product['cost_price'];
        $product['sell_price'] = (float)$product['sell_price'];
        $product['reorder_level'] = $product['reorder_level'] !== null ? (int)$product['reorder_level'] : null;
        $product['current_quantity'] = (int)$product['current_quantity'];

        http_response_code(200); // OK
        echo json_encode($product);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(array('message' => "Product with ID $productId not found."));
    }
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable
    echo json_encode(
        array(
            'message' => 'Query failed: ' . $e->getMessage()
        )
    );
}

?>
