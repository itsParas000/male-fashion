<?php
session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category_name = $_POST['category_name'];
    $price = floatval($_POST['price']);
    $description = $_POST['description'] ?? null;
    $score_and_care_tips = $_POST['score_and_care_tips'] ?? null;
    $related_products = $_POST['related_products'] ?? null;
    $color = $_POST['color'] ?? null;
    $size = $_POST['size'] ?? null;
    $stock_quantity = intval($_POST['stock_quantity']);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
        $image_path = '../assets/' . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_name;
        } else {
            header("Location: products.php?error=Failed to upload image");
            exit();
        }
    }

    // Prepare SQL query
    $sql = "INSERT INTO products (name, category_name, price, description, score_and_care_tips, related_products, image, stock_quantity, old_price, color, size) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    // Bind parameters (11 parameters, type string should be 'ssdssssidss')
    $stmt->bind_param('ssdssssidss', $name, $category_name, $price, $description, $score_and_care_tips, $related_products, $image, $stock_quantity, $old_price, $color, $size);

    if ($stmt->execute()) {
        header("Location: products.php?success=Product added successfully");
    } else {
        header("Location: products.php?error=Failed to add product: " . $con->error);
    }
    $stmt->close();
    exit();
}

$con->close();
?>