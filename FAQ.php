<?php
session_name('SESSION_USER');
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css" type="text/css">
    <style>
        .faq-item { margin-bottom: 30px; }
        .faq-item h2 { font-size: 24px; font-weight: 700; color: #111111; margin-bottom: 10px; }
        .faq-item p { font-size: 16px; color: #3d3d3d; line-height: 1.6; }
        .spad { padding: 80px 0; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: 36px; font-weight: 700; color: #111111; }
        .back-to-home { text-align: center; margin-top: 40px; }
        .back-to-home a { 
            display: inline-block; 
            padding: 12px 30px; 
            font-size: 16px; 
            font-weight: 600; 
            color: #ffffff; 
            background-color: #111111; 
            border-radius: 5px; 
            text-decoration: none; 
            transition: background-color 0.3s; 
        }
        .back-to-home a:hover { 
            background-color: #3d3d3d; 
            color: #ffffff; 
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>Frequently Asked Questions</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span>FAQ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Frequently Asked Questions</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="faq-item">
                        <h2>What does Male fashion offer?</h2>
                        <p>schön is an exclusive e-commerce platform offering a curated selection of men’s clothing and accessories, including shirts, trousers, jackets, shoes, watches, and grooming products.</p>
                    </div>
                    <div class="faq-item">
                        <h2>How do I place an order?</h2>
                        <p>Browse our products, add items to your cart, and proceed to checkout. You’ll need to provide billing and shipping details, select a payment method, and agree to our Terms & Conditions.</p>
                    </div>
                    <div class="faq-item">
                        <h2>What payment methods do you accept?</h2>
                        <p>We accept Credit Card, PayPal, Cash on Delivery (COD), and Direct Bank Transfer. Virtual money and coupons can also be applied for discounts.</p>
                    </div>
                    <div class="faq-item">
                        <h2>How long does shipping take?</h2>
                        <p>Shipping times vary by location and method selected at checkout. Estimates are provided during the checkout process. Delays may occur due to holidays or unforeseen circumstances.</p>
                    </div>
                    <div class="faq-item">
                        <h2>Can I return an item?</h2>
                        <p>Yes, you can return unused items within 30 days of delivery in their original condition and packaging. Contact us at info@Malefashion.com to initiate a return.</p>
                    </div>
                    <div class="faq-item">
                        <h2>Do you offer free shipping?</h2>
                        <p>Free shipping is available on select orders, as indicated during checkout. Check our promotions page for current offers.</p>
                    </div>
                    <div class="faq-item">
                        <h2>How can I track my order?</h2>
                        <p>Once your order ships, you’ll receive a tracking number via email. Use this on our "Order Tracking" page or contact us for assistance.</p>
                    </div>
                    <div class="faq-item">
                        <h2>What if I receive a defective item?</h2>
                        <p>If an item arrives damaged or defective, contact us within 7 days of delivery at info@Malefashion.com with your order number and photos of the issue. We’ll arrange a replacement or refund.</p>
                    </div>
                    <div class="faq-item">
                        <h2>Can I cancel my order?</h2>
                        <p>Orders can be canceled before they ship. Contact us immediately at info@Malefashion.com with your order number to request cancellation.</p>
                    </div>
                    <div class="faq-item">
                        <h2>How do I contact customer support?</h2>
                        <p>Reach us at info@Malefashion.com or call +1 (555) 333 22 11. We’re here to assist you!</p>
                    </div>
                </div>
            </div>
            <!-- Back to Home Button -->
            <div class="back-to-home">
                <a href="./index.php">Back to Home</a>
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