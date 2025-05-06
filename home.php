<?php 
   session_start();

   include("php/config.php");
   if(!isset($_SESSION['valid'])){
    header("Location: login.php");
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Home</title>
    <style>
        /* Style for the modal */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            z-index: 1000;
            padding: 20px;
            text-align: center;
        }

        .modal-header {
            font-weight: bold;
            margin-bottom: 15px;
        }

        .modal a {
            display: block;
            margin: 10px 0;
            text-decoration: none;
            color: #333;
        }

        .modal a:hover {
            color: #007bff;
        }

        .modal .close-btn {
            background-color: #ff5e5e;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .welcome-section {
            display: none;
        }

        .main-banner {
            text-align: center;
            margin-top: 20px;
        }

        .main-banner img {
            width: 100%;
            max-width: 1200px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p><a href="home.php">Logo</a></p>
        </div>

        <div class="right-links">
            <?php 
                $id = $_SESSION['id'];
                $query = mysqli_query($con, "SELECT * FROM users WHERE Id=$id");

                while($result = mysqli_fetch_assoc($query)){
                    $res_id = $result['Id'];
                }
            ?>
            <!-- Trigger Button -->
            <button class="btn" onclick="openModal()">Menu</button>
        </div>
    </div>

    <main>
        <div class="main-banner">
            <img src="assets/banner.jpg" alt="Shop Banner">
        </div>
    </main>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="closeModal()"></div>

    <!-- Modal -->
    <div class="modal" id="modal">
        <div class="modal-header">Menu</div>
        <a href="edit.php?Id=<?php echo $res_id; ?>">Change Profile</a>
        <!-- Logout Confirmation -->
        <a href="javascript:void(0);" onclick="confirmLogout()">Log Out</a>
        <button class="close-btn" onclick="closeModal()">Close</button>
    </div>

    <script>
        // Open modal
        function openModal() {
            document.getElementById('modal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // Confirm Logout
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "php/logout.php";
            }
        }
    </script>
    
</body>
</html>
