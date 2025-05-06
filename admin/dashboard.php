<?php
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

// Check if admin session is valid
if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verify database connection
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle report generation from modal
if (isset($_POST['generate'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $report_options = isset($_POST['report_options']) ? $_POST['report_options'] : [];

    // Initialize CSV content
    $csv_content = [];
    $csv_content[] = ["Generated Report - " . date('Y-m-d H:i:s')];
    $csv_content[] = ["Date Range", "$start_date to $end_date"];
    $csv_content[] = []; // Empty row for spacing

    // Fetch data based on selected options
    if (in_array('users', $report_options) || in_array('all', $report_options)) {
        $csv_content[] = ["Users"];
        $csv_content[] = ["Username", "Email", "Created At"];
        $result = mysqli_query($con, "SELECT Username, Email, created_at FROM users WHERE Role='user' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
        while ($row = mysqli_fetch_assoc($result)) {
            $csv_content[] = [$row['Username'], $row['Email'], $row['created_at']];
        }
        $csv_content[] = []; // Empty row for spacing
    }

    if (in_array('orders', $report_options) || in_array('all', $report_options)) {
        $csv_content[] = ["Orders"];
        $csv_content[] = ["Order Number", "Total Amount", "Status", "Created At"];
        $result = mysqli_query($con, "SELECT order_number, total_amount, status, created_at FROM orders WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
        while ($row = mysqli_fetch_assoc($result)) {
            $csv_content[] = [$row['order_number'], $row['total_amount'], $row['status'], $row['created_at']];
        }
        $csv_content[] = []; // Empty row for spacing
    }

    if (in_array('products', $report_options) || in_array('all', $report_options)) {
        $csv_content[] = ["Products"];
        $csv_content[] = ["Name", "Price", "Category", "Created At"];
        $result = mysqli_query($con, "SELECT name, price, category_name, created_at FROM products WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
        while ($row = mysqli_fetch_assoc($result)) {
            $csv_content[] = [$row['name'], $row['price'], $row['category_name'], $row['created_at']];
        }
        $csv_content[] = []; // Empty row for spacing
    }

    // if (in_array('coupons', $report_options) || in_array('all', $report_options)) {
    //     $csv_content[] = ["Coupons"];
    //     $csv_content[] = ["Code", "Discount Type", "Discount Value", "Expires At"];
    //     $result = mysqli_query($con, "SELECT code, discount_type, discount_value, expires_at FROM coupons WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
    //     while ($row = mysqli_fetch_assoc($result)) {
    //         $discount = $row['discount_type'] === 'percentage' ? ($row['discount_value'] * 100) . '%' : '₹' . $row['discount_value'];
    //         $csv_content[] = [$row['code'], $row['discount_type'], $discount, $row['expires_at'] ?: 'N/A'];
    //     }
    // }

    // Generate CSV file
    $filename = "report_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    foreach ($csv_content as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Fetch counts for dashboard cards
$userCount = mysqli_num_rows(mysqli_query($con, "SELECT * FROM users WHERE Role='user'"));
$productCount = mysqli_num_rows(mysqli_query($con, "SELECT * FROM products"));
$orderCount = mysqli_num_rows(mysqli_query($con, "SELECT * FROM orders"));

// Calculate Total Revenue (sum of total_amount for completed orders)
$totalRevenue = 0;
$result = mysqli_query($con, "SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status='Completed'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalRevenue = $row['revenue'] ?? 0;
}

// Product distribution by category
$categoryCounts = [];
$result = mysqli_query($con, "SELECT category_name, COUNT(*) as count FROM products GROUP BY category_name");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categoryCounts[$row['category_name']] = $row['count'];
    }
}
$categoryLabels = array_keys($categoryCounts);
$categoryData = array_values($categoryCounts);

// Order status distribution
$orderStatusCounts = [];
$result = mysqli_query($con, "SELECT status, COUNT(*) as count FROM orders GROUP BY status");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orderStatusCounts[$row['status']] = $row['count'];
    }
}
$orderStatusLabels = array_keys($orderStatusCounts);
$orderStatusData = array_values($orderStatusCounts);

// Fetch recent activities from users, products, and orders tables
$recentActivities = [];

// 1. New User Registered (from users table)
$query = "SELECT Username, created_at FROM users WHERE Role = 'user' ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = [
            'description' => "New User Registered: " . htmlspecialchars($row['Username']),
            'timestamp' => $row['created_at']
        ];
    }
} else {
    echo "Error fetching users: " . mysqli_error($con) . "<br>";
}

