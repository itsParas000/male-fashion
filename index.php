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

include("php/config.php");
if (!isset($_SESSION['valid']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch a featured product for the countdown deal
$featured_product = null;
$sql_featured = "SELECT id, name, price, old_price, image 
                 FROM products 
                 WHERE is_active = 1 AND old_price > price AND old_price > 0 
                 ORDER BY (old_price - price) DESC 
                 LIMIT 1";
$stmt_featured = $con->prepare($sql_featured);
$stmt_featured->execute();
$result_featured = $stmt_featured->get_result();
if ($result_featured->num_rows > 0) {
    $featured_product = $result_featured->fetch_assoc();
}
$stmt_featured->close();

// Set countdown end date (10 days from now)
$today = new DateTime();
$today->modify('+10 days');
$countdown_date = $today->format('m/d/Y');
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Male_Fashion Template">
    <meta name="keywords" content="Male_Fashion, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home | Male-Fashion</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">

    <style>
        .carousel-item {
            height: 800px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        .hero .carousel-caption {
            text-align: left;
            top: 230px;
            bottom: auto;
            color: #111111;
            padding: 0 15px;
        }
        .hero .carousel-caption h6 {
            color: #e53637;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 28px;
        }
        .hero .carousel-caption h2 {
            font-size: 48px;
            font-weight: 700;
            line-height: 58px;
            margin-bottom: 30px;
        }
        .hero .carousel-caption p {
            font-size: 16px;
            line-height: 28px;
            margin-bottom: 35px;
            color: #3d3d3d;
        }
        .hero__social {
            margin-top: 190px;
        }
        .hero__social a {
            font-size: 16px;
            color: #3d3d3d;
            margin-right: 32px;
        }
        .carousel-control-prev, .carousel-control-next {
            width: 75px;
            opacity: 0.7;
            background: none;
            z-index: 10;
        }
        .carousel-control-prev-icon, .carousel-control-next-icon {
            font-size: 36px;
            color: #333333;
            background: none;
        }
        /* Wishlist and Product Styling */
        .wishlist-btn {
            font-size: 20px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .wishlist-btn.filled {
            color: #ff0000;
        }
        .action-message {
            display: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        /* Categories Section Styling */
        .categories__deal__countdown__timer {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .cd-item {
            text-align: center;
            min-width: 70px;
        }
        .cd-item span {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #e53637;
        }
        .cd-item p {
            margin: 0;
            font-size: 14px;
            color: #3d3d3d;
        }
        /* Responsive Styles */
        @media (max-width: 991px) {
            .carousel-item {
                height: 600px;
            }
            .hero .carousel-caption {
                top: 150px;
            }
            .hero .carousel-caption h2 {
                font-size: 36px;
                line-height: 48px;
            }
            .hero__social {
                margin-top: 100px;
            }
            .banner__item {
                margin-bottom: 20px;
            }
            .categories__text, .categories__hot__deal, .categories__deal__countdown {
                margin-bottom: 20px;
            }
            .instagram__pic {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .instagram__pic__item {
                flex: 1 1 30%;
                min-width: 120px;
            }
        }
        @media (max-width: 767px) {
            .carousel-item {
                height: 500px;
                padding-top: 100px;
                padding-bottom: 40px;
            }
            .hero .carousel-caption {
                top: 80px;
                text-align: center;
            }
            .hero .carousel-caption h6 {
                font-size: 12px;
                margin-bottom: 20px;
            }
            .hero .carousel-caption h2 {
                font-size: 28px;
                line-height: 36px;
            }
            .hero .carousel-caption p {
                font-size: 14px;
                line-height: 24px;
                margin-bottom: 20px;
            }
            .hero__social {
                margin-top: 50px;
                text-align: center;
            }
            .hero__social a {
                margin: 0 15px;
            }
            .carousel-control-prev, .carousel-control-next {
                width: 50px;
                top: auto;
                bottom: 20px;
                transform: translateY(0);
            }
            .carousel-control-prev {
                left: 10px;
            }
            .carousel-control-next {
                right: 10px;
                left: auto;
            }
            .carousel-control-prev-icon, .carousel-control-next-icon {
                font-size: 24px;
            }
            .product__filter .col-lg-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .banner__item__pic img {
                width: 100%;
                height: auto;
            }
            .categories__deal__countdown__timer {
                gap: 10px;
            }
            .cd-item span {
                font-size: 20px;
            }
            .cd-item p {
                font-size: 12px;
            }
        }
        @media (max-width: 479px) {
            .carousel-item {
                height: 400px;
            }
            .hero .carousel-caption h2 {
                font-size: 24px;
                line-height: 32px;
            }
            .hero .carousel-caption p {
                font-size: 12px;
                line-height: 20px;
            }
            .product__filter .col-lg-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .categories__hot__deal img {
                width: 100%;
                height: auto;
            }
            .instagram__pic__item {
                flex: 1 1 45%;
            }
            .blog__item__pic img {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <!-- Hero Section Begin -->
    <section class="hero">
        <div id="heroCarousel" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
                <li data-target="#heroCarousel" data-slide-to="1"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active" style="background-image: url('img/hero/hero-1.jpg');">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-5 col-lg-7 col-md-8">
                                <div class="carousel-caption">
                                    <h6>Summer Collection</h6>
                                    <h2>Fall - Winter Collections 2030</h2>
                                    <p>A specialist label creating luxury essentials. Ethically crafted with an unwavering commitment to exceptional quality.</p>
                                    <a href="shop.php" class="primary-btn">Shop now <span class="arrow_right"></span></a>
                                    <div class="hero__social">
                                        <a href="#"><i class="fa fa-facebook"></i></a>
                                        <a href="#"><i class="fa fa-twitter"></i></a>
                                        <a href="#"><i class="fa fa-pinterest"></i></a>
                                        <a href="#"><i class="fa fa-instagram"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img src="img/hero/hero-1.jpg" alt="Hero 1" class="d-block w-100">
                </div>
                <div class="carousel-item" style="background-image: url('img/hero/hero-2.jpg');">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-5 col-lg-7 col-md-8">
                                <div class="carousel-caption">
                                    <h6>Summer Collection</h6>
                                    <h2>Fall - Winter Collections 2030</h2>
                                    <p>A specialist label creating luxury essentials. Ethically crafted with an unwavering commitment to exceptional quality.</p>
                                    <a href="shop.php" class="primary-btn">Shop now <span class="arrow_right"></span></a>
                                    <div class="hero__social">
                                        <a href="#"><i class="fa fa-facebook"></i></a>
                                        <a href="#"><i class="fa fa-twitter"></i></a>
                                        <a href="#"><i class="fa fa-pinterest"></i></a>
                                        <a href="#"><i class="fa fa-instagram"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img src="img/hero/hero-2.jpg" alt="Hero 2" class="d-block w-100">
                </div>
            </div>
            <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"><i class="fa fa-angle-left"></i></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"><i class="fa fa-angle-right"></i></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </section>
    <!-- Hero Section End -->

    <!-- Banner Section Begin -->
    <section class="banner spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 offset-lg-4">
                    <div class="banner__item">
                        <div class="banner__item__pic">
                            <img src="img/banner/banner-1.jpg" alt="" loading="lazy">
                        </div>
                        <div class="banner__item__text">
                            <h2>Clothing Collections 2030</h2>
                            <a href="shop.php">Shop now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="banner__item banner__item--middle">
                        <div class="banner__item__pic">
                            <img src="img/banner/banner-2.jpg" alt="" loading="lazy">
                        </div>
                        <div class="banner__item__text">
                            <h2>Accessories</h2>
                            <a href="shop.php">Shop now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="banner__item banner__item--last">
                        <div class="banner__item__pic">
                            <img src="img/banner/banner-3.jpg" alt="" loading="lazy">
                        </div>
                        <div class="banner__item__text">
                            <h2>Shoes Spring 2030</h2>
                            <a href="shop.php">Shop now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Banner Section End -->

    <!-- Product Section Begin -->
    <section class="product spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="nav nav-tabs filter__controls" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="best-sellers-tab" data-toggle="tab" href="#best-sellers" role="tab">Best Sellers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="new-arrivals-tab" data-toggle="tab" href="#new-arrivals" role="tab">New Arrivals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="hot-sales-tab" data-toggle="tab" href="#hot-sales" role="tab">Hot Sales</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <?php
                $user_id = $_SESSION['user_id'];

                // Get user's wishlist items
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

                // Fetch all active products once
                $sql = "SELECT p.id, p.name, p.price, p.old_price, p.image, p.stock_quantity, p.is_active, p.created_at, c.name AS category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.is_active = 1";
                $stmt = $con->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                $all_products = [];
                while ($row = $result->fetch_assoc()) {
                    $all_products[] = $row;
                }
                $stmt->close();

                // Define tab configurations
                $categories = [
                    'best_sellers' => [
                        'id' => 'best-sellers',
                        'title' => 'Best Sellers',
                    ],
                    'new_arrivals' => [
                        'id' => 'new-arrivals',
                        'title' => 'New Arrivals',
                    ],
                    'hot_sales' => [
                        'id' => 'hot-sales',
                        'title' => 'Hot Sales',
                    ],
                ];

                // Track product usage to minimize overlap
                $product_usage = array_fill_keys(array_column($all_products, 'id'), 0);
                $tab_products = [];

                // Assign products to each tab
                foreach ($categories as $key => $category) {
                    $products = $all_products;
                    usort($products, function($a, $b) use ($key, $product_usage) {
                        if ($key === 'best_sellers') {
                            $score_a = $a['price'] - ($product_usage[$a['id']] * 100);
                            $score_b = $b['price'] - ($product_usage[$b['id']] * 100);
                        } elseif ($key === 'new_arrivals') {
                            $time_a = strtotime($a['created_at'] ?: '1970-01-01');
                            $time_b = strtotime($b['created_at'] ?: '1970-01-01');
                            $score_a = $time_a - ($product_usage[$a['id']] * 1000000);
                            $score_b = $time_b - ($product_usage[$b['id']] * 1000000);
                        } else {
                            $discount_a = ($a['old_price'] > $a['price'] && $a['old_price'] > 0) ? ($a['old_price'] - $a['price']) : 0;
                            $discount_b = ($b['old_price'] > $b['price'] && $b['old_price'] > 0) ? ($b['old_price'] - $b['price']) : 0;
                            $score_a = $discount_a - ($product_usage[$a['id']] * 100);
                            $score_b = $discount_b - ($product_usage[$b['id']] * 100);
                        }
                        return $score_b <=> $score_a;
                    });

                    $selected = [];
                    foreach ($products as $product) {
                        if (count($selected) < 6) {
                            if ($key === 'hot_sales' && ($product['old_price'] <= $product['price'] || $product['old_price'] <= 0)) {
                                continue;
                            }
                            $selected[] = $product;
                            $product_usage[$product['id']]++;
                        }
                    }

                    if (count($selected) < 6) {
                        foreach ($products as $product) {
                            if (!in_array($product, $selected) && count($selected) < 6) {
                                $selected[] = $product;
                                $product_usage[$product['id']]++;
                            }
                        }
                    }

                    $tab_products[$key] = $selected;
                    echo "<!-- Debug: $key products fetched: " . count($selected) . " -->";
                }
                ?>
                <?php foreach ($categories as $key => $category): ?>
                    <div class="tab-pane <?php echo $key === 'best_sellers' ? 'active' : ''; ?>" id="<?php echo $category['id']; ?>" role="tabpanel">
                        <div class="row product__filter">
                            <?php foreach ($tab_products[$key] as $row): 
                                $is_new = (!empty($row['created_at']) && strtotime($row['created_at']) !== false && strtotime($row['created_at']) > strtotime('-30 days'));
                                $is_sale = ($row['old_price'] > $row['price'] && $row['old_price'] > 0);
                            ?>
                                <div class="col-lg-3 col-md-6 col-sm-6 mix <?php echo htmlspecialchars($category['id']); ?>">
                                    <div class="product__item <?php echo $is_sale ? 'sale' : ''; ?>">
                                        <div class="product__item__pic set-bg" data-setbg="assets/<?php echo htmlspecialchars($row['image']); ?>" style="background-image: url('assets/<?php echo htmlspecialchars($row['image']); ?>');">
                                            <?php 
                                            if (!isset($row['stock_quantity']) || $row['stock_quantity'] <= 0) {
                                                echo '<span class="label" style="background-color: #ff4444; color: #fff;">Out of Stock</span>';
                                            } elseif ($is_sale) {
                                                $discount = round((($row['old_price'] - $row['price']) / $row['old_price']) * 100);
                                                echo '<span class="label" style="background-color: #10b981; color: #fff;">' . $discount . '% Off</span>';
                                            } elseif ($is_new) {
                                                echo '<span class="label" style="background-color: #facc15; color: #fff;">New</span>';
                                            }
                                            ?>
                                            <ul class="product__hover">
                                                <li>
                                                    <a href="#">
                                                        <i class="wishlist-btn far fa-heart <?php echo in_array($row['id'], $wishlist_items) ? 'fas filled' : ''; ?>" data-id="<?php echo $row['id']; ?>"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="product__item__text">
                                            <h6><?php echo htmlspecialchars($row['name']); ?></h6>
                                            <a href="product.php?id=<?php echo $row['id']; ?>" class="add-cart">+ Add To Cart</a>
                                            <div class="rating">
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star-half-o"></i>
                                            </div>
                                            <h5>₹<?php echo number_format($row['price'], 2); ?></h5>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Product Section End -->

    <!-- Categories Section Begin -->
    <section class="categories spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="categories__text">
                        <h2>Clothings Hot <br /> <span>Shoe Collection</span> <br /> Accessories</h2>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="categories__hot__deal">
                        <?php if ($featured_product): ?>
                            <img src="assets/<?php echo htmlspecialchars($featured_product['image']); ?>" alt="<?php echo htmlspecialchars($featured_product['name']); ?>" loading="lazy">
                            <div class="hot__deal__sticker">
                                <span>Sale Of</span>
                                <h5>₹<?php echo number_format($featured_product['price'], 2); ?></h5>
                            </div>
                        <?php else: ?>
                            <img src="img/product-sale.png" alt="Default Product" loading="lazy">
                            <div class="hot__deal__sticker">
                                <span>Sale Of</span>
                                <h5>₹29.99</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 offset-lg-1">
                    <div class="categories__deal__countdown">
                        <span>Deal Of The Week</span>
                        <h2><?php echo $featured_product ? htmlspecialchars($featured_product['name']) : 'Multi-pocket Chest Bag Black'; ?></h2>
                        <div class="categories__deal__countdown__timer" id="countdown">
                            <!-- Countdown will be populated by JavaScript -->
                        </div>
                        <a href="<?php echo $featured_product ? 'product.php?id=' . $featured_product['id'] : '#'; ?>" class="primary-btn">Shop now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Categories Section End -->

    <!-- Instagram Section Begin -->
    <section class="instagram spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="instagram__pic">
                        <div class="instagram__pic__item set-bg" data-setbg="img/instagram/instagram-1.jpg"></div>
                        <div class="instagram__pic__item set-bg" data-setbg="img/instagram/instagram-2.jpg"></div>
                        <div class="instagram__pic__item set-bg" data-setbg="img/instagram/instagram-3.jpg"></div>
                        <div class="instagram__pic__item set-bg" data-setbg="img/instagram/instagram-4.jpg"></div>
                        <div class="instagram__pic__item set-bg" data-setbg="img/instagram/instagram-5.jpg"></div>
                        <div class="instagram__pic__item set-bg" data-setbg="img/instagram/instagram-6.jpg"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="instagram__text">
                        <h2>Instagram</h2>
                        <p>Follow us on Instagram for the latest fashion trends, exclusive sneak peeks, and style inspiration straight from Male Fashion!</p>
                        <h3>#Male_Fashion</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Instagram Section End -->

    <!-- Latest Blog Section Begin -->
    <section class="latest spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <span>Latest News</span>
                        <h2>Fashion New Trends</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="blog__item">
                        <div class="blog__item__pic set-bg" data-setbg="img/blog/blog-1.jpg"></div>
                        <div class="blog__item__text">
                            <span><img src="img/icon/calendar.png" alt=""> 16 February 2025</span>
                            <h5>Top 5 Must-Have Jackets for Winter 2025</h5>
                            <a href="blog.php?id=1">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="blog__item">
                        <div class="blog__item__pic set-bg" data-setbg="img/blog/blog-2.jpg"></div>
                        <div class="blog__item__text">
                            <span><img src="img/icon/calendar.png" alt=""> 21 February 2025</span>
                            <h5>How to Style Sneakers for Every Occasion</h5>
                            <a href="blog.php?id=2">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="blog__item">
                        <div class="blog__item__pic set-bg" data-setbg="img/blog/blog-3.jpg"></div>
                        <div class="blog__item__text">
                            <span><img src="img/icon/calendar.png" alt=""> 28 February 2025</span>
                            <h5>The Rise of Sustainable Fashion in 2025</h5>
                            <a href="blog.php?id=3">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Latest Blog Section End -->

    <?php include 'footer.php'; ?>
    <!-- Js Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7XgwQHe6OaUs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main1.js"></script>
    <script>
        history.pushState(null, document.title, location.href);
        window.addEventListener('popstate', function(event) {
            history.pushState(null, document.title, location.href);
        });

        const menuBtn = document.getElementById('menu-btn');
        const navLinks = document.querySelector('.nav__links');
        if (menuBtn && navLinks) {
            menuBtn.addEventListener('click', () => {
                navLinks.classList.toggle('open');
            });
        }

        // Initialize countdown timer for Deal of the Week
        $(document).ready(function() {
            $("#countdown").countdown("<?php echo $countdown_date; ?>", function(event) {
                $(this).html(event.strftime(
                    "<div class='cd-item'><span>%D</span><p>Days</p></div>" +
                    "<div class='cd-item'><span>%H</span><p>Hours</p></div>" +
                    "<div class='cd-item'><span>%M</span><p>Minutes</p></div>" +
                    "<div class='cd-item'><span>%S</span><p>Seconds</p></div>"
                ));
            });
        });
    </script>

    <!-- Wishlist Functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        const messageDiv = document.createElement('div');
        messageDiv.id = 'action-message';
        messageDiv.className = 'action-message';
        const productContainer = document.querySelector('.product.spad .container');
        if (productContainer) {
            productContainer.prepend(messageDiv);
        }

        wishlistButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                const productId = button.dataset.id;
                const isFilled = button.classList.contains('filled');

                if (isFilled) {
                    messageDiv.textContent = "Already in wishlist! Remove from wishlist page.";
                    messageDiv.className = 'action-message success';
                    messageDiv.style.display = 'block';
                    setTimeout(() => messageDiv.style.display = 'none', 3000);
                    return;
                }

                const url = `wishlist.php?add=${productId}`;
                try {
                    const response = await fetch(url, { method: 'GET' });
                    const data = await response.text();

                    if (data.includes("added")) {
                        button.classList.remove('far');
                        button.classList.add('fas', 'filled');
                        messageDiv.textContent = "Added to wishlist!";
                        messageDiv.className = 'action-message success';
                        messageDiv.style.display = 'block';
                        setTimeout(() => messageDiv.style.display = 'none', 3000);
                    } else if (data.includes("already")) {
                        button.classList.remove('far');
                        button.classList.add('fas', 'filled');
                        messageDiv.textContent = "Already in wishlist!";
                        messageDiv.className = 'action-message success';
                        messageDiv.style.display = 'block';
                        setTimeout(() => messageDiv.style.display = 'none', 3000);
                    } else {
                        throw new Error("Unexpected response: " + data);
                    }
                } catch (err) {
                    messageDiv.textContent = 'An error occurred';
                    messageDiv.className = 'action-message error';
                    messageDiv.style.display = 'block';
                    console.error('Error:', err);
                    setTimeout(() => messageDiv.style.display = 'none', 3000);
                }
            });
        });
    });
    </script>
</body>
</html>