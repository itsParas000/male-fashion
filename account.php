<?php
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

session_name('SESSION_USER');
session_start();

include 'php/config.php';

if (!isset($_SESSION['valid']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Define color mapping
$color_map = [
    'c-1' => 'White',
    'c-2' => 'Black',
    'c-3' => 'Blue',
    'c-4' => 'Brown',
    'c-5' => 'Red'
];

// Fetch user details
$sql_user = "SELECT Username, Email, address, virtual_money, profile_picture FROM users WHERE Id = ?";
$stmt_user = $con->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$stmt_user->close();

// Handle profile updates (username, address)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = htmlspecialchars(trim($_POST['username']));
    $new_address = htmlspecialchars(trim($_POST['address']));
    $sql_update = "UPDATE users SET Username = ?, address = ? WHERE Id = ?";
    $stmt_update = $con->prepare($sql_update);
    $stmt_update->bind_param("ssi", $new_username, $new_address, $user_id);
    $stmt_update->execute();
    $stmt_update->close();
    $user['Username'] = $new_username;
    $user['address'] = $new_address;
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 2 * 1024 * 1024;

    if ($file['size'] > $max_size || !in_array($file['type'], $allowed_types)) {
        $error = "Invalid file. Use JPEG/PNG, max 2MB.";
    } else {
        $filename = 'profile_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $upload_path = 'Uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $sql_pic = "UPDATE users SET profile_picture = ? WHERE Id = ?";
            $stmt_pic = $con->prepare($sql_pic);
            $stmt_pic->bind_param("si", $filename, $user_id);
            $stmt_pic->execute();
            $stmt_pic->close();
            $user['profile_picture'] = $filename;
        }
    }
}

// Handle profile picture removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_profile_picture'])) {
    if ($user['profile_picture']) {
        $old_picture_path = 'Uploads/' . $user['profile_picture'];
        if (file_exists($old_picture_path)) {
            unlink($old_picture_path);
        }
    }
    $sql_remove_pic = "UPDATE users SET profile_picture = NULL WHERE Id = ?";
    $stmt_remove_pic = $con->prepare($sql_remove_pic);
    $stmt_remove_pic->bind_param("i", $user_id);
    $stmt_remove_pic->execute();
    $stmt_remove_pic->close();
    $user['profile_picture'] = null;
}

// Fetch order history with color and size
$sql_orders = "SELECT o.id, o.order_number, o.created_at, o.status, oi.product_id, oi.quantity, oi.price, oi.color, oi.size, p.name, p.color AS product_colors, p.size AS product_sizes
               FROM orders o
               LEFT JOIN order_items oi ON o.id = oi.order_id
               LEFT JOIN products p ON oi.product_id = p.id
               WHERE o.user_id = ?
               ORDER BY o.created_at DESC";
$stmt_orders = $con->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders_result = $stmt_orders->get_result();
$stmt_orders->close();

