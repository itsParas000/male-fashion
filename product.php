<?php
// Secure session settings
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

session_name('SESSION_USER');
session_start();
session_regenerate_id(true);

include 'php/config.php'; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['valid']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? '';
$username = $_SESSION['username'] ?? '';

// Fetch user's wishlist items
$wishlist_sql = "SELECT product_id FROM wishlist WHERE user_id = ?";
$wishlist_stmt = $con->prepare($wishlist_sql);
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();
$wishlist_items = [];
while ($row = $wishlist_result->fetch_assoc()) {
    $wishlist_items[] = $row['product_id'];
}
$wishlist_stmt->close();

// Validate Product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    die("Invalid product ID.");
}

// Fetch Product Details
$sql = "SELECT id, name, price, old_price, description, image, score_and_care_tips, related_products, stock_quantity, color, size 
        FROM products 
        WHERE id = ?";
$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Database error: " . $con->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();

if ($product_result->num_rows === 0) {
    die("Product not found.");
}
$product = $product_result->fetch_assoc();
$stmt->close();

// Parse colors and sizes from comma-separated strings
$colors = !empty($product['color']) ? explode(',', $product['color']) : [];
$sizes = !empty($product['size']) ? explode(',', $product['size']) : [];

// Fetch Product Reviews for all users
$sql_reviews = "SELECT * FROM reviews WHERE product_id = ? AND is_active = 1 ORDER BY created_at DESC";
$stmt_reviews = $con->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $product_id);
$stmt_reviews->execute();
$reviews = $stmt_reviews->get_result();
$stmt_reviews->close();

