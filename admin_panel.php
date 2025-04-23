<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "foodiehub");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";

// Handle Out for Delivery
if (isset($_GET['out_for_delivery_order_id'])) {
    $orderId = $_GET['out_for_delivery_order_id'];

    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE orders SET status = 'Out for Delivery' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $conn->commit();
        $msg = "Order status updated to 'Out for Delivery'.";
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "Error updating the order: " . $e->getMessage();
    }
}

// Handle Delivered Order
if (isset($_GET['delivered_order_id'])) {
    $orderId = $_GET['delivered_order_id'];

    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE orders SET status = 'Delivered' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $conn->commit();
        $msg = "Order marked as delivered.";
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "Error updating the order: " . $e->getMessage();
    }
}

// Fetch orders
$stmt = $conn->prepare("
    SELECT orders.id, orders.user_id, orders.total_amount, orders.payment_method, orders.created_at, orders.status, users.name 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    WHERE orders.status IN ('pending', 'Out for Delivery')
    ORDER BY orders.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Orders</title>
  <link rel="stylesheet" href="public/css/navbar.css">
  <link rel="stylesheet" href="public/css/admin_panel.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    /* Your existing styles... */
  </style>
  <script>
    function toggleItems(orderId) {
      const itemRow = document.getElementById("items-" + orderId);
      if (itemRow.style.display === "none" || itemRow.style.display === "") {
        itemRow.style.display = "table-row";
      } else {
        itemRow.style.display = "none";
      }
    }

    function printReceipt(orderId) {
      window.open("print_receipt.php?order_id=" + orderId, "_blank");
    }
  </script>
</head>
<body>
  <div class="navbar">
    <div class="logo">FoodieHub Admin</div>
    
    <div class="nav-buttons">
    <div class="center">
      <?php if (isset($_SESSION['name'])): ?>
        Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
      <?php endif; ?>
    </div>
      <button onclick="window.location.href='manage_menu.php'">Manage Menu</button>
      <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </div>

  <div class="container">
    <h2>Received Orders</h2>

    <?php if (!empty($msg)): ?>
      <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Total Amount</th>
          <th>Payment</th>
          <th>Order Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td>
              <?= $row['id'] ?>
              <button class="toggle-btn" onclick="toggleItems(<?= $row['id'] ?>)">View Items</button>
            </td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <?php
    $baseAmount = $row['total_amount'];
    $gst = $baseAmount * 0.12; // 12% GST
    $deliveryCharge = 20;
    $finalAmount = $baseAmount + $gst + $deliveryCharge;
?>
<td>₹<?= number_format($finalAmount, 2) ?> <br><small>(incl. GST & delivery)</small></td>

            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <?php if ($row['status'] === 'pending'): ?>
                <button class="action-btn print-btn" onclick="printReceipt(<?= $row['id'] ?>)">Print Receipt</button>
                <a href="admin_panel.php?out_for_delivery_order_id=<?= $row['id'] ?>" class="action-btn out-for-delivery-btn">Out for Delivery</a>
              <?php elseif ($row['status'] === 'Out for Delivery'): ?>
                <button class="action-btn delivered-btn" onclick="window.location.href='admin_panel.php?delivered_order_id=<?= $row['id'] ?>'">Delivered</button>
              <?php endif; ?>
            </td>
          </tr>

          <tr id="items-<?= $row['id'] ?>" class="order-items">
            <td colspan="7">
              <strong>Items in this order:</strong>
              <ul>
                <?php
                  // Query to fetch items in the order
                  $itemStmt = $conn->prepare("
                    SELECT m.dish_name, oi.quantity, oi.price 
                    FROM order_items oi 
                    JOIN menu_items m ON oi.item_id = m.id 
                    WHERE oi.order_id = ?
                  ");
                  $itemStmt->bind_param("i", $row['id']);
                  $itemStmt->execute();
                  $itemResult = $itemStmt->get_result();

                  if ($itemResult->num_rows > 0):
                    while ($item = $itemResult->fetch_assoc()):
                ?>
                      <li><?= htmlspecialchars($item['dish_name']) ?> (x<?= $item['quantity'] ?>) – ₹<?= number_format($item['price'], 2) ?></li>
                <?php
                    endwhile;
                  else:
                ?>
                    <li>No items found for this order.</li>
                <?php endif; ?>
              </ul>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
