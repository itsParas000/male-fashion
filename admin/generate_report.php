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

// Handle report generation
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
            $csv_content[] = [$row['order_number'], "INR " . number_format($row['total_amount'], 2, '.', ''), $row['status'], $row['created_at']];
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

    if (in_array('coupons', $report_options) || in_array('all', $report_options)) {
        $csv_content[] = ["Coupons"];
        $csv_content[] = ["Code", "Discount Type", "Discount Value", "Expires At"];
        $result = mysqli_query($con, "SELECT code, discount_type, discount_value, expires_at FROM coupons WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
        while ($row = mysqli_fetch_assoc($result)) {
            $discount = $row['discount_type'] === 'percentage' ? ($row['discount_value'] * 100) . '%' : 'INR ' . number_format($row['discount_value'], 2, '.', '');
            $csv_content[] = [$row['code'], $row['discount_type'], $discount, $row['expires_at'] ?: 'N/A'];
        }
    }

    // Generate CSV file
    $filename = "report_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    // Add UTF-8 BOM (optional, since we're using "INR " now)
    fwrite($output, "\xEF\xBB\xBF");
    foreach ($csv_content as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Default dashboard data (unchanged from original)
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

// Recent activities
$recentActivities = [];
$query = "SELECT Username, created_at FROM users WHERE Role = 'user' ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = ['description' => "New User Registered: " . htmlspecialchars($row['Username']), 'timestamp' => $row['created_at']];
    }
}
$query = "SELECT name, created_at FROM products ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = ['description' => "Product Added: " . htmlspecialchars($row['name']), 'timestamp' => $row['created_at']];
    }
}
$query = "SELECT order_number, created_at FROM orders ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentActivities[] = ['description' => "Order Placed: " . htmlspecialchars($row['order_number']), 'timestamp' => $row['created_at']];
    }
}
if (!empty($recentActivities)) {
    usort($recentActivities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    $recentActivities = array_slice($recentActivities, 0, 5);
}
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
</body>
</html>