// Handle "Add to Cart"
if (isset($_GET['add']) && isset($_GET['quantity'])) {
    $product_id_to_add = intval($_GET['add']);
    $quantity = max(1, intval($_GET['quantity']));
    $color = isset($_GET['color']) ? htmlspecialchars(urldecode($_GET['color'])) : '';
    $size = isset($_GET['size']) ? htmlspecialchars(urldecode($_GET['size'])) : '';

    // Validate color and size
    if (empty($color) || !in_array($color, $colors)) {
        $error_message = "Please select a valid color.";
    } elseif (empty($size) || !in_array($size, $sizes)) {
        $error_message = "Please select a valid size.";
    } else {
        // Check if item exists in cart
        $sql_check = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND color = ? AND size = ?";
        $stmt_check = $con->prepare($sql_check);
        $stmt_check->bind_param("iiss", $user_id, $product_id_to_add, $color, $size);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $stmt_check->close();

        if ($result_check->num_rows > 0) {
            $sql_update = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ? AND color = ? AND size = ?";
            $stmt_update = $con->prepare($sql_update);
            $stmt_update->bind_param("iiiss", $quantity, $user_id, $product_id_to_add, $color, $size);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $sql_add = "INSERT INTO cart (user_id, product_id, quantity, color, size, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'active', NOW())";
            $stmt_add = $con->prepare($sql_add);
            $stmt_add->bind_param("iiiss", $user_id, $product_id_to_add, $quantity, $color, $size);
            $stmt_add->execute();
            $stmt_add->close();
        }
        header("Location: cart.php");
        exit();
    }
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comment = htmlspecialchars(trim($_POST['comment']));

    if ($rating < 1 || $rating > 5) {
        die("Invalid rating.");
    }

    if (empty($user_email) || empty($username)) {
        $sql_user = "SELECT email, username FROM users WHERE id = ?";
        $stmt_user = $con->prepare($sql_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $user_email = $user['email'];
            $username = $user['username'] ?? 'Anonymous';
            $_SESSION['email'] = $user_email;
            $_SESSION['username'] = $username;
        } else {
            die("User not found in database.");
        }
        $stmt_user->close();
    }

    $sql_review_insert = "INSERT INTO reviews (product_id, user_id, user_name, email, rating, comment, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt_review_insert = $con->prepare($sql_review_insert);
    if (!$stmt_review_insert) {
        die("Prepare failed: " . $con->error);
    }
    $stmt_review_insert->bind_param("iissis", $product_id, $user_id, $username, $user_email, $rating, $comment);
    $stmt_review_insert->execute();
    $stmt_review_insert->close();

    header("Location: product.php?id=" . $product_id . "&success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .wishlist-btn {
            font-size: 20px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .wishlist-btn.filled {
            color: #e53637;
        }
        .product__hover li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background 0.3s ease;
        }
        .product__hover li a:hover {
            background: #e53637;
        }
        .product__hover li a:hover .wishlist-btn {
            color: #fff;
        }
        .out-of-stock {
            background: #e53637;
            color: #fff;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 3px;
            margin: 10px 0;
        }
        .stock-info {
            font-size: 14px;
            margin: 10px 0;
        }
        .stock-info.in-stock {
            color: #28a745;
        }
        .stock-info.low-stock {
            color: #f39c12;
        }
        .review {
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .review .author {
            font-weight: 600;
            color: #111111;
        }
        .review .rating {
            color: #f1c40f;
        }
        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e1e1e1;
            border-radius: 5px;
        }
        .review-form .primary-btn {
            margin-top: 10px;
        }
        .rating-input i {
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }
        .rating-input i.fas {
            color: #f1c40f;
        }
        .product__details__options {
            margin: 15px 0;
        }
        .product__details__options h6 {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .product__details__options .color-option, .product__details__options .size-option {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .product__details__options .color-option label {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
            border: 1px solid #e1e1e1;
        }
        .product__details__options .size-option label {
            padding: 5px 10px;
            border: 1px solid #e1e1e1;
            border-radius: 3px;
            cursor: pointer;
            background: #fff;
        }
        .product__details__options input[type="radio"] {
            display: none;
        }
        .product__details__options input[type="radio"]:checked + label {
            border-color: #e53637;
        }
        .total-price {
            font-size: 16px;
            color: #e53637;
            margin-top: 10px;
            display: block;
        }
        .pro-qty {
            display: flex;
            align-items: center;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            overflow: hidden;
            width: 120px;
        }
        .pro-qty input {
            width: 100%;
            border: none;
            text-align: center;
            font-size: 14px;
            background: #fff;
        }
        .pro-qty input:focus {
            outline: none;
        }
        @media (max-width: 768px) {
            .pro-qty {
                width: 100px;
            }
            .pro-qty input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="shop-details">
        <div class="product__details__pic">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="product__details__breadcrumb">
                            <a href="./index.php">Home</a>
                            <a href="./shop.php">Shop</a>
                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-3">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">
                                    <div class="product__thumb__pic set-bg" data-setbg="assets/<?php echo htmlspecialchars($product['image']); ?>"></div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6 col-md-9">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__pic__item">
                                    <img src="assets/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product__details__content">
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-lg-8">
                        <div class="product__details__text">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa fa-star<?php echo $i <= 4 ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                                <span> - <?php echo $reviews->num_rows; ?> Reviews</span>
                            </div>
                            <h3>₹<?php echo number_format($product['price'], 2); ?> 
                                <?php if ($product['old_price'] > 0): ?>
                                    <span><?php echo number_format($product['old_price'], 2); ?></span>
                                <?php endif; ?>
                            </h3>
                            <span class="total-price">Total: ₹<?php echo number_format($product['price'], 2); ?></span>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <?php if ($product['stock_quantity'] <= 0): ?>
                                <div class="out-of-stock">Out of Stock</div>
                            <?php else: ?>
                                <div class="stock-info <?php echo $product['stock_quantity'] <= 5 ? 'low-stock' : 'in-stock'; ?>">
                                    <?php echo $product['stock_quantity'] <= 5 ? 'Low Stock: Only ' . $product['stock_quantity'] . ' left!' : 'In Stock: ' . $product['stock_quantity'] . ' available'; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($error_message)): ?>
                                <p style="color: #e53637;"><?php echo htmlspecialchars($error_message); ?></p>
                            <?php endif; ?>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <div class="product__details__options">
                                    <h6>Color:</h6>
                                    <?php if (empty($colors)): ?>
                                        <p>No colors available</p>
                                    <?php else: ?>
                                        <?php foreach ($colors as $index => $color): ?>
                                            <div class="color-option">
                                                <input type="radio" id="color-<?php echo htmlspecialchars($color); ?>" 
                                                       name="color" value="<?php echo htmlspecialchars($color); ?>" 
                                                       <?php echo $index === 0 ? 'checked' : ''; ?> required>
                                                <label for="color-<?php echo htmlspecialchars($color); ?>" 
                                                       style="background-color: <?php echo htmlspecialchars($color); ?>;"></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="product__details__options">
                                    <h6>Size:</h6>
                                    <?php if (empty($sizes)): ?>
                                        <p>No sizes available</p>
                                    <?php else: ?>
                                        <?php foreach ($sizes as $index => $size): ?>
                                            <div class="size-option">
                                                <input type="radio" id="size-<?php echo htmlspecialchars($size); ?>" 
                                                       name="size" value="<?php echo htmlspecialchars($size); ?>" 
                                                       <?php echo $index === 0 ? 'checked' : ''; ?> required>
                                                <label for="size-<?php echo htmlspecialchars($size); ?>">
                                                    <?php echo htmlspecialchars($size); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="product__details__cart__option">
                                    <div class="quantity">
                                        <div class="pro-qty">
                                            <input type="number" value="1" id="quantity" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                        </div>
                                    </div>
                                    <a href="#" class="primary-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</a>
                                </div>
                            <?php endif; ?>
                            <div class="product__details__btns__option">
                                <a href="#" onclick="addToWishlist(<?php echo $product['id']; ?>); return false;">
                                    <i class="wishlist-btn fa fa-heart <?php echo in_array($product['id'], $wishlist_items) ? 'fas filled' : 'far'; ?>" data-id="<?php echo $product['id']; ?>"></i> Add to Wishlist
                                </a>
                                <a href="#" onclick="shareProduct(<?php echo $product['id']; ?>)"><i class="fa fa-share"></i> Share</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="product__details__tab">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tabs-5" role="tab">Description</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-6" role="tab">Reviews (<?php echo $reviews->num_rows; ?>)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-7" role="tab">Score & Care</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tabs-5" role="tabpanel">
                                    <div class="product__details__tab__content">
                                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tabs-6" role="tabpanel">
                                    <div class="product__details__tab__content">
                                        <?php 
                                        $review_count = 0;
                                        while ($review = $reviews->fetch_assoc()): 
                                            $review_count++;
                                            $is_hidden = $review_count > 3 ? 'hidden' : '';
                                        ?>
                                            <div class="review <?php echo $is_hidden; ?>">
                                                <div class="author"><?php echo htmlspecialchars($review['user_name']); ?></div>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fa fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                                <small><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                        <?php endwhile; ?>
                                        <?php if ($review_count > 3): ?>
                                            <a href="#" class="primary-btn" onclick="showMoreReviews()">Show More</a>
                                        <?php endif; ?>
                                        <div class="review-form">
                                            <h5>Add a Review</h5>
                                            <?php if (isset($_GET['success'])): ?>
                                                <p style="color: #28a745;">Thank you for your review!</p>
                                            <?php endif; ?>
                                            <form method="POST">
                                                <div class="rating-input" id="rating-stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="far fa-star" data-value="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <input type="hidden" name="rating" id="rating-value" value="0" required>
                                                <textarea name="comment" placeholder="Your Comment" rows="4" required></textarea>
                                                <button type="submit" class="primary-btn">Submit</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tabs-7" role="tabpanel">
                                    <div class="product__details__tab__content">
                                        <p><?php echo nl2br(htmlspecialchars($product['score_and_care_tips'] ?? 'No score or care tips available.')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="related spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="related-title">Related Products</h3>
                </div>
            </div>
            <div class="row">
                <?php
                if (!empty($product['related_products'])) {
                    $related_ids = explode(',', $product['related_products']);
                    $placeholders = implode(',', array_fill(0, count($related_ids), '?'));
                    $sql_related = "SELECT id, name, price, image FROM products WHERE id IN ($placeholders) LIMIT 4";
                    $stmt_related = $con->prepare($sql_related);
                    $stmt_related->bind_param(str_repeat('i', count($related_ids)), ...array_map('intval', $related_ids));
                    $stmt_related->execute();
                    $related_result = $stmt_related->get_result();
                    while ($related = $related_result->fetch_assoc()):
                ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="product__item">
                            <div class="product__item__pic set-bg" data-setbg="assets/<?php echo htmlspecialchars($related['image']); ?>" style="background-image: url('assets/<?php echo htmlspecialchars($related['image']); ?>');">
                                <ul class="product__hover">
                                    <li>
                                        <a href="#" onclick="addToWishlist(<?php echo $related['id']; ?>); return false;">
                                            <i class="wishlist-btn fa fa-heart <?php echo in_array($related['id'], $wishlist_items) ? 'fas filled' : 'far'; ?>" data-id="<?php echo $related['id']; ?>"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="product__item__text">
                                <h6><?php echo htmlspecialchars($related['name']); ?></h6>
                                <a href="product.php?id=<?php echo $related['id']; ?>" class="add-cart">View Product</a>
                                <h5>₹<?php echo number_format($related['price'], 2); ?></h5>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                    $stmt_related->close();
                }
                ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8     <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7X691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/mixitup@3.3.1/dist/mixitup.min.js" integrity="sha256-q4HLmF7zMEP2nKPVszwVUWZaTShZFr8jX691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main1.js"></script>
    <script>
        const unitPrice = <?php echo $product['price']; ?>;

        function updateTotalPrice() {
            const quantity = parseInt($('#quantity').val()) || 1;
            const totalPrice = quantity * unitPrice;
            $('.total-price').text('Total: ₹' + totalPrice.toFixed(2));
        }

        $(document).ready(function() {
            updateTotalPrice();

            // Handle manual quantity input
            $('#quantity').on('input', function() {
                let value = parseInt($(this).val());
                const maxQuantity = parseInt($(this).attr('max'));
                if (isNaN(value) || value < 1) {
                    $(this).val(1);
                } else if (value > maxQuantity) {
                    $(this).val(maxQuantity);
                }
                updateTotalPrice();
            });
        });

        function addToCart(productId) {
            <?php if ($product['stock_quantity'] > 0): ?>
                const quantity = parseInt($('#quantity').val());
                const maxQuantity = parseInt($('#quantity').attr('max'));
                if (isNaN(quantity) || quantity <= 0 || quantity > maxQuantity) {
                    alert('Invalid quantity. Please select between 1 and ' + maxQuantity);
                    return;
                }
                const color = $('input[name="color"]:checked').val();
                const size = $('input[name="size"]:checked').val();
                if (!color || !size) {
                    alert('Please select a color and size before adding to cart.');
                    return;
                }
                // Validate against available options
                const validColors = <?php echo json_encode($colors); ?>;
                const validSizes = <?php echo json_encode($sizes); ?>;
                if (!validColors.includes(color) || !validSizes.includes(size)) {
                    alert('Invalid color or size selected.');
                    return;
                }
                window.location.href = "product.php?id=" + productId + "&add=" + productId + "&quantity=" + quantity + 
                                      "&color=" + encodeURIComponent(color) + "&size=" + encodeURIComponent(size);
            <?php else: ?>
                alert('This product is out of stock.');
            <?php endif; ?>
        }

        function addToWishlist(productId) {
            const button = $(`.wishlist-btn[data-id="${productId}"]`);
            if (button.hasClass('filled')) {
                alert('Already in wishlist! Remove from wishlist page.');
                return;
            }

            const url = `wishlist.php?add=${productId}`;
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    if (data.includes('added')) {
                        button.removeClass('far').addClass('fas filled');
                        alert('Added to wishlist!');
                    } else if (data.includes('already')) {
                        button.removeClass('far').addClass('fas filled');
                        alert('Product is already in your wishlist!');
                    } else {
                        alert('An error occurred');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Failed to add to wishlist. Please try again.');
                }
            });
        }

        function shareProduct(productId) {
            const shareData = {
                title: '<?php echo htmlspecialchars($product['name']); ?>',
                text: 'Check out this product!',
                url: window.location.href
            };
            if (navigator.share) {
                navigator.share(shareData).then(() => alert('Shared successfully!')).catch(() => fallbackShare());
            } else {
                fallbackShare();
            }
        }

        function fallbackShare() {
            navigator.clipboard.writeText(window.location.href).then(() => alert('Link copied to clipboard!'));
        }

        function setRating(value) {
            $('#rating-stars i').each(function() {
                if (parseInt($(this).data('value')) <= value) {
                    $(this).removeClass('far').addClass('fas');
                } else {
                    $(this).removeClass('fas').addClass('far');
                }
            });
            $('#rating-value').val(value);
        }

        function showMoreReviews() {
            $('.review.hidden').slice(0, 3).removeClass('hidden');
            if ($('.review.hidden').length === 0) {
                $('.primary-btn:contains("Show More")').hide();
            }
        }
    </script>
</body>
</html>