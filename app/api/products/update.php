<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, PUT'); // Allowing POST as per task, PUT is also common
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

// Check if request method is POST (or PUT, though task specifies POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('message' => 'Only POST or PUT methods are allowed for update. Task specifies POST.'));
    exit;
}

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Validate JSON data
if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Invalid JSON payload: ' . json_last_error_msg()));
    exit;
}

// Ensure ID is provided and is numeric
if (!isset($data->id) || !is_numeric($data->id) || $data->id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Product ID is required and must be a positive integer.'));
    exit;
}
$productId = (int)$data->id;

// --- Further processing (existence check, validation, uniqueness, update) will go here ---

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

// --- Validation, Uniqueness checks, and Update logic will follow ---

$errors = [];
$updateFields = []; // To store validated fields for the UPDATE query

// Validate provided fields
// String fields
$stringFields = ['name', 'sku', 'item_number', 'barcode', 'category', 'supplier_name', 'description'];
foreach ($stringFields as $field) {
    if (isset($data->$field)) {
        $trimmedValue = trim($data->$field);
        if ($trimmedValue === '' && in_array($field, ['name', 'sku', 'item_number', 'barcode'])) { // These cannot be empty if provided for update
            $errors[] = "$field cannot be empty if provided for update.";
        } else {
            $updateFields[$field] = htmlspecialchars(strip_tags($trimmedValue));
        }
    }
}

// Numeric fields
if (isset($data->cost_price)) {
    if (!is_numeric($data->cost_price) || $data->cost_price <= 0) {
        $errors[] = "cost_price must be a numeric value greater than 0.";
    } else {
        $updateFields['cost_price'] = (float)$data->cost_price;
    }
}
if (isset($data->sell_price)) {
    if (!is_numeric($data->sell_price) || $data->sell_price <= 0) {
        $errors[] = "sell_price must be a numeric value greater than 0.";
    } else {
        $updateFields['sell_price'] = (float)$data->sell_price;
    }
}
if (isset($data->current_quantity)) {
    if (!is_numeric($data->current_quantity) || $data->current_quantity < 0) {
        $errors[] = "current_quantity must be a numeric value greater than or equal to 0.";
    } else {
        $updateFields['current_quantity'] = (int)$data->current_quantity;
    }
}
if (isset($data->reorder_level)) {
    if ($data->reorder_level !== null && (!is_numeric($data->reorder_level) || $data->reorder_level < 0)) {
        $errors[] = "reorder_level must be a numeric value greater than or equal to 0 (or null).";
    } else {
         // Allow null or valid number
        $updateFields['reorder_level'] = ($data->reorder_level === null) ? null : (int)$data->reorder_level;
    }
}
// receiving_quantity_placeholder is not in schema, so not validated for update here.

if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(array('errors' => $errors));
    exit;
}

if (empty($updateFields)) {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'No valid fields provided for update.'));
    exit;
}

// --- Uniqueness checks and Update logic will follow ---

// Helper function for uniqueness check (for other records)
function checkIfExistsAnother(PDO $db, string $column, $value, int $currentProductId): bool {
    $query = "SELECT id FROM products WHERE " . $column . " = :value AND id != :current_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':value', $value);
    $stmt->bindParam(':current_id', $currentProductId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

$uniqueFieldsToCheck = ['sku', 'item_number', 'barcode'];
foreach ($uniqueFieldsToCheck as $field) {
    if (isset($updateFields[$field])) { // Check only if this field is being updated
        if (checkIfExistsAnother($db, $field, $updateFields[$field], $productId)) {
            $errors[] = "Another product with this $field ('{$updateFields[$field]}') already exists.";
        }
    }
}

if (!empty($errors)) {
    // Distinguish if these are new errors or appended to previous validation errors
    $onlyUniquenessErrors = true;
    foreach ($errors as $errMsg) {
        if (strpos($errMsg, 'cannot be empty') !== false || strpos($errMsg, 'must be a numeric') !== false) {
            $onlyUniquenessErrors = false;
            break;
        }
    }

    if ($onlyUniquenessErrors) {
        http_response_code(409); // Conflict
    } else {
        // If there were prior validation errors, 400 is already appropriate
        // This block might not be strictly necessary if http_response_code(400) was already called and exited.
        // However, if errors were accumulated, this ensures the right code.
        if (http_response_code() !== 400) { // If not already set by previous validation block
             http_response_code(400); // Default to 400 if mixed errors or unsure
        }
    }
    echo json_encode(array('errors' => $errors));
    exit;
}


// --- Update logic will follow ---

if (empty($updateFields)) {
    // This check was also done before uniqueness checks, but as a safeguard here.
    // Or, if only 'id' was passed and no actual data fields to update.
    http_response_code(200); // OK, but no changes
    echo json_encode(array('message' => 'No fields provided to update or data is identical to existing.'));
    exit;
}

$setClauses = [];
$params = [':id' => $productId];

foreach ($updateFields as $field => $value) {
    $setClauses[] = "$field = :$field";
    $params[":$field"] = $value;
}

$query = "UPDATE products SET " . implode(', ', $setClauses) . " WHERE id = :id";

try {
    $stmt = $db->prepare($query);

    // Bind parameters
    // PDO can often infer types, but explicit binding is safer for some cases (like NULL or INT)
    foreach ($params as $paramName => &$paramValue) { // Pass by reference for bindParam
        if ($paramName === ':id' || $paramName === ':current_quantity' || $paramName === ':reorder_level') {
             // Explicitly bind INT for id, current_quantity. reorder_level can be NULL or INT.
            if ($paramValue === null && $paramName === ':reorder_level') {
                $stmt->bindParam($paramName, $paramValue, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam($paramName, $paramValue, PDO::PARAM_INT);
            }
        } else {
            $stmt->bindParam($paramName, $paramValue); // Default is PDO::PARAM_STR
        }
    }
    unset($paramValue); // Unset reference

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200); // OK
            echo json_encode(array('message' => 'Product updated successfully.'));
        } else {
            http_response_code(200); // OK (or 304 Not Modified, but 200 is simpler)
            echo json_encode(array('message' => 'No changes detected for the product. Values might be identical.'));
        }
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array('message' => 'Product update failed (execute returned false).'));
    }
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable
    echo json_encode(
        array(
            'message' => 'Product update failed due to database error.',
            'error' => $e->getMessage()
        )
    );
}

?>
