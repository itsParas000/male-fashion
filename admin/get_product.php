<?php
require_once '../php/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No product ID provided']);
    exit();
}

$product_id = intval($_GET['id']);
$query = "SELECT id, name, category_name, price, old_price, description, image, stock_quantity, color, size, score_and_care_tips, related_products FROM products WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit();
}

echo json_encode($product);
?>