<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
// Task allows ID via GET param or POST payload. DELETE method is also conventional.
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');

// Include necessary files
include_once __DIR__ . '/../../config/Database.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Check database connection
if (!$db) {
    http_response_code(503); // Service Unavailable
    echo json_encode(array('message' => 'Database connection failed.'));
    exit;
}

$productId = null;
$requestMethod = $_SERVER['REQUEST_METHOD'];
$allowedMethods = ['GET', 'POST', 'DELETE'];

if (!in_array($requestMethod, $allowedMethods)) {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('message' => 'HTTP method not allowed for this endpoint. Please use GET, POST, or DELETE.'));
    exit;
}

// Determine Product ID based on the (now validated) request method
if ($requestMethod === 'GET' || ($requestMethod === 'DELETE' && empty(file_get_contents("php://input")))) {
    // For GET, or for DELETE if it has no body (implying ID in URL)
    if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
        $productId = (int)$_GET['id'];
    }
} elseif ($requestMethod === 'POST' || ($requestMethod === 'DELETE' && !empty(file_get_contents("php://input")))) {
    // For POST, or for DELETE if it has a body
    // Re-read contents as it might have been consumed by empty() check if not careful,
    // but for this structure, it's okay. A more robust way is to read once.
    $raw_input = file_get_contents("php://input");
    if (!empty($raw_input)) {
        $data = json_decode($raw_input);
        if (json_last_error() === JSON_ERROR_NONE && isset($data->id) && is_numeric($data->id) && $data->id > 0) {
            $productId = (int)$data->id;
        } else if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Bad Request
            echo json_encode(array('message' => 'Invalid JSON payload: ' . json_last_error_msg()));
            exit;
        }
    }
}


if ($productId === null) {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Product ID is required and must be a positive integer. For GET/DELETE, use "id" query parameter. For POST/DELETE (with body), use {"id": value} in JSON payload.'));
    exit;
}

// --- Verify existence, execute delete, and respond will go here ---

// Verify Product Existence
$checkExistenceQuery = "SELECT id FROM products WHERE id = :id LIMIT 1";
$stmtCheck = $db->prepare($checkExistenceQuery);
$stmtCheck->bindParam(':id', $productId, PDO::PARAM_INT);
$stmtCheck->execute();

if ($stmtCheck->rowCount() == 0) {
    http_response_code(404); // Not Found
    echo json_encode(array('message' => "Product with ID $productId not found."));
    exit;
}

// --- Execute delete and respond will go here ---

$deleteQuery = "DELETE FROM products WHERE id = :id";

try {
    $stmt = $db->prepare($deleteQuery);
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            // Product successfully deleted
            http_response_code(200); // OK
            echo json_encode(array('message' => 'Product deleted successfully.'));
        } else {
            // Product not found (e.g., deleted by another request between check and delete)
            // This case should be rare given the prior existence check, but good to handle.
            http_response_code(404); // Not Found
            echo json_encode(array('message' => 'Product not found or already deleted.'));
        }
    } else {
        // Execution failed for some other reason
        http_response_code(503); // Service Unavailable
        echo json_encode(array('message' => 'Product deletion failed (execute returned false).'));
    }
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable
    echo json_encode(
        array(
            'message' => 'Product deletion failed due to database error.',
            'error' => $e->getMessage() // Provide specific error in dev, generic in prod
        )
    );
}

// Final check for unsupported methods (if not GET, POST, DELETE handled above)
$allowedMethods = ['GET', 'POST', 'DELETE'];
if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('message' => 'HTTP method not allowed for this endpoint.'));
}
?>
