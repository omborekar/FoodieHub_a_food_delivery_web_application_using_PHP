<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Gmail SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'User_Mail');  // Replace with your Gmail
define('SMTP_PASS', 'User_pass'); // Replace with your App Password

// DB connection
$conn = new mysqli("localhost", "root", "", "foodiehub");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = ""; // For showing messages
$secretCode = "adminSecret123"; // The secret code for admin registration

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role']; // User or Admin
    $enteredSecretCode = isset($_POST['secretCode']) ? trim($_POST['secretCode']) : '';

    try {
        // Validate password match
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match.");
        }

        // Admin validation
        if ($role == "admin" && $enteredSecretCode !== $secretCode) {
            throw new Exception("Invalid secret code for admin registration.");
        }

        // OTP Generation and Email
        $otp = rand(1000, 9999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['password'] = $password;
        $_SESSION['role'] = $role;

        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("Email address is already registered.");
        }

        // Send OTP
        if (!sendOtp($email, $otp)) {
            throw new Exception("Failed to send OTP. Please try again.");
        }

        header("Location: signup.php?otp=true");
        exit;

    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

// Handle OTP
if (isset($_POST['otp'])) {
    $otpEntered = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'];
    try {
        if ($otpEntered == $_SESSION['otp']) {
            $name = $_SESSION['name'];
            $email = $_SESSION['email'];
            $passwordHash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);
            $role = $_SESSION['role'] == "admin" ? 1 : 0; // 1 for admin, 0 for user

            // Insert user into the database
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $email, $passwordHash, $role);
            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                header("Location: main.php");
                exit;
            } else {
                throw new Exception("Error registering user.");
            }
        } else {
            throw new Exception("Invalid OTP.");
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

function sendOtp($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Set the sender and recipient
        $mail->setFrom(SMTP_USER, 'FoodieHub');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is: <strong>$otp</strong>";

        // Send email
        $mail->send();
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="public/css/navbar.css">
  <link rel="stylesheet" href="public/css/signup.css">
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
    <?php if (!isset($_GET['otp'])): ?>
      <h2>Create Account</h2>
      <?php if (!empty($msg)): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
      
      <form method="post" id="signupForm">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirmPassword" placeholder="Confirm Password" required>

        <label for="role">Select Role:</label>
        <div class="role-section">
          <input type="radio" name="role" value="user" id="role_user" checked> 
          <label for="role_user">User</label>
          
          <input type="radio" name="role" value="admin" id="role_admin">
          <label for="role_admin">Admin</label>
        </div>

        <div id="adminCode" style="display: none;">
          <label for="secretCode">Enter Secret Code for Admin Registration:</label>
          <input type="text" name="secretCode" placeholder="Secret Code">
        </div>

        <button type="submit" name="signup">Register</button>
      </form>

      <script>
        // Show the secret code input if admin is selected
        document.querySelectorAll('input[name="role"]').forEach(radio => {
          radio.addEventListener('change', function() {
            if (this.value === 'admin') {
              document.getElementById('adminCode').style.display = 'block';
            } else {
              document.getElementById('adminCode').style.display = 'none';
            }
          });
        });
      </script>
      
    <?php else: ?>
      <h3>Verify OTP</h3>
      <?php if (!empty($msg)): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
      <p style="text-align: center;">We sent a 4-digit OTP to your email.</p>
      <form method="post" id="otpForm">
        <div class="otp-inputs">
          <input type="text" name="otp1" maxlength="1" required oninput="moveFocus(this, 'otp2')">
          <input type="text" name="otp2" maxlength="1" required oninput="moveFocus(this, 'otp3')">
          <input type="text" name="otp3" maxlength="1" required oninput="moveFocus(this, 'otp4')">
          <input type="text" name="otp4" maxlength="1" required>
        </div>
        <button type="submit" name="otp">Verify</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    function moveFocus(current, nextId) {
      if (current.value.length === 1) {
        const next = document.getElementsByName(nextId)[0];
        if (next) next.focus();
      }
    }
  </script>
</body>
</html>
