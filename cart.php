<?php
session_name('SESSION_USER');
session_start();
include 'php/config.php';

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Define color mapping consistent with checkout.php and order_complete.php
$color_map = [
    'c-1' => 'White',
    'c-2' => 'Black',
    'c-3' => 'Blue',
    'c-4' => 'Brown',
    'c-5' => 'Red'
];

// Fetch user's virtual money balance
$sql_user = "SELECT virtual_money FROM users WHERE Id = ?";
$stmt_user = $con->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$virtual_money = $user['virtual_money'] ?? 0.00;
$stmt_user->close();

// Fetch coupon codes
$sql_coupons = "SELECT id, code, discount_type, discount_value, user_id, used 
                FROM coupons 
                WHERE is_active = 1 AND used = 0 AND (expires_at IS NULL OR expires_at > NOW())";
$coupon_result = $con->query($sql_coupons);
$coupon_codes = [];
while ($row = $coupon_result->fetch_assoc()) {
    $coupon_codes[$row['code']] = [
        'id' => $row['id'],
        'type' => $row['discount_type'],
        'value' => $row['discount_value'],
        'user_id' => $row['user_id'],
        'used' => $row['used']
    ];
}

// Fetch shipping rates
$sql_shipping = "SELECT id, city, rate FROM shipping_rates";
$shipping_result = $con->query($sql_shipping);
$shipping_rates = [];
if ($shipping_result) {
    while ($row = $shipping_result->fetch_assoc()) {
        $shipping_rates[$row['city']] = ['id' => $row['id'], 'rate' => $row['rate']];
    }
} else {
    $shipping_rates = ['Other' => ['id' => 0, 'rate' => 10.00]];
}

// Initialize variables
$coupon_discount = $_SESSION['coupon_discount'] ?? 0;
$is_free_shipping = $_SESSION['is_free_shipping'] ?? false;
$coupon_message = '';
$applied_coupon_id = $_SESSION['applied_coupon_id'] ?? null;
$applied_virtual_money = min($_SESSION['applied_virtual_money'] ?? 0, $virtual_money);
$applied_shipping_id = $_SESSION['applied_shipping_id'] ?? null;
$selected_city = $_SESSION['selected_city'] ?? '';

// Fetch cart items with color and size, including product color/size for validation
$sql_cart = "SELECT cart.id, products.name, products.price, cart.quantity, products.image, cart.color, cart.size, products.color AS product_colors, products.size AS product_sizes 
             FROM cart 
             JOIN products ON cart.product_id = products.id 
             WHERE cart.user_id = ? AND cart.status = 'active'";
$stmt_cart = $con->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$cart_items = $stmt_cart->get_result();

// Calculate cart subtotal
$cart_subtotal = 0;
$cart_items_array = [];
while ($item = $cart_items->fetch_assoc()) {
    $cart_subtotal += $item['price'] * $item['quantity'];
    $cart_items_array[] = $item;
}
$stmt_cart->close();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_virtual_money'])) {
        $requested_amount = floatval($_POST['virtual_money_amount']);
        if ($requested_amount > 0 && $requested_amount <= $virtual_money) {
            $applied_virtual_money = $requested_amount;
            $_SESSION['applied_virtual_money'] = $applied_virtual_money;
        } else {
            $coupon_message = "Invalid virtual money amount. Balance is ₹" . number_format($virtual_money, 2);
            unset($_SESSION['applied_virtual_money']);
        }
    }
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $sql_remove = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt_remove = $con->prepare($sql_remove);
        $stmt_remove->bind_param("ii", $cart_id, $user_id);
        $stmt_remove->execute();
        $stmt_remove->close();
        header("Location: cart.php");
        exit();
    }
    if (isset($_POST['clear_cart'])) {
        $sql_clear = "DELETE FROM cart WHERE user_id = ? AND status = 'active'";
        $stmt_clear = $con->prepare($sql_clear);
        $stmt_clear->bind_param("i", $user_id);
        $stmt_clear->execute();
        $stmt_clear->close();
        unset($_SESSION['applied_virtual_money'], $_SESSION['coupon_discount'], $_SESSION['is_free_shipping'], 
              $_SESSION['applied_coupon_id'], $_SESSION['selected_city'], $_SESSION['applied_shipping_id']);
        header("Location: cart.php");
        exit();
    }
    if (isset($_POST['apply_coupon'])) {
        $coupon_code = strtoupper(trim($_POST['coupon_code']));
        if (array_key_exists($coupon_code, $coupon_codes)) {
            $coupon = $coupon_codes[$coupon_code];
            if ($coupon['used'] || ($coupon['user_id'] && $coupon['user_id'] != $user_id)) {
                $coupon_message = "Coupon is already used or not valid for this user.";
                unset($_SESSION['coupon_discount'], $_SESSION['is_free_shipping'], $_SESSION['applied_coupon_id']);
            } else {
                $applied_coupon_id = $coupon['id'];
                if ($coupon['type'] === 'free_shipping') {
                    $is_free_shipping = true;
                    $coupon_discount = 0;
                    $coupon_message = "Free shipping applied!";
                } elseif ($coupon['type'] === 'percentage') {
                    $coupon_discount = $cart_subtotal * $coupon['value'];
                    $is_free_shipping = false;
                    $coupon_message = "Discount of ₹" . number_format($coupon_discount, 2) . " applied!";
                } else {
                    $coupon_discount = $coupon['value'];
                    $is_free_shipping = false;
                    $coupon_message = "Discount of ₹" . number_format($coupon_discount, 2) . " applied!";
                }
                $sql_mark_used = "UPDATE coupons SET used = 1 WHERE id = ?";
                $stmt_mark_used = $con->prepare($sql_mark_used);
                $stmt_mark_used->bind_param("i", $coupon['id']);
                $stmt_mark_used->execute();
                $stmt_mark_used->close();
                $_SESSION['coupon_discount'] = $coupon_discount;
                $_SESSION['is_free_shipping'] = $is_free_shipping;
                $_SESSION['applied_coupon_id'] = $applied_coupon_id;
            }
        } else {
            $coupon_message = "Invalid or expired coupon code!";
            unset($_SESSION['coupon_discount'], $_SESSION['is_free_shipping'], $_SESSION['applied_coupon_id']);
        }
    }
    if (isset($_POST['city']) && !empty($_POST['city'])) {
        $selected_city = $_POST['city'];
        $_SESSION['selected_city'] = $selected_city;
        if (array_key_exists($selected_city, $shipping_rates)) {
            $applied_shipping_id = $shipping_rates[$selected_city]['id'];
            $_SESSION['applied_shipping_id'] = $applied_shipping_id;
        }
    }
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = max(1, intval($_POST['quantity']));
        $sql_update = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        header("Location: cart.php");
        exit();
    }
}

