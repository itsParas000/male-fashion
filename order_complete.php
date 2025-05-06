<?php
session_name('SESSION_USER');
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    die("Invalid order ID.");
}

// Define color mapping consistent with cart.php and checkout.php
$color_map = [
    'c-1' => 'White',
    'c-2' => 'Black',
    'c-3' => 'Blue',
    'c-4' => 'Brown',
    'c-5' => 'Red'
];

// Fetch order details including tracking_number
$sql_order = "SELECT o.order_number, o.total_amount, o.created_at, o.shipping_address, o.virtual_money_used,
                     o.tracking_number, c.code AS coupon_code, sr.city AS shipping_city, sr.rate AS shipping_rate
              FROM orders o
              LEFT JOIN coupons c ON o.coupon_id = c.id
              LEFT JOIN shipping_rates sr ON o.shipping_rate_id = sr.id
              WHERE o.id = ? AND o.user_id = ?";
$stmt_order = $con->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found or you don’t have permission to view it.");
}
$order = $order_result->fetch_assoc();
$stmt_order->close();

// Fetch order items including color, size, and product validation
$sql_items = "SELECT oi.quantity, oi.price, oi.color, oi.size, p.name, p.color AS product_colors, p.size AS product_sizes 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $con->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Reward logic
$reward_message = '';
if (!isset($_SESSION['order_rewarded_' . $order_id])) {
    $sql_user = "SELECT virtual_money FROM users WHERE Id = ?";
    $stmt_user = $con->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $user = $user_result->fetch_assoc();
    $current_virtual_money = $user['virtual_money'] ?? 0.00;
    $stmt_user->close();

    $reward_type = rand(0, 1) ? 'coupon' : 'virtual_money';

    if ($reward_type === 'coupon') {
        $coupon_code = "USER{$user_id}-" . strtoupper(substr(md5(uniqid()), 0, 6));
        $discount_value = 10.00;
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        $sql_insert_coupon = "INSERT INTO coupons (code, discount_type, discount_value, is_active, expires_at, user_id) 
                              VALUES (?, 'fixed', ?, 1, ?, ?)";
        $stmt_coupon = $con->prepare($sql_insert_coupon);
        $stmt_coupon->bind_param("sdsi", $coupon_code, $discount_value, $expires_at, $user_id);
        if ($stmt_coupon->execute()) {
            $reward_message = "Congratulations! You've received a unique coupon: " . htmlspecialchars($coupon_code) . ". Use it within 30 days!";
        } else {
            $reward_type = 'virtual_money';
        }
        $stmt_coupon->close();
    }

    if ($reward_type === 'virtual_money') {
        $max_reward = $order['total_amount'] * 0.25;
        $virtual_reward = min(mt_rand(0, 500) / 100, $max_reward, 5.00);
        $new_virtual_money = $current_virtual_money + $virtual_reward;

        $sql_update_vm = "UPDATE users SET virtual_money = ? WHERE Id = ?";
        $stmt_update_vm = $con->prepare($sql_update_vm);
        $stmt_update_vm->bind_param("di", $new_virtual_money, $user_id);
        $stmt_update_vm->execute();
        $stmt_update_vm->close();

        $reward_message = "Congratulations! You've earned ₹" . number_format($virtual_reward, 2) . " in virtual money!";
    }

    $_SESSION['order_rewarded_' . $order_id] = true;
} else {
    $reward_message = "You've already received a reward for this order.";
}

