<?php
session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$query = "SELECT orders.*, users.Username as user_name 
         FROM orders 
         JOIN users ON orders.user_id = users.Id 
         ORDER BY orders.created_at DESC";
$result = mysqli_query($con, $query);
if (!$result) {
    die("Error executing query: " . mysqli_error($con));
}

$hasResults = mysqli_num_rows($result) > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    $tracking_number = $_POST['tracking_number'] ?? null;

    $update_query = "UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("ssi", $new_status, $tracking_number, $order_id);
    if ($stmt->execute()) {
        header("Location: orders.php?success=Status updated successfully");
        exit();
    } else {
        $error = "Error updating status: " . $con->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders Management</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="navigation.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 90%; max-width: 600px; border-radius: 8px; }
        .toast { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .table-container { max-height: 70vh; overflow-y: auto; }
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
                    <h1 class="text-3xl font-bold text-gray-800">Orders Management</h1>
                    <div class="relative">
                        <input type="text" id="search-orders" placeholder="Search orders..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="toast bg-green-500 text-white p-4 rounded-lg shadow-lg mb-4"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="toast bg-red-500 text-white p-4 rounded-lg shadow-lg mb-4"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($hasResults): ?>
                    <div class="bg-white rounded-lg shadow-lg table-container">
                        <table class="w-full">
                            <thead class="bg-gray-200 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left">Order #</th>
                                    <th class="p-4 text-left">User</th>
                                    <th class="p-4 text-left">Total</th>
                                    <th class="p-4 text-left">Payment</th>
                                    <th class="p-4 text-left">Status</th>
                                    <th class="p-4 text-left">Date</th>
                                    <th class="p-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="orders-table">
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['order_number']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <td class="p-4">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['payment_method'] . ' (' . $row['payment_status'] . ')'); ?></td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 rounded text-white 
                                                <?php 
                                                    $statusLower = strtolower($row['status']);
                                                    switch ($statusLower) {
                                                        case 'pending':
                                                            echo 'bg-yellow-500';
                                                            break;                                            
                                                        case 'processing':
                                                            echo 'bg-orange-500';
                                                            break;
                                                        case 'shipped':
                                                            echo 'bg-blue-500';
                                                            break;
                                                        case 'delivered':
                                                            echo 'bg-teal-500';
                                                            break;
                                                        case 'cancelled':
                                                            echo 'bg-red-500';
                                                            break;
                                                        case 'refunded':
                                                            echo 'bg-gray-500';
                                                            break;
                                                        case 'replacement requested':
                                                            echo 'bg-purple-500';
                                                            break;
                                                        case 'return requested':
                                                            echo 'bg-pink-500';
                                                            break;
                                                        case 'accepted': 
                                                            echo 'bg-green-500'; 
                                                            break;
                                                        default:
                                                            echo 'bg-gray-500';
                                                    }
                                                ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td class="p-4 flex gap-2">
                                            <button onclick="openDetailsModal(<?php echo $row['id']; ?>)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['status']); ?>', '<?php echo htmlspecialchars($row['tracking_number'] ?? ''); ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center text-gray-600">
                        No orders found in the database.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="details-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Order Details</h2>
            <div id="order-details-content"></div>
            <div class="flex justify-end mt-4">
                <button onclick="closeModal('details-modal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
            </div>
        </div>
    </div>

    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Update Order Status</h2>
            <form method="POST">
                <input type="hidden" name="order_id" id="edit-order-id">
                <div class="mb-4">
                    <label class="block text-gray-700">Status</label>
                    <select name="status" id="edit-status" class="w-full p-2 border rounded">
                        <option value="Pending">Pending</option>
                        <option value="Accepted">Accepted</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Refunded">Refunded</option>
                        <option value="Replacement Requested">Replacement Requested</option>
                        <option value="Return Requested">Return Requested</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Tracking Number</label>
                    <input type="text" name="tracking_number" id="edit-tracking" class="w-full p-2 border rounded" placeholder="Enter tracking number">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('edit-modal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                    <button type="submit" name="update_status" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="main.js"></script>
    <script>
        document.getElementById('search-orders').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#orders-table tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openEditModal(orderId, currentStatus, trackingNumber) {
            document.getElementById('edit-order-id').value = orderId;
            document.getElementById('edit-status').value = currentStatus;
            document.getElementById('edit-tracking').value = trackingNumber || '';
            openModal('edit-modal');
        }

        function openDetailsModal(orderId) {
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('order-details-content');
                    content.innerHTML = `
                        <p><strong>Order Number:</strong> ${data.order_number}</p>
                        <p><strong>User:</strong> ${data.user_name}</p>
                        <p><strong>Total Amount:</strong> ₹${parseFloat(data.total_amount).toFixed(2)}</p>
                        <p><strong>Virtual Money Used:</strong> ₹${parseFloat(data.virtual_money_used).toFixed(2)}</p>
                        <p><strong>Coupon:</strong> ${data.coupon_id ? 'ID: ' + data.coupon_id : 'None'}</p>
                        <p><strong>Shipping Rate:</strong> ${data.shipping_rate_id ? 'ID: ' + data.shipping_rate_id : 'None'}</p>
                        <p><strong>Payment Method:</strong> ${data.payment_method} (${data.payment_status})</p>
                        <p><strong>Transaction ID:</strong> ${data.transaction_id || 'N/A'}</p>
                        <p><strong>Shipping Address:</strong> ${data.shipping_address}</p>
                        <p><strong>Billing Address:</strong> ${data.billing_address}</p>
                        <p><strong>Tracking Number:</strong> ${data.tracking_number || 'N/A'}</p>
                        <h3 class="mt-4 font-semibold">Order Items:</h3>
                        <ul class="list-disc pl-5">
                            ${data.items.map(item => {
                                const color = item.color ? item.color : 'N/A';
                                const size = item.size ? item.size : 'N/A';
                                return `<li>${item.quantity}x ${item.product_name} - ${color}, ${size} - ₹${parseFloat(item.price).toFixed(2)} (Subtotal: ₹${parseFloat(item.subtotal).toFixed(2)})</li>`;
                            }).join('')}
                        </ul>
                    `;
                    openModal('details-modal');
                })
                .catch(err => alert('Error loading details: ' + err));
        }
    </script>
        <?php include 'loading.php'; ?>
</body>
</html>