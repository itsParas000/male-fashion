<?php
session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$result = mysqli_query($con, "SELECT * FROM users where role='user' ORDER BY created_at DESC");
if (!$result) {
    die("Error executing query: " . mysqli_error($con));
}

$hasResults = mysqli_num_rows($result) > 0;

if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $stmt = $con->prepare("UPDATE users SET is_active = NOT is_active WHERE Id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: users.php?success=User status updated successfully");
        exit();
    } else {
        $error = "Error updating status: " . $con->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Users Management</title>
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
                    <h1 class="text-3xl font-bold text-gray-800">Users Management</h1>
                    <div class="relative">
                        <input type="text" id="search-users" placeholder="Search users..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                    <th class="p-4 text-left">ID</th>
                                    <th class="p-4 text-left">Username</th>
                                    <th class="p-4 text-left">Email</th>
                                    <th class="p-4 text-left">Role</th>
                                    <th class="p-4 text-left">Virtual Money</th>
                                    <th class="p-4 text-left">Profile Picture</th>
                                    <th class="p-4 text-left">Created At</th>
                                    <th class="p-4 text-left">Status</th>
                                    <th class="p-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table">
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['Id']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['Username'] ?? 'N/A'); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['Email'] ?? 'N/A'); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['Role']); ?></td>
                                        <td class="p-4">₹<?php echo number_format($row['virtual_money'], 2); ?></td>
                                        <td class="p-4">
                                            <?php if ($row['profile_picture']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile" class="w-12 h-12 object-cover rounded-full">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle text-gray-500 text-2xl"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 rounded text-white <?php echo $row['is_active'] ? 'bg-green-500' : 'bg-red-500'; ?>">
                                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <button onclick="toggleStatus(<?php echo $row['Id']; ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                                <i class="fas fa-toggle-<?php echo $row['is_active'] ? 'on' : 'off'; ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center text-gray-600">
                        No users found in the database.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="main.js"></script>
    <script>
        document.getElementById('search-users').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#users-table tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        function toggleStatus(userId) {
            if (confirm('Are you sure you want to toggle this user\'s status?')) {
                window.location.href = `users.php?toggle_status=${userId}`;
            }
        }
    </script>
        <?php include 'loading.php'; ?>
</body>
</html>