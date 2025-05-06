<?php
// Set session settings before starting session
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

session_name('SESSION_DELIVERY');
session_start();
require_once '../php/config.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

// Redirect if not a delivery boy
if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'delivery') {
    header("Location: ../login.php");
    exit();
}

// Check database connection
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get delivery boy details
$delivery_boy_id = $_SESSION['id'];
$stmt = $con->prepare("SELECT Username, city, Email, profile_picture FROM users WHERE Id = ? AND role = 'delivery'");
$stmt->bind_param("i", $delivery_boy_id);
$stmt->execute();
$delivery_boy = $stmt->get_result()->fetch_assoc();
if (!$delivery_boy) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
$delivery_boy_city = trim(strtolower($delivery_boy['city']));
$delivery_boy_name = $delivery_boy['Username'];
$delivery_boy_email = $delivery_boy['Email'];

// Handle profile picture with absolute path and file existence check
$default_image = '/images/default-user.png';
$profile_picture_path = $delivery_boy['profile_picture'] ?? '';
if ($profile_picture_path && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $profile_picture_path)) {
    $delivery_boy_profile_picture = '/' . $profile_picture_path;
} else {
    $delivery_boy_profile_picture = $default_image;
}
$stmt->close();

// Function to extract city from shipping address
function extractCityFromAddress($address) {
    $parts = array_map('trim', explode(',', $address));
    $potential_city = end($parts);
    if (preg_match('/\d{6}/', $potential_city)) {
        array_pop($parts);
        $potential_city = end($parts);
    }
    $states = ['maharashtra', 'karnataka', 'delhi', 'tamil nadu', 'gujarat'];
    foreach ($states as $state) {
        $potential_city = str_ireplace($state, '', $potential_city);
    }
    return trim(strtolower($potential_city));
}

// Handle POST requests
$error = $success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);

    if (isset($_POST['accept_order'])) {
        $stmt = $con->prepare("SELECT shipping_address FROM orders WHERE id = ? AND status = 'Pending' AND delivery_boy_id IS NULL");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $error = "Order not available for acceptance.";
        } else {
            $order = $result->fetch_assoc();
            $shipping_city = extractCityFromAddress($order['shipping_address']);
            if ($shipping_city !== $delivery_boy_city) {
                $error = "Order is not in your city.";
            } else {
                $stmt = $con->prepare("UPDATE orders SET delivery_boy_id = ?, status = 'Accepted', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $delivery_boy_id, $order_id);
                if ($stmt->execute()) {
                    $success = "Order accepted successfully.";
                } else {
                    $error = "Error accepting order.";
                }
            }
        }
        $stmt->close();
    } elseif (isset($_POST['deny_order'])) {
        $stmt = $con->prepare("SELECT shipping_address FROM orders WHERE id = ? AND status = 'Pending' AND delivery_boy_id IS NULL");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $error = "Order not available for denial.";
        } else {
            $order = $result->fetch_assoc();
            $shipping_city = extractCityFromAddress($order['shipping_address']);
            if ($shipping_city !== $delivery_boy_city) {
                $error = "Order is not in your city.";
            } else {
                $success = "Order denied (remains available for others).";
            }
        }
        $stmt->close();
    } elseif (isset($_POST['update_status'])) {
        $new_status = $_POST['status'] ?? '';
        $valid_statuses = ['Accepted', 'Processing', 'Shipped', 'Delivered'];
        if (!in_array($new_status, $valid_statuses)) {
            $error = "Invalid status selected.";
        } else {
            $stmt = $con->prepare("SELECT id FROM orders WHERE id = ? AND delivery_boy_id = ? AND status IN ('Accepted', 'Processing', 'Shipped')");
            $stmt->bind_param("ii", $order_id, $delivery_boy_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $error = "Order not assigned to you or cannot be updated.";
            } else {
                $stmt = $con->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $new_status, $order_id);
                if ($stmt->execute()) {
                    $success = "Order status updated to $new_status.";
                } else {
                    $error = "Error updating status.";
                }
            }
            $stmt->close();
        }
    } elseif (isset($_POST['upload_profile_picture'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/png'];
            $max_size = 2 * 1024 * 1024; // 2MB
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Uploads/profiles/';
            $relative_upload_dir = 'Uploads/profiles/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $error = "Failed to create upload directory.";
                    file_put_contents('debug.log', "Failed to create directory: $upload_dir\n", FILE_APPEND);
                }
            }
            if (!$error) {
                if (!in_array($file['type'], $allowed_types)) {
                    $error = "Only JPEG or PNG images are allowed.";
                } elseif ($file['size'] > $max_size) {
                    $error = "Image size must not exceed 2MB.";
                } else {
                    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_filename = 'user_' . $delivery_boy_id . '_' . time() . '.' . $file_ext;
                    $destination = $upload_dir . $new_filename;
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Delete old profile picture if not default
                        if ($profile_picture_path && $profile_picture_path !== $default_image && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $profile_picture_path)) {
                            unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $profile_picture_path);
                        }
                        $profile_path = $relative_upload_dir . $new_filename;
                        $stmt = $con->prepare("UPDATE users SET profile_picture = ? WHERE Id = ?");
                        $stmt->bind_param("si", $profile_path, $delivery_boy_id);
                        if ($stmt->execute()) {
                            $success = "Profile picture updated successfully.";
                            $delivery_boy_profile_picture = '/' . $profile_path;
                        } else {
                            $error = "Error updating profile picture in database.";
                            if (file_exists($destination)) {
                                unlink($destination);
                            }
                        }
                        $stmt->close();
                    } else {
                        $error = "Error uploading image to server.";
                        file_put_contents('debug.log', "Upload failed for: $destination\n", FILE_APPEND);
                    }
                }
            }
        } else {
            $error = "No image selected or upload error (code: " . ($_FILES['profile_picture']['error'] ?? 'N/A') . ").";
        }
    } elseif (isset($_POST['remove_profile_picture'])) {
        if ($profile_picture_path && $profile_picture_path !== $default_image && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $profile_picture_path)) {
            if (unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $profile_picture_path)) {
                file_put_contents('debug.log', "Deleted profile picture: $profile_picture_path\n", FILE_APPEND);
            } else {
                file_put_contents('debug.log', "Failed to delete profile picture: $profile_picture_path\n", FILE_APPEND);
            }
        }
        $stmt = $con->prepare("UPDATE users SET profile_picture = NULL WHERE Id = ?");
        $stmt->bind_param("i", $delivery_boy_id);
        if ($stmt->execute()) {
            $success = "Profile picture removed successfully.";
            $delivery_boy_profile_picture = $default_image;
        } else {
            $error = "Error removing profile picture.";
        }
        $stmt->close();
    }
}

