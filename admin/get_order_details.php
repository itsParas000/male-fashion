<?php
require_once '../php/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No order ID provided']);
    exit();
}

$order_id = intval($_GET['id']);

// Fetch order details
$query = "SELECT orders.*, users.Username as user_name 
         FROM orders 
         JOIN users ON orders.user_id = users.Id 
         WHERE orders.id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit();
}

// Fetch order items with color and size
$items_query = "SELECT order_items.*, products.name as product_name 
                FROM order_items 
                JOIN products ON order_items.product_id = products.id 
                WHERE order_id = ?";
$stmt = $con->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

$order['items'] = $items;
echo json_encode($order);
?>