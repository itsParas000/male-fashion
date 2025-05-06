<?php
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

session_name('SESSION_USER');
session_start();

include 'php/config.php';

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Define color mapping consistent with cart.php
$color_map = [
    'c-1' => 'White',
    'c-2' => 'Black',
    'c-3' => 'Blue',
    'c-4' => 'Brown',
    'c-5' => 'Red'
];

// Fetch all orders for the user
$sql_orders = "SELECT o.id, o.order_number, o.tracking_number, o.status, o.created_at, o.total_amount, o.shipping_address,
                      c.code AS coupon_code, sr.city AS shipping_city, sr.rate AS shipping_rate, o.virtual_money_used
               FROM orders o
               LEFT JOIN coupons c ON o.coupon_id = c.id
               LEFT JOIN shipping_rates sr ON o.shipping_rate_id = sr.id
               WHERE o.user_id = ?
               ORDER BY o.created_at DESC";
$stmt_orders = $con->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders_result = $stmt_orders->get_result();
$stmt_orders->close();

// Function to fetch order items
function getOrderItems($con, $order_id) {
    $sql_items = "SELECT oi.quantity, oi.price, p.name, oi.color, oi.size, p.color AS product_colors, p.size AS product_sizes
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
    $stmt_items = $con->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
    $stmt_items->close();
    return $items_result;
}

// Handle popup toggle and actions
$selected_order = null;
$error = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $view_order_id = intval($_GET['view']);
    $sql_order = "SELECT o.order_number, o.tracking_number, o.status, o.created_at, o.total_amount, o.shipping_address,
                         c.code AS coupon_code, sr.city AS shipping_city, sr.rate AS shipping_rate, o.virtual_money_used
                  FROM orders o
                  LEFT JOIN coupons c ON o.coupon_id = c.id
                  LEFT JOIN shipping_rates sr ON o.shipping_rate_id = sr.id
                  WHERE o.id = ? AND o.user_id = ?";
    $stmt_order = $con->prepare($sql_order);
    $stmt_order->bind_param("ii", $view_order_id, $user_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    if ($order_result->num_rows > 0) {
        $selected_order = $order_result->fetch_assoc();
        $selected_items = getOrderItems($con, $view_order_id);
    }
    $stmt_order->close();
}

