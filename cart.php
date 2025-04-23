<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodiehub";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Cart Items
$cartItems = [];
$cartSql = "SELECT ci.item_id, ci.quantity, mi.dish_name, mi.price, mi.image_path 
            FROM cart_items ci 
            JOIN menu_items mi ON ci.item_id = mi.id 
            WHERE ci.user_id = ?";
$stmt = $conn->prepare($cartSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}
$stmt->close();

// Fetch Addresses
$addresses = [];
$addrSql = "SELECT * FROM addresses WHERE user_id = ?";
$addrStmt = $conn->prepare($addrSql);
$addrStmt->bind_param("i", $user_id);
$addrStmt->execute();
$addrResult = $addrStmt->get_result();
while ($row = $addrResult->fetch_assoc()) {
    $addresses[] = $row;
}
$addrStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart | FoodieHub</title>
  <link rel="stylesheet" href="public/css/navbar.css">
  <link rel="stylesheet" href="public/css/cart.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    function updateCart(dish_id, quantity) {
        $.post("add_to_cart.php", { dish_id: dish_id, quantity: quantity, redirect: "cart.php" }, function(response) {
            let res = JSON.parse(response);
            if (res.status === "success") {
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }
  </script>

  <style>
    .cart-summary {
        padding: 15px;
        margin-top: 25px;
        background-color: #fafafa;
        border: 1px solid #ddd;
        border-radius: 8px;
        max-width: 500px;
    }
    .cart-summary h3 {
        margin-top: 0;
        font-size: 18px;
    }
    .cart-summary p {
        margin: 8px 0;
        font-size: 15px;
    }
    .cart-summary hr {
        margin: 10px 0;
    }
  </style>

</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <div class="logo">FoodieHub</div>
  <div class="nav-buttons">
    <span>Hello, <?= htmlspecialchars($name) ?>!</span>
    <form action="logout.php" method="POST"><button>Logout</button></form>
  </div>
</div>

<!-- Cart Items -->
<div class="cart-container">
  <h2>Your Cart</h2>
  <?php if (!empty($cartItems)): ?>
    <?php
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $gst_rate = 12; // 12%
        $gst = ($subtotal * $gst_rate) / 100;
        $delivery_charge = 20;
        $total = $subtotal + $gst + $delivery_charge;
    ?>
    <?php foreach ($cartItems as $item): ?>
      <div class="cart-item">
        <img src="<?= htmlspecialchars($item['image_path']) ?>" class="item-image" alt="Image">
        <div class="item-details">
          <h3><?= htmlspecialchars($item['dish_name']) ?></h3>
          <p>Price: ₹<?= number_format($item['price'], 2) ?></p>
          <div class="quantity-controls">
            <button onclick="updateCart(<?= $item['item_id'] ?>, -1)">−</button>
            <span><?= $item['quantity'] ?></span>
            <button onclick="updateCart(<?= $item['item_id'] ?>, 1)">+</button>
          </div>
          <p>Total: ₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Order Summary -->
    <div class="cart-summary">
        <hr>
        <h3>Order Summary</h3>
        <p><strong>Items Subtotal:</strong> ₹<?= number_format($subtotal, 2) ?></p>
        <p><strong>GST (<?= $gst_rate ?>%):</strong> ₹<?= number_format($gst, 2) ?></p>
        <p><strong>Delivery Charges:</strong> ₹<?= number_format($delivery_charge, 2) ?></p>
        <hr>
        <p><strong>Order Total:</strong> ₹<?= number_format($total, 2) ?></p>
    </div>

  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>
</div>

<!-- Address Section -->
<div class="address-section">
  <h2>Select Address</h2>
  <form method="POST" action="place_order.php">
    <?php if (!empty($addresses)): ?>
      <label for="address_id">Select Address</label>
      <select name="address_id" id="address_id" required class="address-dropdown">
        <option value="" disabled selected>Select an address</option>
        <?php foreach ($addresses as $addr): ?>
          <option value="<?= $addr['id'] ?>">
            <?= htmlspecialchars($addr['fullname']) ?> - <?= htmlspecialchars($addr['phone']) ?> (<?= htmlspecialchars($addr['address']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    <?php else: ?>
      <p>No saved addresses. Please add one below.</p>
    <?php endif; ?>
    <button class="place-order-btn">Place Order (COD)</button>
  </form>

  <h2>Add New Address</h2>
  <form method="POST" action="add_address.php" class="new-address-form">
    <input type="text" name="fullname" placeholder="Full Name" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <textarea name="address" placeholder="Complete Address" required></textarea>
    <button class="add-btn">Add Address</button>
  </form>
</div>

</body>
</html>
