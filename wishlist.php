<?php
include 'php/config.php';
session_name('SESSION_USER');
session_start();

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle adding a product to the cart
if (isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    
    $check = $con->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND status = 'active'");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $stmt = $con->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        $stmt = $con->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ? AND status = 'active'");
        $stmt->bind_param("ii", $user_id, $product_id);
    }
    $stmt->execute();
    
    $remove_stmt = $con->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $remove_stmt->bind_param("ii", $user_id, $product_id);
    $remove_stmt->execute();
    
    echo "Product added to cart and removed from wishlist";
    exit;
}

// Handle removing a product from the wishlist
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    
    $stmt = $con->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    
    echo "Product removed from wishlist";
    exit;
}

// Handle adding a product to the wishlist (for shop compatibility)
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']);
    
    $check = $con->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $stmt = $con->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        echo "Product added to wishlist";
    } else {
        echo "Product already in wishlist";
    }
    exit;
}

// Fetch wishlist items
$sql = "SELECT w.product_id, p.name, p.price, p.image, p.stock_quantity
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Male_Fashion - Wishlist">
    <meta name="keywords" content="Male_Fashion, wishlist, fashion, ecommerce">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Wishlist - Male Fashion</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">
    <style>
        /* Retain wishlist-container styles */
        .wishlist-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .wishlist-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px;
        }

        .wishlist-table th {
            padding: 15px;
            text-transform: uppercase;
            font-size: 14px;
            font-weight: 700;
            color: #111111;
            background: #f3f2ee;
            border-bottom: 2px solid #e53637;
        }

        .wishlist-table td {
            padding: 20px;
            background: #fff;
            transition: all 0.3s ease;
            vertical-align: middle;
        }

        .wishlist-table tr:hover td {
            background: #f9f9f9;
        }

        .wishlist-table img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #111111;
        }

        .price {
            font-size: 16px;
            font-weight: 700;
            color: #e53637;
        }

        .stock-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .in-stock {
            background: #e7f5e7;
            color: #2c662d;
        }
        .out-stock {
            background: #fce8e6;
            color: #dc3545;
        }

        .actions-cell {
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: flex-end;
        }

        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .cart-btn {
            background: #e53637;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cart-btn:hover {
            background: #ca2e2f;
        }
        .cart-btn i {
            font-size: 14px;
        }

        .remove-btn {
            background: #fff;
            color: #e53637;
            border: 1px solid #e53637;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .remove-btn:hover {
            background: #e53637;
            color: #fff;
        }
        .remove-btn i {
            font-size: 16px;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            color: #fff;
            z-index: 2000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.5s ease, slideOut 0.5s ease 2.5s forwards;
        }
        .toast.success { background: #e53637; }
        .toast.error { background: #dc3545; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        .empty-message {
            text-align: center;
            padding: 60px;
            font-size: 20px;
            font-weight: 600;
            color: #3d3d3d;
        }
        .empty-message a {
            color: #e53637;
            text-decoration: none;
            border-bottom: 1px solid #e53637;
        }

        @media (max-width: 768px) {
            .wishlist-table { display: block; overflow-x: auto; }
            .wishlist-table img { width: 80px; height: 80px; }
            .actions-cell { flex-direction: column; gap: 10px; }
        }

        /* Additional styles to align with Male_Fashion theme */
    </style>
</head>

<body>
    <!-- Header Section Begin -->
    <?php include 'header.php'; ?>
    <!-- Header Section End -->

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>wishlist</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span>wishlist</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Wishlist Section Begin -->
    <div class="wishlist-container">
        <table class="wishlist-table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-product-id="<?php echo $row['product_id']; ?>">
                        <td><img src="assets/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>"></td>
                        <td><span class="product-name"><?php echo htmlspecialchars($row['name']); ?></span></td>
                        <td><span class="price">$<?php echo number_format($row['price'], 2); ?></span></td>
                        <td>
                            <span class="stock-status <?php echo $row['stock_quantity'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                <?php echo $row['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <button class="action-btn cart-btn" data-action="add-to-cart" data-id="<?php echo $row['product_id']; ?>">
                                    <i class="fa fa-shopping-cart"></i> Add to Cart
                                </button>
                                <button class="action-btn remove-btn" data-action="remove" data-id="<?php echo $row['product_id']; ?>">
                                    <i class="fa fa-heart"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" class="empty-message">
                            Your wishlist awaits its treasures. <a href="shop.php">Discover Now</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Wishlist Section End -->
    <?PHP include 'footer.php'; ?>
    <!-- JS Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7XgwQHe6OaUs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const actionButtons = document.querySelectorAll('.action-btn');

        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        actionButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                const productId = button.dataset.id;
                const action = button.dataset.action;
                const url = `wishlist.php?${action === 'add-to-cart' ? 'add_to_cart' : 'remove'}=${productId}`;

                try {
                    const response = await fetch(url, { method: 'GET' });
                    const data = await response.text();

                    if (data.includes("added to cart")) {
                        showToast("Added to cart and removed from wishlist", 'success');
                    } else if (data.includes("removed from wishlist")) {
                        showToast("Removed from wishlist", 'success');
                    } else {
                        throw new Error("Unexpected response: " + data);
                    }

                    const row = button.closest('tr');
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(50px)';
                    setTimeout(() => {
                        row.remove();
                        if (!document.querySelector('.wishlist-table tbody tr')) {
                            const tbody = document.querySelector('.wishlist-table tbody');
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" class="empty-message">
                                        Your wishlist awaits its treasures. <a href="shop.php">Discover Now</a>
                                    </td>
                                </tr>
                            `;
                        }
                    }, 500);
                } catch (error) {
                    showToast('Something went wrong', 'error');
                    console.error('Error:', error);
                }
            });
        });

        // Mobile menu functionality from Male_Fashion
        $('.canvas__open').on('click', function() {
            $('.header__menu ul').slideToggle();
        });
    });
    </script>
</body>

</html>