// Calculate shipping cost
$shipping_cost = 0;
if ($cart_subtotal > 0) {
    if ($is_free_shipping) {
        $shipping_cost = 0;
    } elseif (!empty($selected_city) && array_key_exists($selected_city, $shipping_rates)) {
        $shipping_cost = $shipping_rates[$selected_city]['rate'];
        $applied_shipping_id = $shipping_rates[$selected_city]['id'];
    } else {
        $shipping_cost = $shipping_rates['Other']['rate'] ?? 10.00;
        $applied_shipping_id = $shipping_rates['Other']['id'] ?? 0;
    }
}

$cart_total = $cart_subtotal + $shipping_cost - $applied_virtual_money - $coupon_discount;
if ($cart_total < 0) $cart_total = 0;
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="stylesheet" href="style.css" type="text/css">
    <style>
        .product__cart__item__pic img { width: 90px; height: 90px; object-fit: cover; }
        .coupon-message { margin-top: 10px; font-size: 14px; }
        .virtual-money, .shipping { margin-bottom: 20px; }
        .virtual-money h6, .shipping h6 { margin-bottom: 15px; font-size: 18px; color: #111111; }
        .virtual-money form, .coupon form, .shipping form { display: flex; gap: 10px; }
        .virtual-money input, .coupon input, .shipping select { 
            padding: 10px; 
            border: 1px solid #e1e1e1; 
            border-radius: 5px; 
            flex-grow: 1; 
        }
        .virtual-money button, .coupon button, .shipping button { 
            padding: 10px 20px; 
            background: #e53637; 
            color: #fff; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        .message { margin-top: 10px; font-size: 14px; }
        .message.success { color: #28a745; }
        .message.error { color: #e53637; }
        .item-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
                        <h4>Shopping Cart</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <a href="./shop.php">Shop</a>
                            <span>Shopping Cart</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="shopping-cart spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="shopping__cart__table" style="padding: 30px; margin-bottom: 50px;">
                        <form method="POST">
                            <table style="width: 100%; max-width: 800px; margin: 0 auto;">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($cart_items_array) > 0): ?>
                                        <?php foreach ($cart_items_array as $index => $item): ?>
                                            <tr class="cart__item" data-sr-id="<?php echo $index; ?>">
                                                <td class="product__cart__item">
                                                    <div class="product__cart__item__pic">
                                                        <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                    </div>
                                                    <div class="product__cart__item__text">
                                                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <div class="item-details">
                                                            <?php
                                                            // Map color code to name, consistent with checkout.php and order_complete.php
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
                                                            Color: <?php echo htmlspecialchars($color_name); ?>,
                                                            Size: <?php echo htmlspecialchars($size_name); ?>
                                                        </div>
                                                        <h5>₹<?php echo number_format($item['price'], 2); ?></h5>
                                                    </div>
                                                </td>
                                                <td class="quantity__item">
                                                    <div class="quantity">
                                                        <div class="pro-qty-2" style="width: 80px;">
                                                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" style="width: 40px;" min="1">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="cart__price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                <td class="cart__close">
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" name="remove_item" style="background:none;border:none;cursor:pointer;">
                                                            <i class="fa fa-close"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4">Your cart is empty.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (count($cart_items_array) > 0): ?>
                                <div class="row" style="margin-top: 50px;">
                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                        <div class="continue__btn">
                                            <a href="./shop.php">Continue Shopping</a>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                        <div class="continue__btn update__btn">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="clear_cart" value="1">
                                                <a href="#" onclick="this.closest('form').submit(); return false;">Clear Cart</a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="cart__discount">
                        <h6>Discount Codes</h6>
                        <form method="POST">
                            <input type="text" name="coupon_code" placeholder="Coupon code">
                            <button type="submit" name="apply_coupon">Apply</button>
                        </form>
                        <?php if (!empty($coupon_message)): ?>
                            <p class="coupon-message <?php echo strpos($coupon_message, 'Invalid') === false ? 'success' : 'error'; ?>">
                                <?php echo $coupon_message; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="cart__discount virtual-money">
                        <h6>Virtual Money</h6>
                        <p>Your Balance: ₹<?php echo number_format($virtual_money, 2); ?></p>
                        <?php if ($applied_virtual_money > 0): ?>
                            <p>Applied: ₹<?php echo number_format($applied_virtual_money, 2); ?></p>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="number" name="virtual_money_amount" step="0.01" min="0" max="<?php echo $virtual_money; ?>" placeholder="Amount" required>
                            <button type="submit" name="apply_virtual_money">Apply</button>
                        </form>
                    </div>
                    <div class="cart__discount shipping">
                        <h6>Shipping</h6>
                        <form method="POST">
                            <select name="city" onchange="this.form.submit()">
                                <option value="">Select City</option>
                                <?php foreach ($shipping_rates as $city => $data): ?>
                                    <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $selected_city === $city ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city); ?> (₹<?php echo number_format($data['rate'], 2); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="cart__total" data-sr-id="totals">
                        <h6>Cart Total</h6>
                        <ul>
                            <li>Subtotal <span>₹<?php echo number_format($cart_subtotal, 2); ?></span></li>
                            <li>Shipping <span><?php echo ($shipping_cost == 0 && $cart_subtotal > 0) ? 'Free' : '₹' . number_format($shipping_cost, 2); ?></span></li>
                            <?php if ($applied_virtual_money > 0): ?>
                                <li>Virtual Money <span>-₹<?php echo number_format($applied_virtual_money, 2); ?></span></li>
                            <?php endif; ?>
                            <?php if ($coupon_discount > 0): ?>
                                <li>Coupon Discount <span>-₹<?php echo number_format($coupon_discount, 2); ?></span></li>
                            <?php endif; ?>
                            <li>Total <span>₹<?php echo number_format($cart_total, 2); ?></span></li>
                        </ul>
                        <?php if (count($cart_items_array) > 0): ?>
                            <form method="POST" action="checkout.php">
                                <input type="hidden" name="cart_subtotal" value="<?php echo $cart_subtotal; ?>">
                                <input type="hidden" name="shipping_cost" value="<?php echo $shipping_cost; ?>">
                                <input type="hidden" name="virtual_money_discount" value="<?php echo $applied_virtual_money; ?>">
                                <input type="hidden" name="coupon_discount" value="<?php echo $coupon_discount; ?>">
                                <input type="hidden" name="cart_total" value="<?php echo $cart_total; ?>">
                                <input type="hidden" name="coupon_id" value="<?php echo $applied_coupon_id; ?>">
                                <input type="hidden" name="shipping_rate_id" value="<?php echo $applied_shipping_id; ?>">
                                <button type="submit" name="proceed_to_checkout" class="primary-btn" <?php echo empty($selected_city) && !$is_free_shipping ? 'disabled style="background: #ccc; cursor: not-allowed;"' : ''; ?>>
                                    Proceed to Checkout
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
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
    <script>
    $(document).ready(function() {
        $('.pro-qty input').on('change', function() {
            let cart_id = $(this).attr('name').match(/\d+/)[0];
            let quantity = $(this).val();
            $.post('cart.php', {
                update_quantity: true,
                cart_id: cart_id,
                quantity: quantity
            }, function() {
                location.reload();
            });
        });
    });

    const scrollRevealOption = {
        distance: "50px",
        origin: "bottom",
        duration: 1000,
    };

    ScrollReveal().reveal(".cart__item", {
        ...scrollRevealOption,
        interval: 200,
    });

    ScrollReveal().reveal(".cart__total", {
        ...scrollRevealOption,
        origin: "right",
        delay: 500,
    });

    ScrollReveal().reveal(".continue__btn, .update__btn", {
        ...scrollRevealOption,
        delay: 700,
    });
    </script>
</body>
</html>