// Fetch pending orders
$pending_query = "SELECT o.*, u.Username as customer_name, o.shipping_address 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.Id 
                 WHERE o.status = 'Pending' 
                 AND o.delivery_boy_id IS NULL 
                 ORDER BY o.created_at DESC";
$stmt_pending = $con->prepare($pending_query);
$stmt_pending->execute();
$pending_result = $stmt_pending->get_result();

// Filter pending orders by city
$pending_orders = [];
while ($row = $pending_result->fetch_assoc()) {
    $shipping_city = extractCityFromAddress($row['shipping_address']);
    if ($shipping_city === $delivery_boy_city) {
        $pending_orders[] = $row;
    }
}
$has_pending = count($pending_orders) > 0;

// Debug: Log all pending orders details
$debug_log = "Delivery Boy: $delivery_boy_name, City: $delivery_boy_city\nPending Query: $pending_query\nRows (after city filter): " . count($pending_orders) . "\n";
foreach ($pending_orders as $row) {
    $shipping_city = extractCityFromAddress($row['shipping_address']);
    $debug_log .= "Order ID: {$row['id']}, Order Number: {$row['order_number']}, Shipping Address: {$row['shipping_address']}, Extracted City: $shipping_city, Status: {$row['status']}, Delivery Boy ID: " . ($row['delivery_boy_id'] ?? 'NULL') . "\n";
}
file_put_contents('debug.log', $debug_log, FILE_APPEND);

// Fetch active orders
$active_query = "SELECT o.id, o.order_number, o.total_amount, o.shipping_address, o.status, o.created_at, u.Username as customer_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.Id 
                WHERE o.delivery_boy_id = ? 
                AND o.status IN ('Accepted', 'Processing', 'Shipped') 
                ORDER BY o.created_at DESC";
$stmt_active = $con->prepare($active_query);
$stmt_active->bind_param("i", $delivery_boy_id);
$stmt_active->execute();
$active_result = $stmt_active->get_result();
$has_active = $active_result->num_rows > 0;

// Fetch delivered history
$history_query = "SELECT o.id, o.order_number, o.total_amount, o.shipping_address, o.status, o.updated_at, u.Username as customer_name 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.Id 
                 WHERE o.delivery_boy_id = ? 
                 AND o.status = 'Delivered' 
                 ORDER BY o.updated_at DESC";
$stmt_history = $con->prepare($history_query);
$stmt_history->bind_param("i", $delivery_boy_id);
$stmt_history->execute();
$history_result = $stmt_history->get_result();
$has_history = $history_result->num_rows > 0;