// 2. Product Added (from products table)
$query = "SELECT name, created_at FROM products ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = [
            'description' => "Product Added: " . htmlspecialchars($row['name']),
            'timestamp' => $row['created_at']
        ];
    }
} else {
    echo "Error fetching products: " . mysqli_error($con) . "<br>";
}

// 3. Order Placed (from orders table)
$query = "SELECT order_number, created_at FROM orders ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = [
            'description' => "Order Placed: " . htmlspecialchars($row['order_number']),
            'timestamp' => $row['created_at']
        ];
    }
} else {
    echo "Error fetching orders: " . mysqli_error($con) . "<br>";
}

// Sort by timestamp (descending) and limit to 5
if (!empty($recentActivities)) {
    usort($recentActivities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    $recentActivities = array_slice($recentActivities, 0, 5);
}

// Optional debug output (uncomment to see raw data)
// echo "<pre>";
// print_r($recentActivities);
// echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="navigation.css">
    <style>
        .card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .toast { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .chart-container { position: relative; height: 300px; width: 100%; margin-top: 20px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; }
        .modal-content { 
            background: #fff; 
            margin: 10% auto; 
            padding: 24px; 
            width: 90%; 
            max-width: 480px; 
            border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2); 
            border-top: 4px solid #ef4444;
        }
        .modal h2 { font-size: 1.75rem; font-weight: 700; color: #1f2937; }
        .modal label { color: #4b5563; font-weight: 500; }
        .modal input[type="date"] { 
            border: 1px solid #d1d5db; 
            padding: 8px; 
            border-radius: 6px; 
            width: 100%; 
            outline: none; 
            transition: border-color 0.2s; 
        }
        .modal input[type="date"]:focus { border-color: #3b82f6; }
        .modal input[type="checkbox"] { 
            accent-color: #ef4444; 
            width: 16px; 
            height: 16px; 
        }
        .modal button { 
            padding: 8px 16px; 
            border-radius: 6px; 
            font-weight: 500; 
            transition: background-color 0.2s; 
        }
        .modal button[type="submit"] { background: #3b82f6; color: #fff; }
        .modal button[type="submit"]:hover { background: #2563eb; }
        .modal button[type="button"] { background: #6b7280; color: #fff; }
        .modal button[type="button"]:hover { background: #4b5563; }
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
                <h1 class="text-4xl font-extrabold text-gray-900 mb-8">Dashboard Overview</h1>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="card bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
                        <h2 class="text-lg font-semibold text-gray-600">Total Users</h2>
                        <p class="text-5xl font-bold text-blue-600 mt-3"><?php echo $userCount; ?></p>
                        <p class="text-sm text-gray-500 mt-2">Registered users in the system</p>
                    </div>
                    <div class="card bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <h2 class="text-lg font-semibold text-gray-600">Total Products</h2>
                        <p class="text-5xl font-bold text-green-600 mt-3"><?php echo $productCount; ?></p>
                        <p class="text-sm text-gray-500 mt-2">Available products in inventory</p>
                    </div>
                    <div class="card bg-white p-6 rounded-xl shadow-md border-l-4 border-orange-500">
                        <h2 class="text-lg font-semibold text-gray-600">Total Orders</h2>
                        <p class="text-5xl font-bold text-orange-600 mt-3"><?php echo $orderCount; ?></p>
                        <p class="text-sm text-gray-500 mt-2">Orders placed by users</p>
                    </div>
                    <div class="card bg-white p-6 rounded-xl shadow-md border-l-4 border-red-500">
                        <h2 class="text-lg font-semibold text-gray-600">Total Revenue</h2>
                        <p class="text-5xl font-bold text-red-600 mt-3">₹<?php echo number_format($totalRevenue, 2); ?></p>
                        <p class="text-sm text-gray-500 mt-2">Revenue from completed orders</p>
                    </div>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Product Distribution by Category</h2>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Order Status Distribution</h2>
                    <div class="chart-container">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activities</h2>
                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <ul class="space-y-4">
                            <?php if (empty($recentActivities)): ?>
                                <li class="text-gray-700">No recent activities found.</li>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <li class="flex items-center justify-between">
                                        <span class="text-gray-700"><?php echo htmlspecialchars($activity['description']); ?></span>
                                        <span class="text-sm text-gray-500"><?php echo date('Y-m-d H:i A', strtotime($activity['timestamp'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="products.php" class="bg-blue-100 p-4 rounded-xl shadow-md text-center hover:bg-blue-200 transition-colors">
                            <i class="fas fa-box text-3xl text-blue-600"></i>
                            <p class="text-lg font-medium text-gray-800 mt-2">Manage Products</p>
                        </a>
                        <a href="orders.php" class="bg-green-100 p-4 rounded-xl shadow-md text-center hover:bg-green-200 transition-colors">
                            <i class="fas fa-shopping-cart text-3xl text-green-600"></i>
                            <p class="text-lg font-medium text-gray-800 mt-2">Manage Orders</p>
                        </a>
                        <a href="users.php" class="bg-purple-100 p-4 rounded-xl shadow-md text-center hover:bg-purple-200 transition-colors">
                            <i class="fas fa-users text-3xl text-purple-600"></i>
                            <p class="text-lg font-medium text-gray-800 mt-2">Manage Users</p>
                        </a>
                        <button id="openModal" class="bg-red-100 p-4 rounded-xl shadow-md text-center hover:bg-red-200 transition-colors">
                            <i class="fas fa-file-export text-3xl text-red-600"></i>
                            <p class="text-lg font-medium text-gray-800 mt-2">Generate Report</p>
                        </button>
                    </div>
                </div>

                <!-- Modal for Report Options -->
                <div id="reportModal" class="modal">
                    <div class="modal-content">
                        <h2 class="mb-6">Generate Report</h2>
                        <form method="POST" id="reportForm">
                            <div class="mb-4">
                                <label class="block mb-1">Start Date</label>
                                <input type="date" name="start_date" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" required>
                            </div>
                            <div class="mb-6">
                                <label class="block mb-1">End Date</label>
                                <input type="date" name="end_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-6">
                                <label class="block mb-2">Select Report Content</label>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="report_options[]" value="users" class="mr-2"> Users
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="report_options[]" value="orders" class="mr-2"> Orders
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="report_options[]" value="products" class="mr-2"> Products
                                    </label>
                                    <!-- <label class="flex items-center">
                                        <input type="checkbox" name="report_options[]" value="coupons" class="mr-2"> Coupons
                                    </label> -->
                                    <label class="flex items-center">
                                        <input type="checkbox" name="report_options[]" value="all" id="selectAll" class="mr-2"> All of the Above
                                    </label>
                                </div>
                            </div>
                            <div class="flex justify-end gap-4">
                                <button type="button" id="closeModal">Cancel</button>
                                <button type="submit" name="generate">Generate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="main.js"></script>
    <script>
        const categoryChart = new Chart(document.getElementById('categoryChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categoryLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($categoryData); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Product Distribution by Category' }
                }
            }
        });

        const orderStatusChart = new Chart(document.getElementById('orderStatusChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($orderStatusLabels); ?>,
                datasets: [{
                    label: 'Order Count',
                    data: <?php echo json_encode($orderStatusData); ?>,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Order Status Distribution' }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Count' } }
                }
            }
        });

        // Modal JavaScript
        const modal = document.getElementById('reportModal');
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('input[name="report_options[]"]:not(#selectAll)');

        openModalBtn.addEventListener('click', () => {
            modal.style.display = 'block';
        });

        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        selectAllCheckbox.addEventListener('change', () => {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                if (!checkbox.checked && selectAllCheckbox.checked) {
                    selectAllCheckbox.checked = false;
                }
                if (Array.from(checkboxes).every(cb => cb.checked)) {
                    selectAllCheckbox.checked = true;
                }
            });
        });
    </script>
    <?php include 'loading.php'; ?>
</body>
</html>