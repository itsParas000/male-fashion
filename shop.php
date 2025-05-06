<?php
session_name('SESSION_USER');
session_start();
include 'php/config.php';

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Base SQL query for products
$sql = "SELECT p.id, p.name, p.price, p.old_price, p.description, p.image, p.stock_quantity, p.is_active, p.category_name 
        FROM products p 
        WHERE p.is_active = 1";
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE p.is_active = 1";
$where_conditions = [];
$params = [];
$types = "";

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR FIND_IN_SET(?, p.color) OR FIND_IN_SET(?, p.size))";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

// Category filter
$categories = isset($_GET['categories']) ? $_GET['categories'] : [];
if (!empty($categories)) {
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $where_conditions[] = "p.category_name IN ($placeholders)";
    $params = array_merge($params, $categories);
    $types .= str_repeat('s', count($categories));
}

// Color filter
$colors = isset($_GET['colors']) ? array_map('strtolower', $_GET['colors']) : [];
if (!empty($colors)) {
    $color_conditions = [];
    foreach ($colors as $color) {
        $color_conditions[] = "FIND_IN_SET(?, LOWER(p.color))";
        $params[] = $color;
        $types .= "s";
    }
    if (!empty($color_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $color_conditions) . ")";
    }
}

// Size filter
$sizes = isset($_GET['sizes']) ? $_GET['sizes'] : [];
if (!empty($sizes)) {
    $size_conditions = [];
    foreach ($sizes as $size) {
        $size_conditions[] = "FIND_IN_SET(?, p.size)";
        $params[] = $size;
        $types .= "s";
    }
    if (!empty($size_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $size_conditions) . ")";
    }
}

// Price filter
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 10;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
$where_conditions[] = "p.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= "dd";

// Add WHERE conditions to queries
if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
    $count_sql .= " AND " . implode(" AND ", $where_conditions);
}

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

// Pagination
$items_per_page = 9;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Count total items
$count_stmt = $con->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Add limit to main query
$sql .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $items_per_page;
$types .= "ii";

// Execute main query
$stmt = $con->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

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

// Fetch all distinct categories and their product counts
$cat_sql = "SELECT c.name AS category_name, 
            (SELECT COUNT(*) FROM products p WHERE p.category_name = c.name AND p.is_active = 1) as product_count 
            FROM categories c";
$cat_result = $con->query($cat_sql);
$all_categories = [];
while ($cat_row = $cat_result->fetch_assoc()) {
    $all_categories[] = $cat_row;
}

// Fetch colors from products table and normalize
$color_sql = "SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND is_active = 1";
$color_result = $con->query($color_sql);
$all_colors = [];
while ($color_row = $color_result->fetch_assoc()) {
    $color_list = array_filter(array_map('trim', explode(',', $color_row['color'])));
    $all_colors = array_merge($all_colors, $color_list);
}
$all_colors = array_unique(array_map('strtolower', $all_colors)); // Normalize to lowercase
$all_colors = array_map('ucfirst', $all_colors); // Capitalize first letter for display

