<?php
session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $category_name = $_POST['category_name'];
    $price = floatval($_POST['price']);
    $description = $_POST['description'] ?? '';
    $score_and_care_tips = $_POST['score_and_care_tips'] ?? '';
    $related_products = $_POST['related_products'] ?? '';
    $color = $_POST['color'] ?? '';
    $size = $_POST['size'] ?? '';
    $stock_quantity = intval($_POST['stock_quantity']);
    $old_price = isset($_POST['old_price']) && $_POST['old_price'] !== '' ? floatval($_POST['old_price']) : null;

    $image = '';
    $stmt = $con->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $image = $product['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../assets/';
        $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_name;
        } else {
            header('Location: products.php?error=Error uploading image');
            exit;
        }
    }

    $stmt = $con->prepare("UPDATE products SET name = ?, category_name = ?, price = ?, description = ?, score_and_care_tips = ?, related_products = ?, image = ?, stock_quantity = ?, old_price = ?, color = ?, size = ? WHERE id = ?");
    $stmt->bind_param("ssdssssidssi", $name, $category_name, $price, $description, $score_and_care_tips, $related_products, $image, $stock_quantity, $old_price, $color, $size, $id);
    
    if ($stmt->execute()) {
        header('Location: products.php?success=Product updated successfully');
        exit;
    } else {
        header('Location: products.php?error=Error updating product: ' . $con->error);
        exit;
    }
}

header('Location: products.php?error=Invalid request');
exit;
?>