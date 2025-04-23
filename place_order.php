<?php
session_start();


// Check if the user is logged in and their flag is 0 (regular user)
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 0) {
    // Redirect to login page if the user is not logged in or if the flag is 1 (admin)
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_POST['address_id'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = $_POST['address_id'];

$conn = new mysqli("localhost", "root", "", "foodiehub");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cart items
$cart_sql = "SELECT ci.item_id, ci.quantity, mi.price FROM cart_items ci JOIN menu_items mi ON ci.item_id = mi.id WHERE ci.user_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

// Insert order
$order_sql = "INSERT INTO orders (user_id, address_id, total_amount) VALUES (?, ?, ?)";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("iid", $user_id, $address_id, $total);
$stmt->execute();
$order_id = $stmt->insert_id;

// Insert order items
foreach ($items as $item) {
    $item_sql = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($item_sql);
    $stmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
    $stmt->execute();
}

// Clear cart
$del_sql = "DELETE FROM cart_items WHERE user_id = ?";
$stmt = $conn->prepare($del_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$conn->close();
header("Location: thank_you.php");
exit;
?>
