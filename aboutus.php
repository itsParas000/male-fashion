<?php 
session_name('SESSION_USER');
session_start();
include("php/config.php");
if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Male Fashion</title>
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
        .team-member { text-align: center; margin-bottom: 30px; }
        .team-member img { width: 200px; height: 200px; border-radius: 50%; margin-bottom: 15px; }
        .team-member h4 { font-size: 20px; font-weight: 700; color: #111111; }
        .team-member p { font-size: 14px; color: #3d3d3d; }
        .workspace-img { width: 100%; max-width: 800px; border-radius: 10px; }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include("header.php"); ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>About Us</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span>About Us</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Who We Are?</h2>
                    </div>
                    <p class="text-center" style="color: #3d3d3d;">
                        We’re a team passionate about fashion, committed to offering stylish and high-quality clothing for everyone. Our goal is to create designs that inspire confidence and individuality. Every piece we craft is made with care, blending modern trends with timeless appeal.
                        <strong>Our dedication to excellence ensures you’ll find something special in every collection.</strong>
                    </p>
                    <p class="text-center" style="color: #3d3d3d; margin-top: 15px;">
                        From concept to creation, we pour our expertise into every detail, making sure our clothing stands out. We’re here to make your shopping experience enjoyable and effortless.
                    </p>
                </div>
            </div>
            <div class="row" style="margin-top: 30px;">
                <div class="col-lg-12 text-center">
                    <span style="color: #3d3d3d; font-weight: 600;">Follow Us:</span>
                    <a href="https://x.com" class="ml-2"><i class="fab fa-twitter" style="color: #111111;"></i></a>
                    <a href="https://facebook.com" class="ml-2"><i class="fab fa-facebook-f" style="color: #111111;"></i></a>
                    <a href="https://linkedin.com" class="ml-2"><i class="fab fa-linkedin-in" style="color: #111111;"></i></a>
                    <a href="https://instagram.com" class="ml-2"><i class="fab fa-instagram" style="color: #111111;"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Our Team</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="team-member">
                        <img src="assets/img_avatar.png" alt="Paras">
                        <h4>Paras</h4>
                        <p>Founder</p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="team-member">
                        <img src="assets/img_avatar.png" alt="Shreyash">
                        <h4>Shreyash</h4>
                        <p>Founder</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workspace Section -->
    <section class="workspace spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Our Workspace</h2>
                    </div>
                    <img src="assets/wkpc.jpg" alt="Workspace" class="workspace-img mx-auto d-block">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7XgwQHe6OaUs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/mixitup@3.3.1/dist/mixitup.min.js" integrity="sha256-q4HLmF7zMEP2nKPVszwVUWZaTShZFr8jX691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    
    <script src="main1.js"></script>
</body>
</html>