// Debug: Log profile picture details
file_put_contents('debug.log', "Profile Picture Path: $delivery_boy_profile_picture, Exists: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . $delivery_boy_profile_picture) ? 'Yes' : 'No') . "\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Boy Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --blue: #2a2185;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --black2: #999;
        }
        .navigation {
            position: fixed;
            width: 300px;
            height: 100%;
            background: var(--blue);
            border-left: 10px solid var(--blue);
            transition: 0.5s;
            overflow: hidden;
            left: 0; /* Ensure it starts from the left edge */
        }
        .navigation.active {
            width: 80px;
        }
        .navigation ul {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
        .navigation ul li {
            position: relative;
            width: 100%;
            list-style: none;
            border-top-left-radius: 30px;
            border-bottom-left-radius: 30px;
        }
        .navigation ul li:hover,
        .navigation ul li.hovered {
            background-color: var(--white);
        }
        .navigation ul li:nth-child(1) {
            margin-bottom: 40px;
            pointer-events: none;
        }
        .navigation ul li a {
            position: relative;
            display: block;
            width: 100%;
            display: flex;
            text-decoration: none;
            color: var(--white);
        }
        .navigation ul li:hover a,
        .navigation ul li.hovered a {
            color: var(--blue);
        }
        .navigation ul li a .icon {
            position: relative;
            display: block;
            min-width: 60px;
            height: 60px;
            line-height: 75px;
            text-align: center;
        }
        .navigation ul li a .icon ion-icon {
            font-size: 1.75rem;
        }
        .navigation ul li a .title {
            position: relative;
            display: block;
            padding: 0 10px;
            height: 60px;
            line-height: 60px;
            text-align: start;
            white-space: nowrap;
        }
        .navigation ul li:hover a::before,
        .navigation ul li.hovered a::before {
            content: "";
            position: absolute;
            right: 0;
            top: -50px;
            width: 50px;
            height: 50px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 35px 35px 0 10px var(--white);
            pointer-events: none;
        }
        .navigation ul li:hover a::after,
        .navigation ul li.hovered a::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: -50px;
            width: 50px;
            height: 50px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 35px -35px 0 10px var(--white);
            pointer-events: none;
        }
        .main {
            margin-left: 300px;
            transition: margin-left 0.5s;
            width: calc(100% - 300px);
            padding: 0 8px;
            background: var(--gray); /* Match body background to avoid white space */
        }
        .main.active {
            margin-left: 80px;
            width: calc(100% - 80px);
        }
        .topbar {
            width: 100%;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 10px;
        }
        .toggle {
            position: relative;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            cursor: pointer;
        }
        .table-container {
            max-height: 50vh;
            overflow-y: auto;
            width: 100%;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
        .file-input {
            display: none;
        }
        .file-label {
            cursor: pointer;
            background-color: #4a5568;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            display: inline-block;
        }
        .file-label:hover {
            background-color: #2d3748;
        }
    </style>

    <style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background: var(--gray); /* Match background with main */
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    img {
        max-width: 100%;
        height: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-container {
        display: flex;
        flex-wrap: wrap;
    }

    .sidebar {
        flex: 1 1 200px;
    }

    .dashboard-main {
        flex: 3 1 600px;
        padding: 1rem;
    }

    .profile, .orders, .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .profile-info {
        flex: 1 1 100%;
        text-align: center;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            position: static;
        }

        .dashboard-main {
            width: 100%;
            padding: 1rem;
        }

        .profile, .orders, .actions {
            flex-direction: column;
            align-items: stretch;
        }

        table th, table td {
            font-size: 14px;
            padding: 6px;
        }
    }

    @media (max-width: 480px) {
        body {
            font-size: 14px;
        }

        .btn {
            font-size: 14px;
            padding: 8px 12px;
        }

        h1, h2, h3 {
            font-size: 1.25rem;
        }
    }
    </style>

</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="container">
        <!-- Navigation -->
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><ion-icon name="person-circle"></ion-icon></span>
                        <span class="title">Delivery Panel</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="openModal('profile-modal')">
                        <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="title">Profile</span>
                    </a>
                </li>
                <li>
                    <a href="../php/logout.php?role=delivery" onclick="return confirm('Are you sure you want to sign out?')">
                        <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>
            <div class="p-8">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-8">Delivery Dashboard</h1>

                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="toast bg-green-500 text-white p-4 rounded-lg shadow-lg mb-4"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="toast bg-red-500 text-white p-4 rounded-lg shadow-lg mb-4"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Pending Orders -->
                <h2 class="text-2xl font-bold text-gray-800 mb-4">New Orders</h2>
                <?php if ($has_pending): ?>
                    <div class="bg-white rounded-xl shadow-md table-container mb-8">
                        <table class="w-full">
                            <thead class="bg-gray-200 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left">Order #</th>
                                    <th class="p-4 text-left">Customer</th>
                                    <th class="p-4 text-left">Total</th>
                                    <th class="p-4 text-left">Address</th>
                                    <th class="p-4 text-left">Date</th>
                                    <th class="p-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_orders as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['order_number']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="p-4">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['shipping_address']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td class="p-4 flex gap-2">
                                            <form method="POST">
                                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="accept_order" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                            </form>
                                            <form method="POST">
                                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="deny_order" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                    <i class="fas fa-times"></i> Deny
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-6 rounded-xl shadow-md text-center text-gray-600 mb-8">
                        No new orders available in your city.
                    </div>
                <?php endif; ?>

                <!-- Active Orders -->
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Active Orders</h2>
                <?php if ($has_active): ?>
                    <div class="bg-white rounded-xl shadow-md table-container mb-8">
                        <table class="w-full">
                            <thead class="bg-gray-200 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left">Order #</th>
                                    <th class="p-4 text-left">Customer</th>
                                    <th class="p-4 text-left">Total</th>
                                    <th class="p-4 text-left">Status</th>
                                    <th class="p-4 text-left">Address</th>
                                    <th class="p-4 text-left">Date</th>
                                    <th class="p-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $active_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['order_number']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="p-4">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 rounded text-white <?php
                                                echo match (strtolower($row['status'])) {
                                                    'accepted' => 'bg-green-500',
                                                    'processing' => 'bg-orange-500',
                                                    'shipped' => 'bg-blue-500',
                                                    default => 'bg-gray-500'
                                                };
                                            ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['shipping_address']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td class="p-4">
                                            <button onclick="openStatusModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['status']); ?>')" 
                                                    class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-6 rounded-xl shadow-md text-center text-gray-600 mb-8">
                        No active orders.
                    </div>
                <?php endif; ?>

                <!-- Delivered History -->
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Delivered Orders</h2>
                <?php if ($has_history): ?>
                    <div class="bg-white rounded-xl shadow-md table-container">
                        <table class="w-full">
                            <thead class="bg-gray-200 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left">Order #</th>
                                    <th class="p-4 text-left">Customer</th>
                                    <th class="p-4 text-left">Total</th>
                                    <th class="p-4 text-left">Address</th>
                                    <th class="p-4 text-left">Delivered On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $history_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['order_number']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="p-4">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['shipping_address']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['updated_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-6 rounded-xl shadow-md text-center text-gray-600">
                        No delivered orders yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Delivery Boy Profile</h2>
            <div class="text-center mb-4">
                <img src="<?php echo htmlspecialchars($delivery_boy_profile_picture); ?>" alt="Profile Picture" class="profile-picture mx-auto">
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4 text-center">
                    <label for="profile_picture" class="file-label">
                        <i class="fas fa-upload mr-2"></i>Choose Profile Picture
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png" class="file-input">
                    <button type="submit" name="upload_profile_picture" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mt-2">
                        <i class="fas fa-save mr-2"></i>Upload
                    </button>
                </div>
            </form>
            <form method="POST">
                <div class="mb-4 text-center">
                    <button type="submit" name="remove_profile_picture" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        <i class="fas fa-trash mr-2"></i>Remove Profile Picture
                    </button>
                </div>
            </form>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold">Name</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($delivery_boy_name); ?></p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold">Email</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($delivery_boy_email); ?></p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold">City</label>
                <p class="text-gray-900"><?php echo htmlspecialchars($delivery_boy_city); ?></p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('profile-modal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="status-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Update Order Status</h2>
            <form method="POST">
                <input type="hidden" name="order_id" id="status-order-id">
                <div class="mb-4">
                    <label class="block text-gray-700">Status</label>
                    <select name="status" id="status-select" class="w-full p-2 border rounded">
                        <option value="Accepted">Accepted</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('status-modal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                    <button type="submit" name="update_status" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        // Toggle navigation
        const toggle = document.querySelector('.toggle');
        const navigation = document.querySelector('.navigation');
        const main = document.querySelector('.main');
        toggle.onclick = () => {
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        };

        // Modal functions
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        function openStatusModal(orderId, currentStatus) {
            document.getElementById('status-order-id').value = orderId;
            document.getElementById('status-select').value = currentStatus;
            openModal('status-modal');
        }
    </script>
</body>
</html>