?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Complete | Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .order-complete { text-align: center; padding: 40px 0; }
        .order-complete h1 { font-size: 28px; font-weight: 700; color: #111111; margin-bottom: 20px; }
        .order-complete p { font-size: 16px; color: #666666; margin-bottom: 20px; }
        .success-icon { font-size: 48px; color: #28a745; margin-bottom: 20px; }
        .reward-message { font-size: 16px; color: #28a745; background: #e6ffe6; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .order-details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .order-details h2 { font-size: 24px; font-weight: 700; color: #111111; margin-bottom: 20px; }
        .detail-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e9ecef; }
        .detail-item span:first-child { font-weight: 600; color: #666666; }
        .detail-item span:last-child { color: #111111; }
        .items-list { margin: 15px 0; }
        .item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e9ecef; }
        .item-details { font-size: 12px; color: #666; margin-top: 5px; }
        .total { font-size: 18px; font-weight: 700; color: #e53637; text-align: right; margin-top: 15px; }
        .button-container { display: flex; justify-content: center; gap: 20px; margin-top: 30px; }
        .site-btn {
            cursor: pointer !important;
            pointer-events: auto !important;
            background-color: #e53637;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .site-btn:hover {
            background-color: #c02526;
        }
        @media print {
            body * { visibility: hidden; }
            #invoice-content, #invoice-content * { visibility: visible; }
            #invoice-content { position: absolute; top: 0; left: 0; width: 100%; }
            .button-container, .success-icon, .reward-message, .breadcrumb-option { display: none; }
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
                        <h4>Order Complete</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <a href="./cart.php">Cart</a>
                            <a href="./checkout.php">Checkout</a>
                            <span>Order Complete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container">
            <div class="order-complete">
                <i class="fas fa-check-circle success-icon"></i>
                <h1>Thank You! Your Order is Complete</h1>
                <p>Your order has been successfully placed. We’ll send you a confirmation email shortly.</p>
                <?php if (!empty($reward_message)): ?>
                    <div class="reward-message"><?php echo $reward_message; ?></div>
                <?php endif; ?>
                <div class="order-details" id="invoice-content">
                    <h2>Order Details</h2>
                    <div class="detail-item">
                        <span>Order ID:</span>
                        <span>#<?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Tracking Number:</span>
                        <span><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Date:</span>
                        <span><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Shipping City:</span>
                        <span><?php echo htmlspecialchars($order['shipping_city'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Shipping Cost:</span>
                        <span>₹ <?php echo number_format($order['shipping_rate'] ?? 0, 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Coupon Applied:</span>
                        <span><?php echo htmlspecialchars($order['coupon_code'] ?? 'None'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Virtual Money Used:</span>
                        <span>₹ <?php echo number_format($order['virtual_money_used'] ?? 0, 2); ?></span>
                    </div>
                    <div class="detail-item items-list">
                        <span>Items:</span>
                        <div>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <div class="item">
                                        <span>
                                            <?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)
                                            <div class="item-details">
                                                <?php
                                                // Map and validate color
                                                $color_name = isset($color_map[$item['color']]) ? $color_map[$item['color']] : ($item['color'] ?: 'Not specified');
                                                $valid_colors = !empty($item['product_colors']) ? explode(',', $item['product_colors']) : [];
                                                if (!in_array($color_name, $valid_colors) && $color_name !== 'Not specified' && !empty($valid_colors)) {
                                                    $color_name = 'Invalid color';
                                                }
                                                // Validate size
                                                $size_name = $item['size'] ?: 'Not specified';
                                                $valid_sizes = !empty($item['product_sizes']) ? explode(',', $item['product_sizes']) : [];
                                                if (!in_array($size_name, $valid_sizes) && $size_name !== 'Not specified' && !empty($valid_sizes)) {
                                                    $size_name = 'Invalid size';
                                                }
                                                ?>
                                                Color: <?php echo htmlspecialchars($color_name); ?>,
                                                Size: <?php echo htmlspecialchars($size_name); ?>
                                            </div>
                                        </span>
                                        <span>₹ <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No items found for this order.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="total">
                        Total: ₹ <?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                </div>
                <div class="button-container">
                    <a href="shop.php" class="site-btn">Continue Shopping</a>
                    <a href="account.php" class="site-btn">View Account</a>
                    <button id="download-invoice-btn" class="site-btn" onclick="window.print()">Download Invoice</button>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch">+</div>
            <form class="search-model-form">
                <input type="text" id="search-input" placeholder="Search here.....">
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7XgwQHe6OaUs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/mixitup@3.3.1/dist/mixitup.min.js" integrity="sha256-q4HLmF7zMEP2nKPVszwVUWZaTShZFr8jX691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main1.js"></script>
</body>
</html>