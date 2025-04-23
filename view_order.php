<?php 
session_start();

// Check if the user is logged in and their flag is 0 (regular user)
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 0) {
    // Redirect to login page if the user is not logged in or if the flag is 1 (admin)
    header("Location: login.php");
    exit;
}
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "foodiehub";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Fetch order details including address info
    $sql = "
        SELECT o.id, o.total_amount, o.payment_method, o.created_at, o.status, u.name as user_name, o.address_id,
               a.address as full_address
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN addresses a ON o.address_id = a.id
        WHERE o.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $order = $orderResult->fetch_assoc();

    // If no order is found
    if (!$order) {
        echo "Order not found.";
        exit;
    }

    // Fetch order items
    $itemStmt = $conn->prepare("SELECT m.dish_name, oi.quantity, oi.price FROM order_items oi JOIN menu_items m ON oi.item_id = m.id WHERE oi.order_id = ?");
    $itemStmt->bind_param("i", $orderId);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    // Calculate order charges
    $gst = 0.12 * $order['total_amount']; // 12% GST
    $deliveryCharge = 20; // Fixed delivery charge
    $totalAmountWithCharges = $order['total_amount'] + $gst + $deliveryCharge;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/view_order.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>

<!-- Navbar with session -->
<div class="navbar">
  <div class="logo">FoodieHub</div>
  <div class="nav-buttons">
    <span>Hello, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
    <a href="your_orders.php" class="nav-link">Your Orders</a>
    <form action="logout.php" method="POST">
      <button type="submit">Logout</button>
    </form>
  </div>
</div>

<!-- Order Details Section -->
<div class="order-details-container">
    <h2>Order Details - #<?= $order['id'] ?></h2>
    <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
    <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
    <p><strong>Total Amount:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
    <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method']) ?></p>

    <!-- Displaying Customer's Address -->
    <h3>Delivery Address:</h3>
    <?php if ($order['address_id']): ?>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['full_address']) ?></p>
    <?php else: ?>
        <p>No address found for this order.</p>
    <?php endif; ?>

    <h3>Ordered Items:</h3>
    <table>
        <thead>
            <tr>
                <th>Dish Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalItemsAmount = 0;
            while ($item = $itemResult->fetch_assoc()): 
                $itemTotal = $item['quantity'] * $item['price'];
                $totalItemsAmount += $itemTotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['dish_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₹<?= number_format($item['price'], 2) ?></td>
                    <td>₹<?= number_format($itemTotal, 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Order Charges:</h3>
    <p><strong>Subtotal:</strong> ₹<?= number_format($totalItemsAmount, 2) ?></p>
    <p><strong>GST (12%):</strong> ₹<?= number_format($gst, 2) ?></p>
    <p><strong>Delivery Charge:</strong> ₹<?= number_format($deliveryCharge, 2) ?></p>
    <p><strong>Total Amount:</strong> ₹<?= number_format($totalAmountWithCharges, 2) ?></p>
</div>

</body>
</html>

<?php
} else {
    echo "Invalid Order ID.";
}
?>
