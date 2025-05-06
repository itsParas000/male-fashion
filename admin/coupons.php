<?php
session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['add_coupon'])) {
    $code = mysqli_real_escape_string($con, $_POST['code']);
    $discount_value = floatval($_POST['discount_value']);
    $discount_type = $_POST['discount_type'];
    $is_active = intval($_POST['is_active']);
    $expires_at = $_POST['expires_at'] ? "'".mysqli_real_escape_string($con, $_POST['expires_at'])."'" : "NULL";
    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : "NULL";

    $query = "INSERT INTO coupons (code, discount_type, discount_value, is_active, expires_at, user_id, used) 
              VALUES ('$code', '$discount_type', '$discount_value', '$is_active', $expires_at, $user_id, 0)";
    if (mysqli_query($con, $query)) {
        $success = "Coupon added successfully!";
    } else {
        $error = "Error adding coupon: " . mysqli_error($con);
    }
}

if (isset($_POST['update_coupon'])) {
    $id = intval($_POST['id']);
    $code = mysqli_real_escape_string($con, $_POST['code']);
    $discount_value = floatval($_POST['discount_value']);
    $discount_type = $_POST['discount_type'];
    $is_active = intval($_POST['is_active']);
    $expires_at = $_POST['expires_at'] ? "'".mysqli_real_escape_string($con, $_POST['expires_at'])."'" : "NULL";
    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : "NULL";

    $query = "UPDATE coupons SET code='$code', discount_value='$discount_value', discount_type='$discount_type', 
              is_active='$is_active', expires_at=$expires_at, user_id=$user_id WHERE id=$id";
    if (mysqli_query($con, $query)) {
        $success = "Coupon updated successfully!";
    } else {
        $error = "Error updating coupon: " . mysqli_error($con);
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM coupons WHERE id=$id";
    if (mysqli_query($con, $query)) {
        $success = "Coupon deleted successfully!";
    } else {
        $error = "Error deleting coupon: " . mysqli_error($con);
    }
}

$coupons = mysqli_query($con, "SELECT c.*, u.Username FROM coupons c LEFT JOIN users u ON c.user_id = u.Id");
$users = mysqli_query($con, "SELECT Id, Username FROM users WHERE Role = 'user'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Coupons Management</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="navigation.css">
    <style>
        .table-row:hover { background-color: #f1f5f9; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; }
        .modal.active { display: flex; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
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
                    <h1 class="text-4xl font-extrabold text-gray-900">Coupons Management</h1>
                    <button id="add-coupon-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i> Add Coupon
                    </button>
                </div>

                <?php if (isset($success)): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6"><?php echo $success; ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="bg-white shadow-md rounded-xl overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Code</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Discount Value</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Discount Type</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">User</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Active</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Used</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Expires At</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($coupons)): ?>
                                <tr class="table-row border-b">
                                    <td class="px-6 py-4 text-gray-800"><?php echo htmlspecialchars($row['code']); ?></td>
                                    <td class="px-6 py-4 text-gray-800"><?php echo $row['discount_value']; ?></td>
                                    <td class="px-6 py-4 text-gray-800"><?php echo ucfirst($row['discount_type']); ?></td>
                                    <td class="px-6 py-4 text-gray-800"><?php echo $row['Username'] ?? 'General'; ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $row['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                            <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $row['used'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo $row['used'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-800"><?php echo $row['expires_at'] ?: 'No Expiry'; ?></td>
                                    <td class="px-6 py-4">
                                        <button class="edit-coupon text-blue-600 hover:text-blue-800 mr-4" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-code="<?php echo $row['code']; ?>" 
                                                data-discount="<?php echo $row['discount_value']; ?>" 
                                                data-type="<?php echo $row['discount_type']; ?>" 
                                                data-active="<?php echo $row['is_active']; ?>" 
                                                data-expiry="<?php echo $row['expires_at']; ?>" 
                                                data-user="<?php echo $row['user_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this coupon?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="coupon-modal" class="modal">
        <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4">Add Coupon</h2>
            <form id="coupon-form" method="POST">
                <input type="hidden" name="id" id="coupon-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Coupon Code</label>
                    <input type="text" name="code" id="coupon-code" class="mt-1 w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" id="coupon-discount" class="mt-1 w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Discount Type</label>
                    <select name="discount_type" id="coupon-type" class="mt-1 w-full p-2 border rounded-lg">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed</option>
                        <option value="free_shipping">Free Shipping</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Assigned User (Optional)</label>
                    <select name="user_id" id="coupon-user" class="mt-1 w-full p-2 border rounded-lg">
                        <option value="">General (No Specific User)</option>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <option value="<?php echo $user['Id']; ?>"><?php echo htmlspecialchars($user['Username']); ?></option>
                        <?php endwhile; mysqli_data_seek($users, 0); ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Active</label>
                    <select name="is_active" id="coupon-active" class="mt-1 w-full p-2 border rounded-lg">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Expires At (Optional)</label>
                    <input type="datetime-local" name="expires_at" id="coupon-expiry" class="mt-1 w-full p-2 border rounded-lg">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="close-modal" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
                    <button type="submit" name="add_coupon" id="submit-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Coupon</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="main.js"></script>
    <script>
        const modal = document.getElementById('coupon-modal');
        const addBtn = document.getElementById('add-coupon-btn');
        const closeBtn = document.getElementById('close-modal');
        const form = document.getElementById('coupon-form');
        const submitBtn = document.getElementById('submit-btn');
        const modalTitle = document.getElementById('modal-title');

        addBtn.addEventListener('click', () => {
            modal.classList.add('active');
            form.reset();
            modalTitle.textContent = 'Add Coupon';
            submitBtn.name = 'add_coupon';
            submitBtn.textContent = 'Add Coupon';
            document.getElementById('coupon-id').value = '';
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        document.querySelectorAll('.edit-coupon').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.classList.add('active');
                modalTitle.textContent = 'Edit Coupon';
                submitBtn.name = 'update_coupon';
                submitBtn.textContent = 'Update Coupon';

                document.getElementById('coupon-id').value = btn.dataset.id;
                document.getElementById('coupon-code').value = btn.dataset.code;
                document.getElementById('coupon-discount').value = btn.dataset.discount;
                document.getElementById('coupon-type').value = btn.dataset.type;
                document.getElementById('coupon-active').value = btn.dataset.active;
                document.getElementById('coupon-expiry').value = btn.dataset.expiry ? btn.dataset.expiry.replace(' ', 'T') : '';
                document.getElementById('coupon-user').value = btn.dataset.user || '';
            });
        });
    </script>
        <?php include 'loading.php'; ?>
</body>
</html>