// Fetch user-specific coupons
$sql_coupons = "SELECT code, discount_value, discount_type, expires_at FROM coupons WHERE user_id = ? AND is_active = 1 AND used = 0";
$stmt_coupons = $con->prepare($sql_coupons);
$stmt_coupons->bind_param("i", $user_id);
$stmt_coupons->execute();
$coupons_result = $stmt_coupons->get_result();
$stmt_coupons->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <script>
        window.onload = function() {
            <?php if (!isset($_SESSION['valid'])): ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        };
    </script>
    <title>Your Account | Male Fashion</title>
    <style>
        body {
            font-family: 'Nunito Sans', sans-serif;
            background: #f3f2ee;
            min-height: 100vh;
            margin: 0;
            color: #111111;
        }

        .nav__header {
            background: #fff;
            padding: 20px 40px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .nav__logo a {
            font-size: 28px;
            font-weight: 700;
            color: #111111;
            text-decoration: none;
        }

        .nav__links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav__links a {
            color: #3d3d3d;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            transition: color 0.3s ease;
        }

        .nav__links a:hover {
            color: #e53637;
        }

        .nav__search {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav__search input {
            padding: 8px;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            font-size: 14px;
        }

        .nav__search i {
            color: #3d3d3d;
            font-size: 16px;
        }

        .account-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-card, .history-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.50);
            padding: 30px;
            margin-bottom: 30px;
        }

        h2, h3 {
            font-size: 28px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-pic-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e1e1e1;
            transition: border-color 0.3s ease;
        }

        .profile-pic:hover {
            border-color: #e53637;
        }

        .profile-pic-upload {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .profile-pic-upload input {
            display: none;
        }

        .profile-pic-upload label {
            background: #e53637;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            transition: background 0.3s ease;
        }

        .profile-pic-upload label:hover {
            background: #ca2e2f;
        }

        .profile-pic-upload button, .remove-pic-btn {
            background: #e53637;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            transition: background 0.3s ease;
        }

        .profile-pic-upload button:hover, .remove-pic-btn:hover {
            background: #ca2e2f;
        }

        .remove-pic-btn {
            background: #fff;
            color: #e53637;
            border: 1px solid #e53637;
        }

        .remove-pic-btn:hover {
            background: #e53637;
            color: #fff;
        }

        .profile-grid {
            display: grid;
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .profile-item span {
            font-weight: 600;
            color: #3d3d3d;
            font-size: 14px;
        }

        .profile-item input, .profile-item p {
            width: 100%;
            padding: 10px;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            font-size: 14px;
            color: #111111;
            background: #fff;
        }

        .profile-item input:focus {
            border-color: #e53637;
            outline: none;
        }

        .profile-item p.text-green-600 {
            color: #e53637;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .action-buttons button, .action-buttons .logout-btn {
            background: #e53637;
            color: #fff;
            padding: 12px 25px;
            border-radius: 4px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            transition: background 0.3s ease;
        }

        .action-buttons button:hover, .action-buttons .logout-btn:hover {
            background: #ca2e2f;
        }

        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .history-table th {
            padding: 15px;
            font-size: 14px;
            font-weight: 700;
            color: #111111;
            background: #f3f2ee;
            text-transform: uppercase;
        }

        .history-table td {
            padding: 15px;
            color: #3d3d3d;
            background: #fff;
        }

        .history-table tbody tr:hover {
            background: #f9f9f9;
        }

        .empty-state {
            text-align: center;
            color: #3d3d3d;
            padding: 20px;
            font-size: 16px;
        }

        .error {
            color: #e53637;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            margin-left: 500px;
            margin-right: 500px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .modal-buttons button {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .modal-buttons .yes-btn {
            background: #e53637;
        }

        .modal-buttons .yes-btn:hover {
            background: #ca2e2f;
        }

        .modal-buttons .no-btn {
            background: #111111;
        }

        .modal-buttons .no-btn:hover {
            background: #333333;
        }

        .item-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .account-container {
                padding: 0 15px;
            }

            .profile-card, .history-card {
                padding: 20px;
            }

            .profile-pic {
                width: 100px;
                height: 100px;
            }

            .profile-grid {
                gap: 10px;
            }

            .history-table th, .history-table td {
                padding: 10px;
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }

            .profile-pic-upload {
                flex-direction: column;
                gap: 10px;
            }

            .nav__links {
                display: none;
            }

            .modal-content {
                margin-left: 20px;
                margin-right: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>Account</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span>Account</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="account-container">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="profile-card">
            <h2>Your Profile</h2>
            <div class="profile-pic-container">
                <img src="<?php echo $user['profile_picture'] ? 'Uploads/' . htmlspecialchars($user['profile_picture']) : 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y'; ?>" alt="Profile Picture" class="profile-pic">
                <div class="profile-pic-upload">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png">
                        <label for="profile_picture">Choose File</label>
                        <button type="submit">Upload</button>
                    </form>
                    <?php if ($user['profile_picture']): ?>
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="remove_profile_picture" class="remove-pic-btn">Remove</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <form method="POST" class="profile-grid">
                <div class="profile-item">
                    <span>Username</span>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                </div>
                <div class="profile-item">
                    <span>Email</span>
                    <p><?php echo htmlspecialchars($user['Email']); ?></p>
                </div>
                <div class="profile-item">
                    <span>Address</span>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="Enter your address">
                </div>
                <div class="profile-item">
                    <span>Virtual Money</span>
                    <p class="text-green-600 font-semibold">₹<?php echo number_format($user['virtual_money'], 2); ?></p>
                </div>
                <div class="action-buttons">
                    <button type="submit" name="update_profile">Save Changes</button>
                    <button type="button" id="logout-btn" class="logout-btn">Logout</button>
                </div>
            </form>
        </div>

        <div class="history-card">
            <h3>Order History</h3>
            <?php if ($orders_result->num_rows > 0): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Date/Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_order_id = null;
                        while ($order = $orders_result->fetch_assoc()):
                            if ($order['id'] !== $current_order_id):
                                $current_order_id = $order['id'];
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars($order['name'] ?? 'N/A');
                                        if ($order['name']):
                                            $color_name = isset($color_map[$order['color']]) ? $color_map[$order['color']] : ($order['color'] ?: 'Not specified');
                                            $valid_colors = !empty($order['product_colors']) ? explode(',', $order['product_colors']) : [];
                                            if (!in_array($color_name, $valid_colors) && $color_name !== 'Not specified' && !empty($valid_colors)) {
                                                $color_name = 'Invalid color';
                                            }
                                            $size_name = $order['size'] ?: 'Not specified';
                                            $valid_sizes = !empty($order['product_sizes']) ? explode(',', $order['product_sizes']) : [];
                                            if (!in_array($size_name, $valid_sizes) && $size_name !== 'Not specified' && !empty($valid_sizes)) {
                                                $size_name = 'Invalid size';
                                            }
                                        ?>
                                            <div class="item-details">
                                                Color: <?php echo htmlspecialchars($color_name); ?>,
                                                Size: <?php echo htmlspecialchars($size_name); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $order['quantity'] ?? 'N/A'; ?></td>
                                    <td>₹<?php echo number_format($order['price'] ?? 0, 2); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars($order['name'] ?? 'N/A');
                                        if ($order['name']):
                                            $color_name = isset($color_map[$order['color']]) ? $color_map[$order['color']] : ($order['color'] ?: 'Not specified');
                                            $valid_colors = !empty($order['product_colors']) ? explode(',', $order['product_colors']) : [];
                                            if (!in_array($color_name, $valid_colors) && $color_name !== 'Not specified' && !empty($valid_colors)) {
                                                $color_name = 'Invalid color';
                                            }
                                            $size_name = $order['size'] ?: 'Not specified';
                                            $valid_sizes = !empty($order['product_sizes']) ? explode(',', $order['product_sizes']) : [];
                                            if (!in_array($size_name, $valid_sizes) && $size_name !== 'Not specified' && !empty($valid_sizes)) {
                                                $size_name = 'Invalid size';
                                            }
                                        ?>
                                            <div class="item-details">
                                                Color: <?php echo htmlspecialchars($color_name); ?>,
                                                Size: <?php echo htmlspecialchars($size_name); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $order['quantity'] ?? 'N/A'; ?></td>
                                    <td>₹<?php echo number_format($order['price'] ?? 0, 2); ?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No order history found.</p>
            <?php endif; ?>
        </div>

        <div class="history-card">
            <h3>Your Coupons</h3>
            <?php if ($coupons_result->num_rows > 0): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Coupon Code</th>
                            <th>Discount</th>
                            <th>Expires At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($coupon = $coupons_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                                <td><?php echo $coupon['discount_type'] === 'fixed' ? '₹' . number_format($coupon['discount_value'], 2) : ($coupon['discount_value'] * 100) . '%'; ?></td>
                                <td><?php echo htmlspecialchars($coupon['expires_at'] ?? 'No Expiry'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No active coupons found.</p>
            <?php endif; ?>
        </div>
    </section>

    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-lg font-semibold mb-4">Are you sure you want to logout?</h3>
            <div class="modal-buttons">
                <button class="yes-btn" id="confirm-logout">Yes</button>
                <button class="no-btn" id="cancel-logout">No</button>
            </div>
        </div>
    </div>

    <?php include 'footer.php' ?>

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="main1.js"></script>
    <script>
        history.pushState(null, document.title, location.href);
        window.addEventListener('popstate', function(event) {
            history.pushState(null, document.title, location.href);
        });

        const logoutBtn = document.getElementById('logout-btn');
        const logoutModal = document.getElementById('logout-modal');
        const confirmLogout = document.getElementById('confirm-logout');
        const cancelLogout = document.getElementById('cancel-logout');

        logoutBtn.addEventListener('click', function() {
            logoutModal.classList.add('active');
        });

        confirmLogout.addEventListener('click', function() {
            window.location.href = 'php/logout.php?role=user';
        });

        cancelLogout.addEventListener('click', function() {
            logoutModal.classList.remove('active');
        });
    </script>
</body>
</html>