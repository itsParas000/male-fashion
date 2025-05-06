<?php
session_name('SESSION_USER');
session_start();
include("php/config.php");

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

// Function to generate a unique tracking number
function generateTrackingNumber($con) {
    do {
        $date = date("Ymd");
        $random = str_pad(rand(0, 9999), 4, "0", STR_PAD_LEFT);
        $tracking_number = "TRK-{$date}-{$random}";
        $count = 0;
        $sql_check = "SELECT COUNT(*) FROM orders WHERE tracking_number = ?";
        $stmt_check = $con->prepare($sql_check);
        $stmt_check->bind_param("s", $tracking_number);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();
    } while ($count > 0);
    return $tracking_number;
}

// Fetch user details for virtual money
$sql_user = "SELECT virtual_money FROM users WHERE Id = ?";
$stmt_user = $con->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$virtual_money = $user['virtual_money'] ?? 0.00;
$stmt_user->close();

// Fetch cart items for display and order_items insertion, including color, size, and product validation
$sql_cart = "SELECT products.id AS product_id, products.name, products.price, cart.quantity, cart.color, cart.size, products.color AS product_colors, products.size AS product_sizes
             FROM cart
             JOIN products ON cart.product_id = products.id
             WHERE cart.user_id = ? AND cart.status = 'active'";
$stmt_cart = $con->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$cart_items_result = $stmt_cart->get_result();

