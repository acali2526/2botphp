<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
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

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('message' => 'Only POST method is allowed.'));
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

// --- Further processing (validation, uniqueness, insertion) will go here ---

// Server-Side Validation
$errors = [];

// Required fields
$requiredFields = ['name', 'sku', 'item_number', 'barcode', 'cost_price', 'sell_price', 'current_quantity'];
foreach ($requiredFields as $field) {
    if (!isset($data->$field) || (is_string($data->$field) && trim($data->$field) === '')) {
        $errors[] = "$field is required.";
    }
}

// Numeric validations
if (isset($data->cost_price) && (!is_numeric($data->cost_price) || $data->cost_price <= 0)) {
    $errors[] = "cost_price must be a numeric value greater than 0.";
}
if (isset($data->sell_price) && (!is_numeric($data->sell_price) || $data->sell_price <= 0)) {
    $errors[] = "sell_price must be a numeric value greater than 0.";
}
if (isset($data->current_quantity) && (!is_numeric($data->current_quantity) || $data->current_quantity < 0)) {
    $errors[] = "current_quantity must be a numeric value greater than or equal to 0.";
}
if (isset($data->reorder_level) && $data->reorder_level !== null && (!is_numeric($data->reorder_level) || $data->reorder_level < 0)) {
    $errors[] = "reorder_level must be a numeric value greater than or equal to 0.";
}
// Assuming receiving_quantity_placeholder is similar to reorder_level if provided
if (isset($data->receiving_quantity_placeholder) && $data->receiving_quantity_placeholder !== null && (!is_numeric($data->receiving_quantity_placeholder) || $data->receiving_quantity_placeholder < 0)) {
    $errors[] = "receiving_quantity_placeholder must be a numeric value greater than or equal to 0.";
}


if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(array('errors' => $errors));
    exit;
}

// Sanitize and prepare data (example for string fields, numerics are already validated)
$name = htmlspecialchars(strip_tags(trim($data->name)));
$sku = htmlspecialchars(strip_tags(trim($data->sku)));
$item_number = htmlspecialchars(strip_tags(trim($data->item_number)));
$barcode = htmlspecialchars(strip_tags(trim($data->barcode)));

$category = isset($data->category) ? htmlspecialchars(strip_tags(trim($data->category))) : null;
$supplier_name = isset($data->supplier_name) ? htmlspecialchars(strip_tags(trim($data->supplier_name))) : null;
$description = isset($data->description) ? htmlspecialchars(strip_tags(trim($data->description))) : null;

// Numeric fields are used directly after validation
$cost_price = $data->cost_price;
$sell_price = $data->sell_price;
$current_quantity = $data->current_quantity;
$reorder_level = isset($data->reorder_level) && is_numeric($data->reorder_level) ? (int)$data->reorder_level : null;
$receiving_quantity_placeholder = isset($data->receiving_quantity_placeholder) && is_numeric($data->receiving_quantity_placeholder) ? (int)$data->receiving_quantity_placeholder : null;


// --- Uniqueness checks and Insertion logic will follow ---

// Helper function for uniqueness check
function checkIfExists(PDO $db, string $column, $value): bool {
    $query = "SELECT id FROM products WHERE " . $column . " = :value LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':value', $value);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

// Uniqueness Validations
$uniqueChecks = [
    'sku' => $sku,
    'item_number' => $item_number,
    'barcode' => $barcode
];

foreach ($uniqueChecks as $field => $value) {
    if (checkIfExists($db, $field, $value)) {
        $errors[] = "A product with this $field ('$value') already exists.";
    }
}

if (!empty($errors)) {
    // Check if errors array was already populated by previous validation step
    // If so, status code 400 is already set. If new errors are from uniqueness, set 409.
    // For simplicity here, if $errors was empty before uniqueness and now has errors, it's a 409.
    // A more robust error handling might distinguish error types.
    // Let's assume previous validation passed if we reach here and $errors becomes non-empty.
    // Re-checking $errors from the previous validation stage:
    $isValidationError = false;
    foreach($errors as $err) {
        if (strpos($err, 'is required') !== false || strpos($err, 'must be a numeric') !== false) {
            $isValidationError = true;
            break;
        }
    }
    if (!$isValidationError && !empty($errors)) { // Errors are purely from uniqueness checks
         http_response_code(409); // Conflict
    } else if (empty($errors)) {
        // This case should not happen if logic is correct, means no errors at all.
    } else {
         // Errors array already contains validation errors, so 400 is appropriate
         // and was likely set by the previous validation block.
         // If http_response_code() was not set, set it to 400.
         if (http_response_code() !== 400) http_response_code(400);
    }
    echo json_encode(array('errors' => $errors)); // Send all accumulated errors
    exit;
}


// --- Insertion logic will follow ---

$query = "INSERT INTO products (
    name, sku, item_number, barcode, category, supplier_name, description,
    cost_price, sell_price, reorder_level, current_quantity
    -- receiving_quantity_placeholder is not in the original table schema, omitting for now
) VALUES (
    :name, :sku, :item_number, :barcode, :category, :supplier_name, :description,
    :cost_price, :sell_price, :reorder_level, :current_quantity
)";

try {
    $stmt = $db->prepare($query);

    // Bind data
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':item_number', $item_number);
    $stmt->bindParam(':barcode', $barcode);
    $stmt->bindParam(':category', $category); // PDO handles null correctly if $category is null
    $stmt->bindParam(':supplier_name', $supplier_name); // PDO handles null correctly
    $stmt->bindParam(':description', $description); // PDO handles null correctly
    $stmt->bindParam(':cost_price', $cost_price);
    $stmt->bindParam(':sell_price', $sell_price);
    $stmt->bindParam(':reorder_level', $reorder_level, $reorder_level === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':current_quantity', $current_quantity, PDO::PARAM_INT);
    // If receiving_quantity_placeholder were to be added to DB:
    // $stmt->bindParam(':receiving_quantity_placeholder', $receiving_quantity_placeholder, $receiving_quantity_placeholder === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

    if ($stmt->execute()) {
        $lastId = $db->lastInsertId();
        http_response_code(201); // Created
        echo json_encode(
            array(
                'message' => 'Product created successfully.',
                'id' => $lastId
            )
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Product creation failed (execute returned false).')
        );
    }
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable
    echo json_encode(
        array(
            'message' => 'Product creation failed due to database error.',
            'error' => $e->getMessage() // Provide specific error in dev, generic in prod
        )
    );
}

?>
