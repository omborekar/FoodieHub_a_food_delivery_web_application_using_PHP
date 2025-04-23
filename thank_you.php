<?php
session_start();

// Check if the user is logged in and their flag is 0 (regular user)
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 0) {
  // Redirect to login page if the user is not logged in or if the flag is 1 (admin)
  header("Location: login.php");
  exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Placed | FoodieHub</title>
  <link rel="stylesheet" href="public/css/navbar.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 80px auto;
      padding: 40px;
      background: white;
      text-align: center;
      border-radius: 12px;
      box-shadow: 0 0 10px #ccc;
    }
    h1 {
      color: #28a745;
    }
    p {
      font-size: 18px;
      margin: 20px 0;
    }
    .btn {
      display: inline-block;
      margin-top: 25px;
      padding: 12px 25px;
      background: #ff4d4d;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-size: 16px;
    }
    .btn:hover {
      background: #e33e3e;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <div class="logo">FoodieHub</div>
  <div class="nav-buttons">
    <span>Hello, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
    <form action="logout.php" method="POST"><button>Logout</button></form>
  </div>
</div>

<!-- Thank You Message -->
<div class="container">
  <h1>🎉 Thank You!</h1>
  <p>Your order has been placed successfully and will be delivered soon.</p>
  <a href="main.php" class="btn">Return to Menu</a>
</div>

</body>
</html>