$size_sql = "SELECT DISTINCT size FROM products WHERE size IS NOT NULL";
$size_result = $con->query($size_sql);
$all_sizes = [];
while ($size_row = $size_result->fetch_assoc()) {
    $size_list = array_filter(array_map('trim', explode(',', $size_row['size'])));
    $all_sizes = array_merge($all_sizes, $size_list);
}
$all_sizes = array_unique($all_sizes);
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Male_Fashion Template">
    <meta name="keywords" content="Male_Fashion, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Shop | Male-Fashion</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">

    <!-- Sidebar and Wishlist Styling -->
    <style>
        /* Sidebar Styling from shop.html */
        .shop__sidebar {
            padding: 20px 0;
        }

        .shop__sidebar__search {
            margin-bottom: 30px;
            position: relative;
        }

        .shop__sidebar__search form {
            display: flex;
        }

        .shop__sidebar__search input {
            width: 100%;
            height: 46px;
            border: 1px solid #e1e1e1;
            font-size: 14px;
            color: #b7b7b7;
            padding-left: 20px;
        }

        .shop__sidebar__search input::placeholder {
            color: #b7b7b7;
        }

        .shop__sidebar__search button {
            font-size: 14px;
            color: #ffffff;
            background: #000000;
            border: none;
            padding: 0 15px;
            height: 46px;
            line-height: 46px;
        }

        .shop__sidebar__accordion .card {
            border: none;
            border-bottom: 1px solid #e1e1e1 !important;
            margin-bottom: 0;
        }

        .shop__sidebar__accordion .card-heading {
            font-size: 16px;
            color: #111111;
            font-weight: 700;
            text-transform: uppercase;
            padding: 18px 0 13px;
        }

        .shop__sidebar__accordion .card-heading a {
            color: #111111;
            display: block;
        }

        .shop__sidebar__categories ul {
            list-style: none;
            padding: 0;
        }

        .shop__sidebar__categories ul li {
            font-size: 15px;
            color: #252525;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .shop__sidebar__categories ul li .product-count {
            font-size: 12px;
            color: #252525;
        }

        .shop__sidebar__categories ul li label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .shop__sidebar__categories ul li input {
            margin-right: 8px;
        }

        .shop__sidebar__price ul li {
            list-style: none;
            font-size: 15px;
            color: #252525;
            padding: 10px 0;
        }

        .shop__sidebar__price ul li a {
            color: #252525;
            text-decoration: none;
        }

        .shop__sidebar__size label {
            font-size: 15px;
            color: #252525;
            text-transform: uppercase;
            border: 1px solid #e5e5e5;
            display: inline-block;
            padding: 7px 13px 5px;
            margin-right: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            position: relative;
        }

        .shop__sidebar__size input {
            position: absolute;
            opacity: 0;
        }

        .shop__sidebar__size input:checked + span {
            background: #ffffff;
            color: #252525;
        }

        .shop__sidebar__size input:checked + span:after {
            content: "\f00c";
            font-family: "FontAwesome";
            position: absolute;
            font-size: 12px;
            color: #000000;
        }

        .shop__sidebar__color label {
            height: 30px;
            width: 30px;
            border-radius: 50%;
            position: relative;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
            cursor: pointer;
        }

        .shop__sidebar__color input {
            position: absolute;
            opacity: 0;
        }

        .shop__sidebar__color input:checked + span:after {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: "FontAwesome";
            content: "\f00c";
            color: #ffffff;
            font-size: 12px;
        }

        .shop__sidebar__color label.fallback {
            background: #ccc; /* Default fallback color */
        }

        .shop__sidebar__color .c-1 { background: #0b0909; }
        .shop__sidebar__color .c-2 { background: #f9f1e7; }
        .shop__sidebar__color .c-3 { background: #ff0000; }
        .shop__sidebar__color .c-4 { background: #00ff00; }
        .shop__sidebar__color .c-5 { background: #0000ff; }
        .shop__sidebar__color .c-6 { background: #ffff00; }
        .shop__sidebar__color .c-7 { background: #ff00ff; }
        .shop__sidebar__color .c-8 { background: #00ffff; }
        .shop__sidebar__color .c-9 { background: #800080; }

        .clear-filter {
            display: block;
            margin-top: 10px;
            font-size: 14px;
            color: #252525;
            text-align: center;
            text-decoration: none;
        }

        .clear-filter:hover {
            color: #000000;
            text-decoration: underline;
        }

        /* Preserve original product and wishlist styling */
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

        .product__item {
            margin-bottom: 30px;
        }

        .product__item__pic {
            position: relative;
            overflow: hidden;
        }

        .product__item__pic .label {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 3px;
        }

        .product__hover {
            position: absolute;
            top: 10px;
            right: 10px;
            list-style: none;
        }

        .product__item__text {
            text-align: center;
            padding-top: 15px;
        }

        .product__item__text h6 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .product__item__text h5 {
            font-size: 18px;
            font-weight: 700;
            color: #111;
        }

        .rating i {
            color: #facc15;
        }

        /* Chatbot Styling */
        .chatbot-icon {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #e53637;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .chatbot-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .chatbot-icon img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
        }

        .chatbot-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 360px;
            height: 480px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 1000;
            font-family: 'Nunito Sans', sans-serif;
            overflow: hidden;
        }

        .chatbot-header {
            background: #111111;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chatbot-header .title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .chatbot-header .close-btn, .chatbot-header .clear-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #ffffff;
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .chatbot-header .close-btn {
            font-size: 20px;
            width: 28px;
            height: 28px;
            line-height: 28px;
        }
        .chatbot-header .close-btn:hover, .chatbot-header .clear-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chatbot-body {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
            scrollbar-width: thin;
            scrollbar-color: #e53637 #f9f9f9;
        }
        .chatbot-body::-webkit-scrollbar {
            width: 6px;
        }
        .chatbot-body::-webkit-scrollbar-thumb {
            background: #e53637;
            border-radius: 10px;
        }

        .chat-message {
            margin: 12px 0;
            padding: 12px 18px;
            border-radius: 12px;
            max-width: 80%;
            position: relative;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }
        .chat-message:hover {
            transform: translateY(-2px);
        }
        .user-message {
            background: #6b48ff;
            color: #fff;
            width: 65%;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 10px 10px auto;
            align-self: flex-end;
        }
        .ai-message {
            background: rgb(241, 241, 241);
            color: #2d3748;
            align-self: flex-start;
            width: 65%;
            margin: 10px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
        }
        .chat-message .time {
            font-size: 10px;
            color: #a0aec0;
            text-align: right;
            margin-top: 2px;
        }
        .chat-message .response-time {
            font-size: 9px;
            color: #a0aec0;
            text-align: right;
            margin-top: 1px;
        }
        .chat-message .delete-link {
            position: absolute;
            top: 2px;
            right: 5px;
            background: none;
            border: none;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            display: none;
            line-height: 1;
            padding: 0;
            text-decoration: none;
        }
        .chat-message:hover .delete-link {
            display: block;
        }

        .chatbot-input {
            display: flex;
            padding: 12px;
            background: #ffffff;
            border-radius: 0 0 12px 12px;
            border-top: 1px solid #f1f1f1;
        }
        .chatbot-input input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            font-family: 'Nunito Sans', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .chatbot-input input:focus {
            border-color: #e53637;
            box-shadow: 0 0 0 2px rgba(229, 54, 55, 0.2);
        }
        .chatbot-input button {
            margin-left: 10px;
            padding: 10px 20px;
            background: #e53637;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Nunito Sans', sans-serif;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .chatbot-input button:hover {
            background: #c9302c;
            transform: translateY(-1px);
        }
        .chatbot-input button:disabled {
            background: #d6d6d6;
            transform: none;
        }

        .typing-indicator {
            font-style: italic;
            color: #3d3d3d;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 8px;
            margin: 10px 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chat-message {
            animation: fadeIn 0.4s ease-out;
        }

        @media (max-width: 767px) {
            .chatbot-window {
                width: 90%;
                height: 80vh;
                bottom: 20px;
                right: 5%;
            }
            .chatbot-icon {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
            }
            .chatbot-icon img {
                width: 25px;
                height: 25px;
            }
        }
    </style>
</head>
<body>
<?php include "header.php"?>

    <!-- Breadcrumb Section -->
    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>Shop</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span>Shop</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop Section -->
    <section class="shop spad">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="shop__sidebar">
                        <div class="shop__sidebar__search">
                            <form method="GET" action="">
                                <input type="text" name="search" placeholder="Search by name, color, or size..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit"><span class="icon_search"></span></button>
                            </form>
                        </div>
                        <div class="shop__sidebar__accordion">
                            <form method="GET" action="">
                                <div class="accordion" id="accordionExample">
                                    <div class="card">
                                        <div class="card-heading">
                                            <a data-toggle="collapse" data-target="#collapseOne">Categories</a>
                                        </div>
                                        <div id="collapseOne" class="collapse show" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="shop__sidebar__categories">
                                                    <ul class="nice-scroll">
                                                        <?php
                                                        foreach ($all_categories as $category) {
                                                            $category_name = htmlspecialchars($category['category_name']);
                                                            $checked = in_array($category_name, $categories) ? 'checked' : '';
                                                            echo "<li><label><input type='checkbox' name='categories[]' value='$category_name' $checked>$category_name</label><span class='product-count'>{$category['product_count']}</span></li>";
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-heading">
                                            <a data-toggle="collapse" data-target="#collapseThree">Filter Price</a>
                                        </div>
                                        <div id="collapseThree" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="shop__sidebar__price">
                                                    <input class="w-full mb-2" type="range" min="10" max="10000" value="<?php echo $max_price; ?>" name="max_price" oninput="this.nextElementSibling.children[1].textContent = '₹' + this.value">
                                                    <div class="flex justify-between text-sm">
                                                        <span>Price ₹10</span>
                                                        <span>₹<?php echo $max_price; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-heading">
                                            <a data-toggle="collapse" data-target="#collapseFour">Size</a>
                                        </div>
                                        <div id="collapseFour" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="shop__sidebar__size">
                                                    <?php
                                                    foreach ($all_sizes as $size) {
                                                        $checked = in_array($size, $sizes) ? 'checked' : '';
                                                        $size_id = htmlspecialchars($size);
                                                        echo "<label for='size-$size_id'><input type='checkbox' id='size-$size_id' name='sizes[]' value='$size_id' $checked><span>$size</span></label>";
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-heading">
                                            <a data-toggle="collapse" data-target="#collapseFive">Colors</a>
                                        </div>
                                        <div id="collapseFive" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                            <div class="shop__sidebar__color">
    <?php
    // Extended color map with fallback for unknown colors
    $color_map = [
        'Black' => 'c-1', 'White' => 'c-2', 'Red' => 'c-3', 'Green' => 'c-4',
        'Blue' => 'c-5', 'Yellow' => 'c-6', 'Magenta' => 'c-7', 'Cyan' => 'c-8',
        'Purple' => 'c-9', 'Silver' => 'c-2', 'Gold' => 'c-6', 'Brown' => 'c-1'
    ];
    // Fallback colors for dynamic assignment
    $fallback_colors = ['c-1', 'c-2', 'c-3', 'c-4', 'c-5', 'c-6', 'c-7', 'c-8', 'c-9'];
    $fallback_index = 0;

    foreach ($all_colors as $color) {
        $checked = in_array($color, $colors) ? 'checked' : '';
        $color_id = htmlspecialchars($color);
        // Assign a class from color_map or fallback
        $color_class = isset($color_map[$color]) ? $color_map[$color] : $fallback_colors[$fallback_index % count($fallback_colors)];
        $fallback_index++;
        echo "<label class='$color_class' for='color-$color_id' title='$color_id'><input type='checkbox' id='color-$color_id' name='colors[]' value='$color_id' $checked><span></span></label>";
    }
    ?>
</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" style="background: #000000; color: #ffffff; border: none; padding: 10px 20px; margin-top: 20px; width: 100%; cursor: pointer;">Filter</button>
                                <a href="shop.php" class="clear-filter">Clear Filters</a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="col-lg-9">
                    <div class="shop__product__option">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="shop__product__option__left">
                                    <p>Showing <?php echo min($offset + 1, $total_items); ?>–<?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> results</p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="shop__product__option__right">
                                    <p>Sort by:</p>
                                    <select name="sort" onchange="this.form.submit()" form="filterForm">
                                        <option value="default" <?php echo $sort == 'default' ? 'selected' : ''; ?>>Default</option>
                                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Low To High</option>
                                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>High To Low</option>
                                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name A-Z</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="action-message" class="action-message"></div>
                    <div class="row">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="product__item">
                                    <div class="product__item__pic set-bg" data-setbg="assets/<?php echo htmlspecialchars($row['image']); ?>" style="background-image: url('assets/<?php echo htmlspecialchars($row['image']); ?>');">
                                        <?php 
                                        $old_price = floatval($row['old_price']);
                                        $price = floatval($row['price']);
                                        if (!isset($row['stock_quantity']) || $row['stock_quantity'] <= 0): ?>
                                            <span class="label" style="background-color: #ff4444; color: #fff;">Out of Stock</span>
                                        <?php elseif ($old_price > $price && $old_price > 0): ?>
                                            <span class="label" style="background-color: #10b981; color: #fff;"><?php echo round((($old_price - $price) / $old_price) * 100); ?>% Off</span>
                                        <?php elseif ($row['id'] % 2 == 0): ?>
                                            <span class="label" style="background-color: #facc15; color: #fff;">Best Price</span>
                                        <?php endif; ?>
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
                        <?php endwhile; ?>
                    </div>
                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="product__pagination">
                                <?php for ($i = 1; $i <= min(4, $total_pages); $i++): ?>
                                    <?php 
                                    $query_string = "?page=$i";
                                    if (!empty($search)) $query_string .= "&search=" . urlencode($search);
                                    if (!empty($categories)) $query_string .= '&categories=' . implode(',', array_map('urlencode', $categories));
                                    if (!empty($colors)) $query_string .= '&colors=' . implode(',', array_map('urlencode', $colors));
                                    if (!empty($sizes)) $query_string .= '&sizes=' . implode(',', array_map('urlencode', $sizes));
                                    $query_string .= "&max_price=$max_price&sort=$sort";
                                    ?>
                                    <a href="<?php echo $query_string; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                                <?php endfor; ?>
                                <?php if ($total_pages > 4): ?>
                                    <span>...</span>
                                    <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&max_price=<?php echo $max_price; ?>&sort=<?php echo $sort; ?>"><?php echo $total_pages; ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Chatbot -->
    <div class="chatbot-icon" id="chatbot-icon">
        <img src="https://cdn-icons-png.flaticon.com/512/4712/4712109.png" alt="Chatbot Icon">
    </div>

    <div class="chatbot-window" id="chatbot-window">
        <div class="chatbot-header">
            <span class="title">Fashion Assistant</span>
            <div>
                <button class="clear-btn" id="clear-btn">Clear</button>
                <button class="close-btn" id="close-btn">×</button>
            </div>
        </div>
        <div class="chatbot-body" id="chatbot-body"></div>
        <div class="chatbot-input">
            <input type="text" id="chatbot-input" placeholder="Ask about fashion, products, or more...">
            <button id="chatbot-send">Send</button>
        </div>
    </div>

    <!-- Search Model -->
    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch">+</div>
            <form class="search-model-form">
                <input type="text" id="search-input" placeholder="Search here.....">
            </form>
        </div>
    </div>

    <!-- Hidden Form for Filters -->
    <form id="filterForm" method="GET" action="">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <?php foreach ($categories as $category): ?>
            <input type="hidden" name="categories[]" value="<?php echo htmlspecialchars($category); ?>">
        <?php endforeach; ?>
        <?php foreach ($colors as $color): ?>
            <input type="hidden" name="colors[]" value="<?php echo htmlspecialchars($color); ?>">
        <?php endforeach; ?>
        <?php foreach ($sizes as $size): ?>
            <input type="hidden" name="sizes[]" value="<?php echo htmlspecialchars($size); ?>">
        <?php endforeach; ?>
        <input type="hidden" name="max_price" value="<?php echo $max_price; ?>">
    </form>

    <!-- JS Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7XgwQHe6OaUs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/mixitup@3.3.1/dist/mixitup.min.js" integrity="sha256-q4HLmF7zMEP2nKPVszwVUWZaTShZFr8jX691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main.js"></script>

    <!-- Wishlist Functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        const messageDiv = document.getElementById('action-message');

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

    <!-- Chatbot Functionality -->
    <script>
    const chatbotIcon = document.getElementById('chatbot-icon');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatBody = document.getElementById('chatbot-body');
    const chatInput = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const closeBtn = document.getElementById('close-btn');
    const clearBtn = document.getElementById('clear-btn');

    // Toggle chatbot window
    chatbotIcon.addEventListener('click', () => {
        chatbotWindow.style.display = 'flex';
        chatbotIcon.style.display = 'none';
        loadChatHistory();
    });
    closeBtn.addEventListener('click', () => {
        chatbotWindow.style.display = 'none';
        chatbotIcon.style.display = 'flex';
    });

    // Load chat history
    function loadChatHistory() {
        fetch('get_chat_history.php')
            .then(response => response.json())
            .then(data => {
                chatBody.innerHTML = '';
                data.messages.forEach(msg => {
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const responseTime = msg.response_time_ms ? `<div class="response-time">Response time: ${msg.response_time_ms}ms</div>` : '';
                    const deleteLink = msg.sender_type === 'user' ? `<a href="#" class="delete-link" data-id="${msg.id}">×</a>` : '';
                    chatBody.innerHTML += `
                        <div class="${msg.sender_type}-message">
                            ${msg.message.replace(/\n/g, '<br>')}
                            ${deleteLink}
                            <div class="time">${time}</div>
                            ${responseTime}
                        </div>`;
                });
                chatBody.scrollTop = chatBody.scrollHeight;
                addDeleteListeners();
            })
            .catch(error => console.error('Error loading chat history:', error));
    }

    // Add delete functionality
    function addDeleteListeners() {
        document.querySelectorAll('.delete-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const messageId = this.dataset.id;
                fetch('delete_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message_id: messageId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadChatHistory();
                    } else {
                        console.error('Deletion failed:', data.error);
                    }
                })
                .catch(error => console.error('Error deleting message:', error));
            });
        });
    }

    // Clear all chat
    clearBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to clear all chat history?')) {
            fetch('delete_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: -1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    chatBody.innerHTML = '<div class="typing-indicator">Chat cleared!</div>';
                    setTimeout(loadChatHistory, 1000);
                } else {
                    console.error('Clear failed:', data.error);
                    chatBody.innerHTML = '<div class="typing-indicator">Failed to clear chat: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error clearing chat:', error);
                chatBody.innerHTML = '<div class="typing-indicator">Failed to clear chat!</div>';
            });
        }
    });

    // Send message
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        chatBody.innerHTML += `
            <div class="user-message">
                ${message}
                <a href="#" class="delete-link" data-id="temp">×</a>
                <div class="time">${time}</div>
            </div>`;
        chatInput.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;
        sendBtn.disabled = true;

        chatBody.innerHTML += `<div class="typing-indicator">AI is thinking...</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

        fetch('get_recommendation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: message })
        })
        .then(response => response.json())
        .then(data => {
            chatBody.querySelector('.typing-indicator').remove();
            const aiTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            chatBody.innerHTML += `
                <div class="ai-message">
                    ${data.message.replace(/\n/g, '<br>')}
                    <div class="time">${aiTime}</div>
                    <div class="response-time">Response time: ${data.response_time_ms}ms</div>
                </div>`;
            chatBody.scrollTop = chatBody.scrollHeight;
            loadChatHistory();
        })
        .catch(error => {
            chatBody.querySelector('.typing-indicator').remove();
            chatBody.innerHTML += `<div class="ai-message">Oops, something went wrong!<div class="time">${time}</div></div>`;
            console.error(error);
        })
        .finally(() => sendBtn.disabled = false);
    }
    </script>
</body>
</html>