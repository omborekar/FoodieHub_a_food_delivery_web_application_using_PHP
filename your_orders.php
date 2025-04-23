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

$user_id = $_SESSION['user_id'];  // Retrieving the logged-in user's ID

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodiehub";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetching all orders placed by the user, ordered by creation time
$sql = "SELECT o.id, o.total_amount, o.payment_method, o.created_at, TRIM(o.status) AS status 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";  // Fetch all orders, sorted by creation date
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orderResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/your_orders.css">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>

<!-- Navbar with session -->
<div class="navbar">
  <div class="logo">FoodieHub</div>
  <div class="nav-buttons">
    <span>Hello, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
    <a href="index.php" class="nav-link">Home</a> <!-- Link to Home page -->
    <form action="logout.php" method="POST">
      <button type="submit">Logout</button>
    </form>
  </div>
</div>

<!-- Orders Section -->
<div class="orders-container">
    <h2>Your Orders</h2>

    <!-- Orders List -->
    <div class="orders-list">
        <?php 
        $orderCount = 0; // Count the number of orders
        while ($order = $orderResult->fetch_assoc()): 
            $orderCount++;
        ?>
            <div class="order-item">
                <p><strong>Order ID:</strong> <?= $order['id'] ?></p>
                <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
                
                <!-- Debugging: Print the raw status value -->
                <p><strong>Raw Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                
                <p><strong>Status:</strong> 
                    <?php
                        // Show status based on order status (case-insensitive check)
                        switch (strtolower($order['status'])) {  // Convert status to lowercase for case-insensitive comparison
                            case 'pending':
                                echo 'In Progress';
                                break;
                            case 'out for delivery':
                                echo 'On the Way';
                                break;
                            case 'delivered':
                                echo 'Delivered';
                                break;
                            default:
                                echo 'Unknown'; // Handle any unexpected status
                        }
                    ?>
                </p>
                
                <?php
    $baseAmount = $order['total_amount'];
    $gst = $baseAmount * 0.12; // 12% GST
    $delivery = 20;
    $finalAmount = $baseAmount + $gst + $delivery;
?>
<p><strong>Total Amount (incl. 12% GST + ₹20 delivery):</strong> ₹<?= number_format($finalAmount, 2) ?></p>
                 <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
                <a href="view_order.php?order_id=<?= $order['id'] ?>" class="view-button">View Order</a>
            </div>
        <?php endwhile; ?>
        <?php if ($orderCount == 0): ?>
            <p>No orders found.</p> <!-- Display message if no orders are found -->
        <?php endif; ?>
    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