// Check if cart is empty when accessing page directly
if ($cart_items_result->num_rows == 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error_message = "Your cart is empty. Please add items to proceed to checkout.";
} else {
    // Get cart totals from cart.php
    $cart_subtotal = isset($_POST['cart_subtotal']) ? floatval($_POST['cart_subtotal']) : 0;
    $shipping_cost = isset($_POST['shipping_cost']) ? floatval($_POST['shipping_cost']) : 0;
    $virtual_money_discount = isset($_POST['virtual_money_discount']) ? floatval($_POST['virtual_money_discount']) : 0;
    $coupon_discount = isset($_POST['coupon_discount']) ? floatval($_POST['coupon_discount']) : 0;
    $cart_total = isset($_POST['cart_total']) ? floatval($_POST['cart_total']) : 0;
    $coupon_id = isset($_POST['coupon_id']) ? (($_POST['coupon_id'] !== '') ? intval($_POST['coupon_id']) : null) : null;

    // Ensure shipping_rate_id is valid or NULL
    $shipping_rate_id = null;
    if (isset($_POST['shipping_rate_id']) && $_POST['shipping_rate_id'] !== '') {
        $temp_shipping_rate_id = intval($_POST['shipping_rate_id']);
        $sql_verify_shipping = "SELECT id FROM shipping_rates WHERE id = ?";
        $stmt_verify_shipping = $con->prepare($sql_verify_shipping);
        $stmt_verify_shipping->bind_param("i", $temp_shipping_rate_id);
        $stmt_verify_shipping->execute();
        $shipping_result = $stmt_verify_shipping->get_result();
        if ($shipping_result->num_rows > 0) {
            $shipping_rate_id = $temp_shipping_rate_id;
        }
        $stmt_verify_shipping->close();
    }

    // Process checkout form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_checkout'])) {
        $required_fields = ['first_name', 'last_name', 'address', 'city', 'state', 'pin', 'email', 'phone', 'payment_method', 'terms'];
        $all_filled = true;
        $errors = [];

        foreach ($required_fields as $field) {
            if ($field === 'terms') {
                if (!isset($_POST[$field])) {
                    $all_filled = false;
                    $errors[] = "You must accept the terms and conditions.";
                }
            } elseif (empty(trim($_POST[$field]))) {
                $all_filled = false;
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $_POST['first_name'])) {
            $errors[] = "First name must be alphabetic characters only.";
            $all_filled = false;
        }
        if (!preg_match('/^[a-zA-Z\s]+$/', $_POST['last_name'])) {
            $errors[] = "Last name must be alphabetic characters only.";
            $all_filled = false;
        }
        if (!preg_match('/^[a-zA-Z0-9\s,.\/-]{5,100}$/', trim($_POST['address']))) {
            $all_filled = false;
            $errors[] = "Address must be 5-100 characters and can include letters, numbers, spaces, commas, periods, slashes, and hyphens.";
        }
        if (!preg_match('/^[a-zA-Z\s]+$/', $_POST['city'])) {
            $errors[] = "City must be alphabetic characters only.";
            $all_filled = false;
        }
        if (!preg_match('/^[a-zA-Z\s]+$/', $_POST['state'])) {
            $errors[] = "State must be alphabetic characters only.";
            $all_filled = false;
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $all_filled = false;
            $errors[] = "Invalid email format.";
        }
        if (!preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
            $all_filled = false;
            $errors[] = "Phone number must be 10 digits.";
        }
        if (!preg_match('/^[0-9]{6}$/', $_POST['pin'])) {
            $all_filled = false;
            $errors[] = "Pin Code must be 6 digits.";
        }
        if (!in_array($_POST['payment_method'], ['credit_card', 'cod', 'paypal', 'bank-transfer'])) {
            $all_filled = false;
            $errors[] = "Invalid payment method selected.";
        }

        if ($cart_items_result->num_rows == 0) {
            $all_filled = false;
            $errors[] = "Your cart is empty. Please add items to proceed.";
        }

        if ($all_filled) {
            $billing_address = htmlspecialchars($_POST['first_name']) . " " . htmlspecialchars($_POST['last_name']) . ", " .
                               htmlspecialchars($_POST['address']) . ", " .
                               htmlspecialchars($_POST['city']) . ", " .
                               htmlspecialchars($_POST['state']) . " " . htmlspecialchars($_POST['pin']) . ", " .
                               "Email: " . htmlspecialchars($_POST['email']) . ", Phone: " . htmlspecialchars($_POST['phone']);
            $shipping_address = $billing_address; // No separate shipping address option

            $order_number = "ORD-" . date("Ymd") . "-" . str_pad($user_id, 4, "0", STR_PAD_LEFT) . "-" . rand(1000, 9999);
            $tracking_number = generateTrackingNumber($con);

            $payment_method_map = [
                'credit_card' => 'Credit Card',
                'cod' => 'Cash on Delivery',
                'paypal' => 'PayPal',
                'bank-transfer' => 'Bank Transfer'
            ];
            $payment_method = $payment_method_map[$_POST['payment_method']] ?? 'PayPal';

            $con->begin_transaction();

            try {
                $sql_order = "INSERT INTO orders (order_number, user_id, total_amount, virtual_money_used, coupon_id, shipping_rate_id, payment_method, payment_status, status, transaction_id, shipping_address, billing_address, tracking_number, delivery_boy_id, created_at)
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'Completed', 'Processing', ?, ?, ?, ?, NULL, NOW())";
                $stmt_order = $con->prepare($sql_order);
                $transaction_id = "DUMMY-" . rand(100000, 999999);
                $stmt_order->bind_param("siddissssss", $order_number, $user_id, $cart_total, $virtual_money_discount, $coupon_id, $shipping_rate_id, $payment_method, $transaction_id, $shipping_address, $billing_address, $tracking_number);
                $stmt_order->execute();
                $order_id = $stmt_order->insert_id;
                $stmt_order->close();

                $cart_items_result->data_seek(0);
                while ($item = $cart_items_result->fetch_assoc()) {
                    $sql_order_item = "INSERT INTO order_items (order_id, product_id, quantity, price, color, size)
                                       VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_order_item = $con->prepare($sql_order_item);
                    $stmt_order_item->bind_param("iiidss", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['color'], $item['size']);
                    $stmt_order_item->execute();
                    $stmt_order_item->close();
                }

                $shipping_city = trim($_POST['city']);
                $assign_query = "SELECT Id FROM users WHERE Role = 'delivery' AND city = ? ORDER BY RAND() LIMIT 1";
                $stmt_assign = $con->prepare($assign_query);
                $stmt_assign->bind_param("s", $shipping_city);
                $stmt_assign->execute();
                $assign_result = $stmt_assign->get_result();
                if ($row = $assign_result->fetch_assoc()) {
                    $delivery_boy_id = $row['Id'];
                    $update_query = "UPDATE orders SET delivery_boy_id = ? WHERE id = ?";
                    $stmt_update = $con->prepare($update_query);
                    $stmt_update->bind_param("ii", $delivery_boy_id, $order_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
                $stmt_assign->close();

                if ($virtual_money_discount > 0 && $virtual_money_discount <= $virtual_money) {
                    $new_virtual_money = $virtual_money - $virtual_money_discount;
                    $sql_update_vm = "UPDATE users SET virtual_money = ? WHERE Id = ?";
                    $stmt_update_vm = $con->prepare($sql_update_vm);
                    $stmt_update_vm->bind_param("di", $new_virtual_money, $user_id);
                    $stmt_update_vm->execute();
                    $stmt_update_vm->close();
                }

                $sql_delete_cart = "DELETE FROM cart WHERE user_id = ? AND status = 'active'";
                $stmt_delete_cart = $con->prepare($sql_delete_cart);
                $stmt_delete_cart->bind_param("i", $user_id);
                $stmt_delete_cart->execute();
                $stmt_delete_cart->close();

                unset($_SESSION['applied_virtual_money'], $_SESSION['coupon_discount'], $_SESSION['is_free_shipping'],
                      $_SESSION['applied_coupon_id'], $_SESSION['selected_city'], $_SESSION['applied_shipping_id']);

                $con->commit();
                header("Location: order_complete.php?order_id=" . $order_id);
                exit();
            } catch (Exception $e) {
                $con->rollback();
                $error_message = "An error occurred: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
}
$stmt_cart->close();
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">
    <style>
        .error {
        color: #e53637;
        font-size: 14px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .item-details {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .checkout__total__products li {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 10px 0;
        font-size: 14px;
        color: #111111;
    }
    .checkout__total__products li .product-details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .checkout__total__products li .item-details {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }
    .checkout__total__products li span {
        font-weight: 600;
        color: #111111;
        min-width: 100px;
        text-align: right;
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
                        <h4>Check Out</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <a href="./shop.php">Shop</a>
                            <span>Check Out</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container">
            <div class="checkout__form">
                <?php if (isset($error_message)): ?>
                    <p class="error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <?php if (!isset($error_message) || $cart_items_result->num_rows > 0): ?>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-lg-8 col-md-6">
                                <h6 class="coupon__code"><span class="icon_tag_alt"></span> Have a coupon? <a href="./cart.php">Click here</a> to enter your code</h6>
                                <h6 class="checkout__title">Billing Details</h6>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>First Name<span>*</span></p>
                                            <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Last Name<span>*</span></p>
                                            <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="checkout__input">
                                    <p>Address<span>*</span></p>
                                    <input type="text" name="address" placeholder="Street Address" class="checkout__input__add" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                                </div>
                                <div class="checkout__input">
                                    <p>Town/City<span>*</span></p>
                                    <input type="text" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                                </div>
                                <div class="checkout__input">
                                    <p>State<span>*</span></p>
                                    <input type="text" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                                </div>
                                <div class="checkout__input">
                                    <p>Postcode / ZIP<span>*</span></p>
                                    <input type="text" name="pin" value="<?php echo isset($_POST['pin']) ? htmlspecialchars($_POST['pin']) : ''; ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Phone<span>*</span></p>
                                            <input type="text" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Email<span>*</span></p>
                                            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="checkout__input">
                                    <p>Order notes</p>
                                    <input type="text" placeholder="Notes about your order, e.g. special notes for delivery." name="order_notes" value="<?php echo isset($_POST['order_notes']) ? htmlspecialchars($_POST['order_notes']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="checkout__order">
                                    <h4 class="order__title">Your Order</h4>
                                    <div class="checkout__order__products">Product <span>Total</span></div>
                                    <ul class="checkout__total__products">
    <?php
    $cart_items_result->data_seek(0);
    while ($item = $cart_items_result->fetch_assoc()) {
        $item_total = $item['price'] * $item['quantity'];
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
        <li>
            <div class="product-details">
                <?php echo htmlspecialchars($item['name']) . " x" . $item['quantity']; ?>
                <div class="item-details">
                    Color: <?php echo htmlspecialchars($color_name); ?>, Size: <?php echo htmlspecialchars($size_name); ?>
                </div>
            </div>
            <span>₹<?php echo number_format($item_total, 2); ?></span>
        </li>
        <?php
    }
    $cart_items_result->data_seek(0);
    ?>
</ul>
                                    <ul class="checkout__total__all">
                                        <li>Subtotal <span>₹<?php echo number_format($cart_subtotal, 2); ?></span></li>
                                        <li>Shipping <span><?php echo ($shipping_cost == 0) ? 'Free' : '₹' . number_format($shipping_cost, 2); ?></span></li>
                                        <?php if ($virtual_money_discount > 0): ?>
                                            <li>Virtual Money <span>-₹<?php echo number_format($virtual_money_discount, 2); ?></span></li>
                                        <?php endif; ?>
                                        <?php if ($coupon_discount > 0): ?>
                                            <li>Coupon Discount <span>-₹<?php echo number_format($coupon_discount, 2); ?></span></li>
                                        <?php endif; ?>
                                        <li>Total <span>₹<?php echo number_format($cart_total, 2); ?></span></li>
                                    </ul>
                                    <div class="checkout__input__checkbox">
                                        <label for="bank-transfer">
                                            Direct Bank Transfer
                                            <input type="radio" id="bank-transfer" name="payment_method" value="bank-transfer" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] == 'bank-transfer') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <p>Make your payment directly into our bank account.</p>
                                    <div class="checkout__input__checkbox">
                                        <label for="cod">
                                            Cash on Delivery
                                            <input type="radio" id="cod" name="payment_method" value="cod" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cod') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <p>Pay with cash upon delivery.</p>
                                    <div class="checkout__input__checkbox">
                                        <label for="paypal">
                                            Paypal
                                            <input type="radio" id="paypal" name="payment_method" value="paypal" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'paypal') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <p>Pay via PayPal.</p>
                                    <div class="checkout__input__checkbox">
                                        <label for="credit-card">
                                            Credit Card
                                            <input type="radio" id="credit-card" name="payment_method" value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'credit_card') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <p>Pay securely with your credit card.</p>
                                    <div class="checkout__input__checkbox">
                                        <label for="terms">
                                            I’ve read & accept the <a href="terms&conditions.html">terms & conditions</a>
                                            <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="cart_subtotal" value="<?php echo $cart_subtotal; ?>">
                                    <input type="hidden" name="shipping_cost" value="<?php echo $shipping_cost; ?>">
                                    <input type="hidden" name="virtual_money_discount" value="<?php echo $virtual_money_discount; ?>">
                                    <input type="hidden" name="coupon_discount" value="<?php echo $coupon_discount; ?>">
                                    <input type="hidden" name="cart_total" value="<?php echo $cart_total; ?>">
                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon_id; ?>">
                                    <input type="hidden" name="shipping_rate_id" value="<?php echo $shipping_rate_id; ?>">
                                    <button type="submit" name="complete_checkout" class="site-btn">PLACE ORDER</button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

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