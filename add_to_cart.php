<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
    exit;
}

// Get the user ID and dish ID
$user_id = $_SESSION['user_id'];
$dish_id = $_POST['dish_id'];
$quantityChange = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;  // Default to 1 if not set

// Validate the quantity change (only allow +1 or -1)
if ($quantityChange !== 1 && $quantityChange !== -1) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity change']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodiehub";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if item is already in the cart
$checkSql = "SELECT quantity FROM cart_items WHERE user_id = ? AND item_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ii", $user_id, $dish_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If item is already in the cart, update the quantity
    $row = $result->fetch_assoc();
    $newQuantity = $row['quantity'] + $quantityChange;
    if ($newQuantity <= 0) {
        // If quantity becomes 0 or negative, remove the item
        $deleteSql = "DELETE FROM cart_items WHERE user_id = ? AND item_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $user_id, $dish_id);
        $deleteStmt->execute();
        $deleteStmt->close();
    } else {
        // Otherwise, update the quantity
        $updateSql = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND item_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iii", $newQuantity, $user_id, $dish_id);
        $updateStmt->execute();
        $updateStmt->close();
    }
} else {
    // If item not in the cart, insert it
    if ($quantityChange > 0) {
        $insertSql = "INSERT INTO cart_items (user_id, item_id, quantity) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iii", $user_id, $dish_id, $quantityChange);
        $insertStmt->execute();
        $insertStmt->close();
    }
}

$conn->close();

// Return a success message
echo json_encode(['status' => 'success', 'message' => 'Cart updated successfully']);
exit;
?>
