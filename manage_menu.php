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

// Add new item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = $_POST['dish_name'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $desc = $_POST['description'];
    $image_path = $_POST['image_path']; // Could be enhanced with file upload

    $stmt = $conn->prepare("INSERT INTO menu_items (dish_name, price, type, description, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", $name, $price, $type, $desc, $image_path);
    if ($stmt->execute()) {
        $msg = "Item added successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
}

// Delete item
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_menu.php");
    exit;
}

// Fetch all menu items
$result = $conn->query("SELECT * FROM menu_items ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Menu - Admin</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/manage_menu.css">
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
        <h2>Add New Menu Item</h2>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="add_item" value="1">
            <label>Dish Name:</label>
            <input type="text" name="dish_name" required>

            <label>Price (₹):</label>
            <input type="text" name="price" required>

            <label>Type:</label>
            <select name="type" required>
                <option value="veg">Veg</option>
                <option value="non-veg">Non-Veg</option>
            </select>

            <label>Description:</label>
            <textarea name="description" rows="3"></textarea>

            <label>Image Path (URL):</label>
            <input type="text" name="image_path">

            <button type="submit" class="submit-btn">Add Item</button>
        </form>
    </div>

    <div class="menu-list">
        <h2>Available Menu Items</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dish Name</th>
                    <th>Price (₹)</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['dish_name']) ?></td>
                        <td><?= number_format($item['price'], 2) ?></td>
                        <td><?= htmlspecialchars($item['type']) ?></td>
                        <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                        <td>
                            <?php if ($item['image_path']): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" width="60" height="40">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="action-btn edit-btn" href="edit_menu_item.php?id=<?= $item['id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="manage_menu.php?delete_id=<?= $item['id'] ?>" onclick="return confirm('Delete this item?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
