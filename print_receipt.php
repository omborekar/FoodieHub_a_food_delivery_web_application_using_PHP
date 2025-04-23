<?php
if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    $conn = new mysqli("localhost", "root", "", "foodiehub");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("
        SELECT o.id, o.total_amount, o.payment_method, o.created_at, u.name as user_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $order = $orderResult->fetch_assoc();

    $itemStmt = $conn->prepare("
        SELECT m.dish_name, oi.quantity, oi.price 
        FROM order_items oi
        JOIN menu_items m ON oi.item_id = m.id
        WHERE oi.order_id = ?
    ");
    $itemStmt->bind_param("i", $orderId);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    $gstRate = 12;
    $deliveryCharges = 20;
    $otherCharges = 0;
    $subtotal = 0;

    $items = [];
    while ($item = $itemResult->fetch_assoc()) {
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        $items[] = [
            'name' => $item['dish_name'],
            'quantity' => $item['quantity'],
            'unit_price' => number_format($item['price'], 2),
            'total_price' => number_format($itemTotal, 2)
        ];
    }

    $gstAmount = ($subtotal * $gstRate) / 100;
    $grandTotal = $subtotal + $gstAmount + $deliveryCharges + $otherCharges;

    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->formatOutput = true;

    $root = $dom->createElement("receipt");

    // Add XSL stylesheet reference
    $pi = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="receipt_template.xsl"');
    $dom->appendChild($pi);

    $root->appendChild($dom->createElement("order_id", $order['id']));
    $root->appendChild($dom->createElement("user", $order['user_name']));
    $root->appendChild($dom->createElement("payment_method", $order['payment_method']));
    $root->appendChild($dom->createElement("order_date", $order['created_at']));

    $itemsElement = $dom->createElement("items");
    foreach ($items as $it) {
        $itemElement = $dom->createElement("item");
        $itemElement->appendChild($dom->createElement("name", $it['name']));
        $itemElement->appendChild($dom->createElement("quantity", $it['quantity']));
        $itemElement->appendChild($dom->createElement("unit_price", $it['unit_price']));
        $itemElement->appendChild($dom->createElement("total_price", $it['total_price']));
        $itemsElement->appendChild($itemElement);
    }
    $root->appendChild($itemsElement);

    $root->appendChild($dom->createElement("subtotal", number_format($subtotal, 2)));
    $root->appendChild($dom->createElement("gst", number_format($gstAmount, 2)));
    $root->appendChild($dom->createElement("delivery", number_format($deliveryCharges, 2)));
    $root->appendChild($dom->createElement("other", number_format($otherCharges, 2)));
    $root->appendChild($dom->createElement("grand_total", number_format($grandTotal, 2)));

    $dom->appendChild($root);

    $receiptDir = "receipts";
    if (!is_dir($receiptDir)) {
        mkdir($receiptDir, 0777, true);
    }

    $xmlFilename = "$receiptDir/receipt_order_{$order['id']}.xml";
    $dom->save($xmlFilename);

    // Redirect to view XML (formatted with XSL)
    header("Location: $xmlFilename");
    exit;
} else {
    echo "Invalid Order ID.";
}
?>
