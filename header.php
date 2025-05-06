<?php
include 'php/config.php'; // Ensure this file contains your database connection

// Initialize cart count
$cart_count = 0;

// Check if user is logged in
if (isset($_SESSION['valid']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Query to count active cart items for the user
    $sql_cart_count = "SELECT COUNT(*) as item_count 
                       FROM cart 
                       WHERE user_id = ? AND status = 'active'";
    $stmt_cart_count = $con->prepare($sql_cart_count);
    $stmt_cart_count->bind_param("i", $user_id);
    $stmt_cart_count->execute();
    $result_cart_count = $stmt_cart_count->get_result();
    $cart_count = $result_cart_count->fetch_assoc()['item_count'];
    $stmt_cart_count->close();
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    
    <link rel="stylesheet" href="style.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .offcanvas__menu {
            display: block !important;
        }
        .offcanvas-menu-wrapper.active .offcanvas__menu {
            opacity: 1;
            visibility: visible;
        }
        .offcanvas__menu ul {
            list-style: none;
            padding: 0;
        }
        .offcanvas__menu ul li {
            margin-bottom: 15px;
        }
        .offcanvas__menu ul li a {
            color: #111111;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: block;
        }
        .offcanvas__menu ul li a:hover {
            color: #e53637;
        }
        .sound-btn {
            border: none;
            background: none;
            cursor: pointer;
            margin-right: 10px;
            position: relative;
            width: 18px;
            height: 16px;
        }
        .sound-btn i {
            font-size: 16px;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header__top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-7">
                        <div class="header__top__left">
                            <p>30-day return or refund guarantee.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-5">
                        <div class="header__top__right">
                            <div class="header__top__links">
                                <?php if (isset($_SESSION['valid'])): ?>
                                    <a href="php/logout.php?role=user" class="logout-link">Sign out</a>
                                <?php else: ?>
                                    <a href="login.php">Sign in</a>
                                <?php endif; ?>
                                <a href="FAQ.php">FAQs</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3">
                    <div class="header__logo">
                        <a href="./index.php"><img src="img/logo.png" alt="Male Fashion"></a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <nav class="header__menu mobile-menu">
                        <ul>
                            <li><a href="./index.php">Home</a></li>
                            <li><a href="./shop.php">Shop</a></li>
                            <li><a href="./aboutus.php">About</a></li>
                            <li><a href="./account.php">Account</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="header__nav__option">
                        <audio id="backgroundMusic" src="assets/bg music/shopping-239023.mp3" preload="auto" loop></audio>
                        <a href="./wishlist.php"><img src="img/icon/heart.png" alt=""></a>
                        <a href="./cart.php"><img src="img/icon/cart.png" alt=""> <span><?php echo $cart_count; ?></span></a>
                        <a href="#" class="sound-btn"><i class="fas fa-pause"></i></a>
                    </div>
                </div>
            </div>
            <div class="canvas__open"><i class="fa fa-bars"></i></div>
            <div class="offcanvas-menu-wrapper">
                <div class="canvas__close">
                    <i class="fa fa-times"></i>
                </div>
                <nav class="offcanvas__menu">
                    <ul>
                        <li><a href="./index.php">Home</a></li>
                        <li><a href="./shop.php">Shop</a></li>
                        <li><a href="./cart.php">Cart</a></li>
                        <li><a href="./wishlist.php">Wishlist</a></li>
                        <li><a href="./aboutus.php">About</a></li>
                        <li><a href="./contact us.php">Contact</a></li>
                        <li><a href="./account.php">Account</a></li>
                    </ul>
                </nav>
            </div>
            <div class="offcanvas-menu-overlay"></div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvasOpen = document.querySelector('.canvas__open');
            const canvasClose = document.querySelector('.canvas__close');
            const offcanvasMenu = document.querySelector('.offcanvas-menu-wrapper');
            const overlay = document.querySelector('.offcanvas-menu-overlay');
            const soundBtn = document.querySelector('.sound-btn');
            const backgroundMusic = document.getElementById('backgroundMusic');
            const logoutLink = document.querySelector('.logout-link');
            let isPlaying = localStorage.getItem('isPlaying') === 'true';

            // Set low volume
            backgroundMusic.volume = 0.4; // 20% volume

            // Restore playback state and position
            const savedTime = parseFloat(localStorage.getItem('musicCurrentTime') || 0);
            backgroundMusic.currentTime = savedTime;

            // Update UI based on saved state
            soundBtn.innerHTML = isPlaying ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>';

            // Attempt to play music if it was playing
            if (isPlaying) {
                const playPromise = backgroundMusic.play();
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        console.log("Autoplay prevented: ", error);
                        isPlaying = false;
                        soundBtn.innerHTML = '<i class="fas fa-play"></i>';
                        localStorage.setItem('isPlaying', false);
                    });
                }
            }

            // Toggle play/pause
            soundBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (isPlaying) {
                    backgroundMusic.pause();
                    soundBtn.innerHTML = '<i class="fas fa-play"></i>';
                    isPlaying = false;
                } else {
                    const playPromise = backgroundMusic.play();
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            soundBtn.innerHTML = '<i class="fas fa-pause"></i>';
                            isPlaying = true;
                        }).catch(error => {
                            console.log("Playback failed: ", error);
                        });
                    }
                }
                localStorage.setItem('isPlaying', isPlaying);
                localStorage.setItem('musicCurrentTime', backgroundMusic.currentTime);
            });

            // Periodically save current time
            backgroundMusic.addEventListener('timeupdate', function() {
                if (isPlaying) {
                    localStorage.setItem('musicCurrentTime', backgroundMusic.currentTime);
                }
            });

            // Save state before navigation
            window.addEventListener('beforeunload', function() {
                localStorage.setItem('musicCurrentTime', backgroundMusic.currentTime);
                localStorage.setItem('isPlaying', isPlaying);
            });

            // Add confirmation for logout
            logoutLink.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to sign out?')) {
                    e.preventDefault();
                }
            });

            // Navigation menu handlers
            canvasOpen.addEventListener('click', function() {
                offcanvasMenu.classList.add('active');
                overlay.classList.add('active');
            });

            canvasClose.addEventListener('click', function() {
                offcanvasMenu.classList.remove('active');
                overlay.classList.remove('active');
            });

            overlay.addEventListener('click', function() {
                offcanvasMenu.classList.remove('active');
                overlay.classList.remove('active');
            });
        });
    </script>
</body>
</html>