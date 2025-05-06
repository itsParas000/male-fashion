<?php
session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$check_product_column = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'is_active'");
if (mysqli_num_rows($check_product_column) == 0) {
    mysqli_query($con, "ALTER TABLE products ADD COLUMN is_active TINYINT(1) DEFAULT 1");
    mysqli_query($con, "UPDATE products SET is_active = 1 WHERE is_active IS NULL");
}

$check_review_column = mysqli_query($con, "SHOW COLUMNS FROM reviews LIKE 'is_active'");
if (mysqli_num_rows($check_review_column) == 0) {
    mysqli_query($con, "ALTER TABLE reviews ADD COLUMN is_active TINYINT(1) DEFAULT 1");
    mysqli_query($con, "UPDATE reviews SET is_active = 1 WHERE is_active IS NULL");
}

// Ensure color and size columns exist
$check_color_column = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'color'");
if (mysqli_num_rows($check_color_column) == 0) {
    mysqli_query($con, "ALTER TABLE products ADD COLUMN color TEXT");
}

$check_size_column = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'size'");
if (mysqli_num_rows($check_size_column) == 0) {
    mysqli_query($con, "ALTER TABLE products ADD COLUMN size TEXT");
}

$result = mysqli_query($con, "SELECT * FROM products ORDER BY created_at DESC");

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $con->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: products.php?success=Product deleted successfully');
        exit;
    } else {
        $error = "Error deleting product: " . $con->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = intval($_POST['id']);
    $stock_quantity = intval($_POST['stock_quantity']);
    if ($stock_quantity < 0) {
        $error = "Stock quantity cannot be negative.";
    } else {
        $stmt = $con->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $stock_quantity, $id);
        if ($stmt->execute()) {
            header("Location: products.php?success=Stock updated successfully");
            exit;
        } else {
            $error = "Error updating stock: " . $con->error;
        }
    }
}

if (isset($_GET['toggle_visibility'])) {
    $id = intval($_GET['toggle_visibility']);
    $stmt = $con->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt = $con->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_status = $result->fetch_assoc()['is_active'];
        $message = $current_status ? "Product shown (is_active = 1) successfully" : "Product hidden (is_active = 0) successfully";
        header("Location: products.php?success=$message");
        exit();
    } else {
        $error = "Error updating product visibility: " . $con->error;
    }
}