// Handle order actions (Cancel, Replace, Return)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];

    $sql_check = "SELECT created_at, status, virtual_money_used FROM orders WHERE id = ? AND user_id = ?";
    $stmt_check = $con->prepare($sql_check);
    $stmt_check->bind_param("ii", $order_id, $user_id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    $order = $check_result->fetch_assoc();
    $stmt_check->close();

    if ($order) {
        $con->begin_transaction();
        try {
            if ($action === 'cancel' && $order['status'] === 'Processing' && (time() - strtotime($order['created_at']) < 86400)) {
                $sql_update = "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?";
                $stmt_update = $con->prepare($sql_update);
                $stmt_update->bind_param("ii", $order_id, $user_id);
                $stmt_update->execute();
                $stmt_update->close();

                if ($order['virtual_money_used'] > 0) {
                    $sql_user = "UPDATE users SET virtual_money = virtual_money + ? WHERE Id = ?";
                    $stmt_user = $con->prepare($sql_user);
                    $stmt_user->bind_param("di", $order['virtual_money_used'], $user_id);
                    $stmt_user->execute();
                    $stmt_user->close();
                }
            } elseif ($action === 'replace' && in_array($order['status'], ['Shipped', 'Delivered'])) {
                $sql_update = "UPDATE orders SET status = 'Replacement Requested' WHERE id = ? AND user_id = ?";
                $stmt_update = $con->prepare($sql_update);
                $stmt_update->bind_param("ii", $order_id, $user_id);
                $stmt_update->execute();
                $stmt_update->close();
            } elseif ($action === 'return' && $order['status'] === 'Delivered' && (time() - strtotime($order['created_at']) < 2592000)) {
                $sql_update = "UPDATE orders SET status = 'Return Requested' WHERE id = ? AND user_id = ?";
                $stmt_update = $con->prepare($sql_update);
                $stmt_update->bind_param("ii", $order_id, $user_id);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                throw new Exception("Action not allowed for this order status or time frame.");
            }
            $con->commit();
            header("Location: tracking.php?view=$order_id");
            exit();
        } catch (Exception $e) {
            $con->rollback();
            $error = "Failed to process $action: " . $e->getMessage();
        }
    } else {
        $error = "Order not found or unauthorized.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css" type="text/css">
    <style>
        .spad { padding: 80px 0; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: 36px; font-weight: 700; color: #111111; }
        .order-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .order-card { 
            background-color: #fff; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
            border: 1px solid #e9ecef; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            cursor: pointer; 
        }
        .order-card:hover { transform: translateY(-5px); box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); }
        .order-card h2 { font-size: 18px; font-weight: 600; color: #111111; margin-bottom: 10px; }
        .order-card .detail-item { 
            display: flex; 
            justify-content: space-between; 
            padding: 5px 0; 
            font-size: 14px; 
            color: #3d3d3d; 
        }
        .order-card .detail-item span:first-child { font-weight: 500; }
        .order-card .status { font-weight: 600; margin-top: 10px; }
        .order-card .status.completed { color: #28a745; }
        .order-card .status.pending { color: #ffc107; }
        .order-card .status.cancelled { color: #dc3545; }
        .order-card .status.processing { color: #17a2b8; }
        .no-orders { 
            text-align: center; 
            color: #3d3d3d; 
            padding: 20px; 
            background-color: #fff; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
            font-size: 16px; 
        }
        .popup { 
            display: <?php echo $selected_order ? 'block' : 'none'; ?>; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            z-index: 1000; 
            overflow-y: auto; 
        }
        .popup-content { 
            background-color: #fff; 
            margin: 40px auto; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); 
            max-width: 800px; 
            position: relative; 
            animation: slideIn 0.3s ease-out; 
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close-btn { 
            position: absolute; 
            top: 15px; 
            right: 15px; 
            font-size: 24px; 
            color: #dc3545; 
            cursor: pointer; 
            transition: color 0.3s ease; 
        }
        .close-btn:hover { color: #007bff; }
        .popup h2 { font-size: 24px; font-weight: 700; color: #111111; margin-bottom: 20px; }
        .popup .detail-item { 
            display: flex; 
            justify-content: space-between; 
            padding: 10px 0; 
            font-size: 16px; 
            color: #3d3d3d; 
        }
        .popup .detail-item span:first-child { font-weight: 600; }
        .popup .items-list { margin: 15px 0; }
        .popup .item { 
            display: flex; 
            justify-content: space-between; 
            padding: 10px 0; 
            color: #3d3d3d; 
        }
        .popup .item-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .popup .total { 
            font-size: 18px; 
            font-weight: 700; 
            color: #007bff; 
            text-align: right; 
            margin-top: 15px; 
        }
        .action-buttons { 
            margin-top: 20px; 
            display: flex; 
            gap: 10px; 
            justify-content: center; 
        }
        .action-btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 8px; 
            font-size: 14px; 
            font-weight: 600; 
            color: #fff; 
            cursor: pointer; 
            transition: background-color 0.3s ease; 
        }
        .cancel-btn { background-color: #dc3545; }
        .cancel-btn:hover { background-color: #c82333; }
        .replace-btn { background-color: #ffc107; }
        .replace-btn:hover { background-color: #e0a800; }
        .return-btn { background-color: #17a2b8; }
        .return-btn:hover { background-color: #138496; }
        .error { 
            color: #dc3545; 
            text-align: center; 
            margin-bottom: 15px; 
            font-weight: 500; 
        }
        .back-to-home { text-align: center; margin-top: 40px; }
        .back-to-home a { 
            display: inline-block; 
            padding: 12px 30px; 
            font-size: 16px; 
            font-weight: 600; 
            color: #ffffff; 
            background-color: #111111; 
            border-radius: 5px; 
            text-decoration: none; 
            transition: background-color 0.3s; 
        }
        .back-to-home a:hover { 
            background-color: #3d3d3d; 
            color: #ffffff; 
        }
        @media (max-width: 768px) {
            .order-grid { grid-template-columns: 1fr; }
            .popup-content { margin: 20px; padding: 20px; }
            .action-buttons { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>Order Tracking</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <a href="./account.php">Account</a>
                            <span>Order Tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tracking Section -->
    <section class="tracking spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Track Your Orders</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?php if ($orders_result->num_rows > 0): ?>
                        <div class="order-grid">
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <div class="order-card" onclick="window.location.href='?view=<?php echo $order['id']; ?>'">
                                    <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                                    <div class="detail-item">
                                        <span>Tracking Number:</span>
                                        <span><?php echo htmlspecialchars($order['tracking_number'] ?? 'Not assigned'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span>Date:</span>
                                        <span><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span>Total:</span>
                                        <span>₹ <?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="status <?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-orders">You have no orders to track at the moment.</div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Back to Home Button -->
            <div class="back-to-home">
                <a href="./index.php">Back to Home</a>
            </div>
        </div>
    </section>

    <!-- Popup for Order Details -->
    <?php if ($selected_order): ?>
        <div class="popup">
            <div class="popup-content">
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <span class="close-btn" onclick="window.location.href='tracking.php'">×</span>
                <h2>Order Details #<?php echo htmlspecialchars($selected_order['order_number']); ?></h2>
                <div class="detail-item">
                    <span>Tracking Number:</span>
                    <span><?php echo htmlspecialchars($selected_order['tracking_number'] ?? 'Not assigned'); ?></span>
                </div>
                <div class="detail-item">
                    <span>Date:</span>
                    <span><?php echo date('F j, Y, g:i a', strtotime($selected_order['created_at'])); ?></span>
                </div>
                <div class="detail-item">
                    <span>Shipping City:</span>
                    <span><?php echo htmlspecialchars($selected_order['shipping_city'] ?? 'Not specified'); ?></span>
                </div>
                <div class="detail-item">
                    <span>Shipping Cost:</span>
                    <span>₹ <?php echo number_format($selected_order['shipping_rate'] ?? 0, 2); ?></span>
                </div>
                <div class="detail-item">
                    <span>Coupon Applied:</span>
                    <span><?php echo htmlspecialchars($selected_order['coupon_code'] ?? 'None'); ?></span>
                </div>
                <div class="detail-item">
                    <span>Virtual Money Used:</span>
                    <span>₹ <?php echo number_format($selected_order['virtual_money_used'] ?? 0, 2); ?></span>
                </div>
                <div class="detail-item items-list">
                    <span>Items:</span>
                    <div>
                        <?php while ($item = $selected_items->fetch_assoc()): ?>
                            <div class="item">
                                <div>
                                    <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                    <?php
                                    // Map color code to name, consistent with cart.php
                                    $color_name = isset($color_map[$item['color']]) ? $color_map[$item['color']] : ($item['color'] ?: 'Not specified');
                                    // Validate against product colors
                                    $valid_colors = !empty($item['product_colors']) ? explode(',', $item['product_colors']) : [];
                                    if (!in_array($color_name, $valid_colors) && $color_name !== 'Not specified' && !empty($valid_colors)) {
                                        $color_name = 'Invalid color';
                                    }
                                    // Size handling
                                    $size_name = $item['size'] ?: 'Not specified';
                                    $valid_sizes = !empty($item['product_sizes']) ? explode(',', $item['product_sizes']) : [];
                                    if (!in_array($size_name, $valid_sizes) && $size_name !== 'Not specified' && !empty($valid_sizes)) {
                                        $size_name = 'Invalid size';
                                    }
                                    ?>
                                    <div class="item-details">
                                        Color: <?php echo htmlspecialchars($color_name); ?>,
                                        Size: <?php echo htmlspecialchars($size_name); ?>
                                    </div>
                                </div>
                                <span>₹ <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="detail-item total">
                    <span>Total:</span>
                    <span>₹ <?php echo number_format($selected_order['total_amount'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <span>Status:</span>
                    <span class="status <?php echo strtolower($selected_order['status']); ?>">
                        <?php echo ucfirst($selected_order['status']); ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span>Shipping Address:</span>
                    <span><?php echo htmlspecialchars($selected_order['shipping_address'] ?? 'Not specified'); ?></span>
                </div>
                <div class="action-buttons">
                    <?php if ($selected_order['status'] === 'Processing' && (time() - strtotime($selected_order['created_at']) < 86400)): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $view_order_id; ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="action-btn cancel-btn">Cancel</button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($selected_order['status'], ['Shipped', 'Delivered'])): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $view_order_id; ?>">
                            <input type="hidden" name="action" value="replace">
                            <button type="submit" class="action-btn replace-btn">Replace</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($selected_order['status'] === 'Delivered' && (time() - strtotime($selected_order['created_at']) < 2592000)): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $view_order_id; ?>">
                            <input type="hidden" name="action" value="return">
                            <button type="submit" class="action-btn return-btn">Return</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    trening
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5Z6Di8NMZo1nxt1Yungdr1sGI=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-SLG1S77+1ED3pJmW/EdAWyJ2U5L2+7N/6UFWN2pGbSA=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0O38JBBP69WigcUoMBlJ2b4Q6vW1DG8OQ2wSifs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-7xm1o4DlC5f7q1y+w/cLZyNqFepkR2oAValJfxPTCuc=" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>