<?php
session_start();
$conn = new mysqli("localhost", "root", "", "foodiehub");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Include the is_admin field in the query
    $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);  // Corrected line
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashedPassword, $is_admin);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['is_admin'] = $is_admin;

            // Redirect to the appropriate page based on admin status
            if ($is_admin) {
                header("Location: admin_panel.php"); // Admin panel
            } else {
                header("Location: main.php"); // Regular user dashboard
            }
            exit;
        } else {
            $msg = "Incorrect password.";
        }
    } else {
        $msg = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
  <link rel="stylesheet" href="public/css/navbar.css">
  <link rel="stylesheet" href="public/css/login.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">FoodieHub</div>
        <div class="nav-buttons">
            <button onclick="window.location.href='login.php'">Login</button>
            <button onclick="window.location.href='signup.php'">Signup</button>
        </div>
    </div>
    
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($msg)): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
    </div>
</body>
</html>
