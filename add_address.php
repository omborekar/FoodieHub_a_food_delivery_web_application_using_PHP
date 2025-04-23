<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = $_POST['fullname'];
$phone = $_POST['phone'];
$address = $_POST['address'];

$conn = new mysqli("localhost", "root", "", "foodiehub");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO addresses (user_id, fullname, phone, address) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $user_id, $fullname, $phone, $address);
$stmt->execute();

$stmt->close();
$conn->close();
header("Location: cart.php");
exit;
?>
