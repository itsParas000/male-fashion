<?php
require_once '../php/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No product ID provided']);
    exit();
}

$product_id = intval($_GET['id']);
$query = "SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode($reviews);
?>