if (isset($_GET['toggle_review_visibility'])) {
    $review_id = intval($_GET['toggle_review_visibility']);
    $stmt = $con->prepare("UPDATE reviews SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    if ($stmt->execute()) {
        $stmt = $con->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_status = $result->fetch_assoc()['is_active'];
        $message = $current_status ? "Review shown (is_active = 1) successfully" : "Review hidden (is_active = 0) successfully";
        header("Location: products.php?success=$message");
        exit;
    } else {
        $error = "Error updating review visibility: " . $con->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Products Management</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="navigation.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 67%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 80vh; overflow-y: auto; }
        .toast { position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 10px 20px; background: #48BB78; color: white; border-radius: 4px; }
        .table-container { max-height: 70vh; overflow-y: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #4A5568; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #E2E8F0; border-radius: 4px; }
        input:focus, textarea:focus { outline: none; border-color: #48BB78; box-shadow: 0 0 0 2px rgba(72, 187, 120, 0.2); }
        .file-input { padding: 8px; border: 1px solid #E2E8F0; border-radius: 4px; background: #F7FAFC; color: #4A5568; }
        .file-input:hover { background: #EDF2F7; }
        button { padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; }
        .btn-primary { background: #48BB78; color: white; }
        .btn-primary:hover { background: #38A169; }
        .btn-secondary { background: #A0AEC0; color: white; }
        .btn-secondary:hover { background: #718096; }
        .btn-visibility { background: #ECC94B; color: white; }
        .btn-visibility:hover { background: #D69E2E; }
        .btn-review-toggle { background: #48BB78; color: white; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0; }
        .btn-review-toggle:hover { background: #38A169; }
        .current-image { max-width: 100%; margin-top: 10px; border-radius: 4px; }
        @keyframes slideIn { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
        .animate-fade-out { animation: fadeOut 0.5s ease-out; }
        .modal-content::-webkit-scrollbar { width: 8px; }
        .modal-content::-webkit-scrollbar-track { background: #F7FAFC; border-radius: 4px; }
        .modal-content::-webkit-scrollbar-thumb { background: #A0AEC0; border-radius: 4px; border: 2px solid #F7FAFC; }
        .modal-content::-webkit-scrollbar-thumb:hover { background: #718096; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container">
        <div class="navigation">
            <?php include 'navigation.php'; ?>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>
            <div class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Products Management</h1>
                    <div class="flex gap-4">
                        <div class="relative">
                            <input type="text" id="search-products" placeholder="Search products..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-200">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                        </div>
                        <button onclick="openAddModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            <i class="fas fa-plus mr-2"></i>Add Product
                        </button>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="toast mb-4 animate-slide-in"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="toast bg-red-500 mb-4 animate-slide-in"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-lg table-container">
                    <table class="w-full">
                        <thead class="bg-gray-200 sticky top-0">
                            <tr>
                                <th class="p-2 text-left">ID</th>
                                <th class="p-2 text-left">Name</th>
                                <th class="p-2 text-left">Category</th>
                                <th class="p-2 text-left">Price</th>
                                <th class="p-2 text-left">Old Price</th>
                                <th class="p-2 text-left">Stock</th>
                                <th class="p-2 text-left">Color</th>
                                <th class="p-2 text-left">Size</th>
                                <th class="p-2 text-left">Score & Care</th>
                                <th class="p-2 text-left">Related Products</th>
                                <th class="p-2 text-left">Image</th>
                                <th class="p-2 text-left">Created At</th>
                                <th class="p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-table">
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-2"><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                                    <td class="p-2">₹<?php echo number_format($row['price'], 2); ?></td>
                                    <td class="p-2 text-gray-500 line-through"><?php echo $row['old_price'] ? '₹' . number_format($row['old_price'], 2) : 'N/A'; ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['color'] ?? 'N/A'); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['size'] ?? 'N/A'); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars(substr($row['score_and_care_tips'] ?? '', 0, 30)) . (strlen($row['score

_and_care_tips'] ?? '') > 30 ? '...' : ''); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['related_products'] ?? 'None'); ?></td>
                                    <td class="p-2">
                                        <?php if ($row['image']): ?>
                                            <img src="../assets/<?php echo htmlspecialchars($row['image']); ?>" alt="Product" class="w-12 h-12 object-cover rounded">
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td class="p-2 flex gap-2">
                                        <button onclick="openUpdateModal(<?php echo $row['id']; ?>)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="openStockModal(<?php echo $row['id']; ?>, <?php echo $row['stock_quantity']; ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                            <i class="fas fa-warehouse"></i>
                                        </button>
                                        <button onclick="toggleVisibility(<?php echo $row['id']; ?>, <?php echo $row['is_active'] ? 1 : 0; ?>)" class="btn-visibility px-3 py-1 rounded" title="Toggle Product Visibility (is_active: <?php echo $row['is_active'] ? '1 (Visible)' : '0 (Hidden)'; ?>)">
                                            <i class="fas fa-eye<?php echo $row['is_active'] ? '' : '-slash'; ?>"></i>
                                        </button>
                                        <button onclick="openReviewsModal(<?php echo $row['id']; ?>)" class="bg-teal-500 text-white px-3 py-1 rounded hover:bg-teal-600">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        <button onclick="deleteProduct(<?php echo $row['id']; ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="add-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Add New Product</h2>
            <form method="POST" action="add_product.php" enctype="multipart/form-data" class="space-y-4">
                <div class="form-group"><label>Name</label><input type="text" name="name" required class="focus:ring-2 focus:ring-green-500 border-green-500 border-2 w-full" value=""></div>
                <div class="form-group"><label>Category Name</label><input type="text" name="category_name" required class="focus:ring-2 focus:ring-green-500 w-full" placeholder="e.g., Chairs, Lighting"></div>
                <div class="form-group"><label>Price</label><input type="number" name="price" step="0.01" required class="focus:ring-2 focus:ring-green-500 w-full"></div>
                <div class="form-group"><label>Old Price (optional)</label><input type="number" name="old_price" step="0.01" class="focus:ring-2 focus:ring-green-500 w-full"></div>
                <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock_quantity" required class="focus:ring-2 focus:ring-green-500 w-full"></div>
                <div class="form-group"><label>Colors (comma-separated)</label><input type="text" name="color" class="focus:ring-2 focus:ring-green-500 w-full" placeholder="e.g., Black,White,Red"></div>
                <div class="form-group"><label>Sizes (comma-separated)</label><input type="text" name="size" class="focus:ring-2 focus:ring-green-500 w-full" placeholder="e.g., S,M,L"></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="focus:ring-2 focus:ring-green-500 w-full" rows="4"></textarea></div>
                <div class="form-group"><label>Score and Care Tips</label><textarea name="score_and_care_tips" class="focus:ring-2 focus:ring-green-500 border-green-500 border-2 w-full" rows="4" placeholder="e.g., Care: Wipe with cloth. Score: 4.5/5"></textarea></div>
                <div class="form-group"><label>Related Products (comma-separated IDs)</label><input type="text" name="related_products" class="focus:ring-2 focus:ring-green-500 border-green-500 border-2 w-full" placeholder="e.g., 1,2,3"></div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" class="file-input mt-2 w-full">
                </div>
                <div class="flex justify-end gap-4 mt-4">
                    <button type="button" onclick="closeModal('add-modal')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <div id="update-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Update Product</h2>
            <form method="POST" action="update_product.php" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" id="update-product-id">
                <div class="form-group"><label>Name</label><input type="text" name="name" id="update-name" required class="focus:ring-2 focus:ring-green-500 border-green-500 border-2 w-full" value=""></div>
                <div class="form-group"><label>Category Name</label><input type="text" name="category_name" id="update-category-name" required class="focus:ring-2 focus:ring-green-500 w-full" placeholder="e.g., Chairs, Lighting"></div>
                <div class="form-group"><label>Price</label><input type="number" name="price" id="update-price" step="0.01" required class="focus:ring-2 focus:ring-green-500 w-full"></div>
                <div class="form-group"><label>Old Price (optional)</label><input type="number" name="old_price" id="update-old-price" step="0.01" class="focus:ring-2 focus:ring-green-500 w-full"></div>
                <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock_quantity" id="update-stock-quantity" required class="focus:ring-2 focus:ring-green-500 w-full"></div>
                <div class="form-group"><label>Colors (comma-separated)</label><input type="text" name="color" id="update-color" class="focus:ring-2 focus:ring-green-500 w-full" placeholder="e.g., Black,White,Red"></div>
                <div class="form-group"><label>Sizes (comma-separated)</label><input type="text" name="size" id="update-size" class="focus:ring-2 focus:ring-green-500 w-full" placeholder="e.g., S,M,L"></div>
                <div class="form-group"><label>Description</label><textarea name="description" id="update-description" class="focus:ring-2 focus:ring-green-500 w-full" rows="4"></textarea></div>
                <div class="form-group"><label>Score and Care Tips</label><textarea name="score_and_care_tips" id="update-score-care" class="focus:ring-2 focus:ring-green-500 border-green-500 border-2 w-full" rows="4"></textarea></div>
                <div class="form-group"><label>Related Products (comma-separated IDs)</label><input type="text" name="related_products" id="update-related-products" class="focus:ring-2 focus:ring-green-500 border-green-500 border-2 w-full" placeholder="e.g., 1,2,3"></div>
                <div class="form-group">
                    <label>Image</label>
                    <div id="current-image"></div>
                    <input type="file" name="image" class="file-input mt-2 w-full">
                </div>
                <div class="flex justify-end gap-4 mt-4">
                    <button type="button" onclick="closeModal('update-modal')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <div id="stock-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Update Stock</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="stock-product-id">
                <div class="form-group">
                    <label class="block text-gray-700">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="stock-quantity" class="focus:ring-2 focus:ring-green-500 w-full" required>
                </div>
                <div class="flex justify-end gap-4 mt-4">
                    <button type="button" onclick="closeModal('stock-modal')" class="btn-secondary">Cancel</button>
                    <button type="submit" name="update_stock" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="reviews-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Product Reviews</h2>
            <div id="reviews-content"></div>
            <div class="flex justify-end mt-4">
                <button onclick="closeModal('reviews-modal')" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="main.js"></script>
    <script>
        document.getElementById('search-products').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#products-table tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openAddModal() { openModal('add-modal'); }
        function openUpdateModal(id) {
            fetch(`get_product.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('update-product-id').value = data.id;
                    document.getElementById('update-name').value = data.name;
                    document.getElementById('update-category-name').value = data.category_name || '';
                    document.getElementById('update-price').value = data.price;
                    document.getElementById('update-old-price').value = data.old_price || '';
                    document.getElementById('update-stock-quantity').value = data.stock_quantity;
                    document.getElementById('update-color').value = data.color || '';
                    document.getElementById('update-size').value = data.size || '';
                    document.getElementById('update-description').value = data.description || '';
                    document.getElementById('update-score-care').value = data.score_and_care_tips || '';
                    document.getElementById('update-related-products').value = data.related_products || '';
                    document.getElementById('current-image').innerHTML = data.image ? `<img src="../assets/${data.image}" alt="Current image" class="w-48 h-auto object-cover rounded mt-2">` : '';
                    openModal('update-modal');
                })
                .catch(err => alert('Error loading product: ' + err));
        }
        function openStockModal(id, currentStock) {
            document.getElementById('stock-product-id').value = id;
            document.getElementById('stock-quantity').value = currentStock;
            openModal('stock-modal');
        }
        function toggleVisibility(id, currentStatus) {
            if (confirm('Are you sure you want to ' + (currentStatus ? 'hide' : 'show') + ' this product? (Set is_active to ' + (currentStatus ? '0' : '1') + ')')) {
                window.location.href = `products.php?toggle_visibility=${id}`;
            }
        }
        function openReviewsModal(productId) {
            fetch(`get_product_reviews.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('reviews-content');
                    content.innerHTML = data.length > 0 
                        ? `<ul class="list-disc pl-5">${data.map(r => `
                            <li class="flex justify-between items-center py-2">
                                <span>${r.user_name} (${r.rating}/5): ${r.comment} - ${r.created_at}</span>
                                <button onclick="toggleReviewVisibility(${r.id}, ${r.is_active ? 1 : 0})" class="btn-review-toggle ml-4" title="Toggle Review Visibility (is_active: ${r.is_active ? '1 (Visible)' : '0 (Hidden)'})">
                                    <i class="fas fa-eye${r.is_active ? '' : '-slash'}"></i>
                                </button>
                            </li>`).join('')}</ul>`
                        : '<p class="text-gray-500">No reviews found for this product.</p>';
                    openModal('reviews-modal');
                })
                .catch(err => alert('Error loading reviews: ' + err));
        }
        function toggleReviewVisibility(reviewId, currentStatus) {
            if (confirm('Are you sure you want to ' + (currentStatus ? 'hide' : 'show') + ' this review? (Set is_active to ' + (currentStatus ? '0' : '1') + ')')) {
                window.location.href = `products.php?toggle_review_visibility=${reviewId}`;
            }
        }
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = `products.php?delete=${id}`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => toast.classList.add('animate-fade-out'), 3000);
                setTimeout(() => toast.remove(), 3500);
            });
        });
    </script>
        <?php include 'loading.php'; ?>
</body>
</html>