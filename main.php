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

$name = $_SESSION['name'];  // Retrieving the user's name

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodiehub";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetching menu items from the database
$menuItems = [];
$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
} else {
    $error = "No menu items found.";
}

// Fetching cart items and their quantities for the logged-in user
$user_id = $_SESSION['user_id'];
$cartItems = [];
$cartSql = "SELECT item_id, quantity FROM cart_items WHERE user_id = ?";
$stmt = $conn->prepare($cartSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartResult = $stmt->get_result();
while ($cartRow = $cartResult->fetch_assoc()) {
    $cartItems[$cartRow['item_id']] = $cartRow['quantity'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodieHub Menu</title>
  <link rel="stylesheet" href="public/css/navbar.css">
  <link rel="stylesheet" href="public/css/main.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    function updateCart(dish_id, quantityChange) {
        $.ajax({
            url: 'add_to_cart.php',
            type: 'POST',
            data: { dish_id: dish_id, quantity: quantityChange }, // Send the change in quantity (+1 or -1)
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert(res.message); // Handle errors, if any
                }
            },
            error: function() {
                alert("An error occurred while updating the cart.");
            }
        });
    }
  </script>
</head>
<body>

<!-- Navbar with session -->
<div class="navbar">
  <div class="logo">FoodieHub</div>
  <div class="nav-buttons">
    <a href="your_orders.php" class="nav-link">Your Orders</a> <!-- Link to "Your Orders" page -->
    <span>Hello, <?= htmlspecialchars($name) ?>!</span>
    <form action="logout.php" method="POST">
      <button type="submit">Logout</button>
    </form>
  </div>
</div>



<!-- Menu Section -->
<div class="menu-container">
  <?php if (!empty($menuItems)): ?>
    <?php foreach ($menuItems as $item): ?>
      <div class="menu-item">
        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['dish_name']) ?>">
        <div class="menu-content">
          <h3>
            <?= htmlspecialchars($item['dish_name']) ?><br>
            <span class="tag <?= $item['type'] === 'non-veg' ? 'non-veg' : '' ?>">
              <?= ucfirst($item['type']) ?>
            </span>
          </h3>
          <div class="price">₹<?= number_format($item['price'], 2) ?></div>
          <div class="description"><?= htmlspecialchars($item['description']) ?></div>

          <?php
          // Check if this item is already in the cart
          $itemInCart = isset($cartItems[$item['id']]) ? $cartItems[$item['id']] : 0;
          ?>

          <div class="cart-quantity">
            <?php if ($itemInCart > 0): ?>
              <button type="button" class="quantity-btn minus" onclick="updateCart(<?= $item['id'] ?>, -1)">-</button>
              <span><?= $itemInCart ?></span>
              <button type="button" class="quantity-btn plus" onclick="updateCart(<?= $item['id'] ?>, 1)">+</button>
            <?php else: ?>
              <button type="button" class="add-btn" onclick="updateCart(<?= $item['id'] ?>, 1)">Add to Cart</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="no-items"><?= $error ?? 'No items available.' ?></div>
  <?php endif; ?>
</div>

<!-- Floating Go to Cart Button -->
<a href="cart.php" class="floating-cart-btn">
  <span class="cart-btn">Go to Cart</span>
</a>

</body>
</html>
