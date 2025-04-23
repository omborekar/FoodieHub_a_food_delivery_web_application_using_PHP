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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_menu.php");
    exit;
}

$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['dish_name'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $desc = $_POST['description'];
    $image_path = $_POST['image_path'];

    $stmt = $conn->prepare("UPDATE menu_items SET dish_name = ?, price = ?, type = ?, description = ?, image_path = ? WHERE id = ?");
    $stmt->bind_param("sdsssi", $name, $price, $type, $desc, $image_path, $id);
    if ($stmt->execute()) {
        $msg = "Item updated successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
}

// Get existing item
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
if (!$item) {
    die("Menu item not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu Item</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/edit_menu_item.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">


</head>
<body>

    <!-- Nav Bar -->
    <div class="navbar">
        <div class="logo">FoodieHub Admin</div>
        <div class="nav-buttons">
            <span style="margin-right: 20px;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
            <button onclick="window.location.href='admin_panel.php'">Orders</button>
            <button onclick="window.location.href='manage_menu.php'">Manage Menu</button>
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>

    <div class="form-container">
        <h2>Edit Menu Item</h2>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Dish Name:</label>
            <input type="text" name="dish_name" value="<?= htmlspecialchars($item['dish_name']) ?>" required>

            <label>Price (₹):</label>
            <input type="text" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>

            <label>Type:</label>
            <select name="type" required>
                <option value="veg" <?= $item['type'] == 'veg' ? 'selected' : '' ?>>Veg</option>
                <option value="non-veg" <?= $item['type'] == 'non-veg' ? 'selected' : '' ?>>Non-Veg</option>
            </select>

            <label>Description:</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($item['description']) ?></textarea>

            <label>Image Path (URL):</label>
            <input type="text" name="image_path" value="<?= htmlspecialchars($item['image_path']) ?>">

            <button type="submit" class="submit-btn">Update Item</button>
        </form>
    </div>

</body>
</html>
