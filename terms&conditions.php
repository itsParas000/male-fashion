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
    <title>Terms & Conditions | Male Fashion</title>
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
        .spad { padding: 80px 0; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: 36px; font-weight: 700; color: #111111; }
        .content h2 { font-size: 24px; font-weight: 700; color: #111111; margin: 25px 0 15px; }
        .content p { font-size: 16px; color: #3d3d3d; margin-bottom: 20px; line-height: 1.6; }
        .content ul { list-style-type: disc; padding-left: 20px; margin-bottom: 20px; }
        .content ul li { font-size: 16px; color: #3d3d3d; margin-bottom: 10px; }
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
                        <h4>Terms & Conditions</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span>Terms & Conditions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms & Conditions Section -->
    <section class="terms spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Terms & Conditions</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="content">
                        <p>Welcome to Male Fashion, an exclusive e-commerce platform for men’s clothing and accessories. By accessing or using our website, you agree to comply with and be bound by the following Terms & Conditions. Please read them carefully before making any purchases.</p>

                        <h2>1. General Terms</h2>
                        <p>Male Fashion (“we,” “us,” or “our”) operates this website solely for the purpose of selling men’s clothing, footwear, and accessories. These Terms & Conditions govern your use of our website and services. We reserve the right to update or modify these terms at any time without prior notice. Your continued use of the site constitutes acceptance of the updated terms.</p>

                        <h2>2. Eligibility</h2>
                        <p>Our products and services are intended for individuals aged 18 and above. By using our website, you confirm that you are at least 18 years old and legally capable of entering into binding contracts.</p>

                        <h2>3. Products & Pricing</h2>
                        <ul>
                            <li>All products listed on Male Fashion are exclusively for men, including but not limited to shirts, trousers, jackets, shoes, watches, and grooming accessories.</li>
                            <li>Prices are displayed in INR (₹) and include applicable taxes unless otherwise stated.</li>
                            <li>We strive to ensure pricing accuracy but reserve the right to correct any errors. If a product is mispriced, we may cancel orders placed for that product and notify you accordingly.</li>
                            <li>Product availability is subject to change without notice.</li>
                        </ul>

                        <h2>4. Orders & Payments</h2>
                        <ul>
                            <li>Orders are subject to acceptance and availability. We may refuse or cancel an order for any reason, including payment issues or suspected fraud.</li>
                            <li>Payment methods include Credit Card, PayPal, Cash on Delivery (COD), and Bank Transfer. All transactions are processed securely.</li>
                            <li>Virtual money or coupons may be applied as discounts, subject to their specific terms of use.</li>
                            <li>Once an order is placed, you will receive a confirmation email with an order number and tracking details when available.</li>
                        </ul>

                        <h2>5. Shipping & Delivery</h2>
                        <ul>
                            <li>We ship to addresses provided during checkout. Shipping costs and estimated delivery times are calculated based on the selected shipping rate.</li>
                            <li>Free shipping may apply to certain orders as indicated at checkout.</li>
                            <li>Delivery times are estimates and may vary due to unforeseen circumstances (e.g., weather, customs delays).</li>
                            <li>You are responsible for providing accurate shipping information. We are not liable for delays or losses due to incorrect addresses.</li>
                        </ul>

                        <h2>6. Returns & Refunds</h2>
                        <ul>
                            <li>Items may be returned within 30 days of delivery if unused, in original packaging, and in the same condition as received.</li>
                            <li>To initiate a return, contact us at info@malefashion.com with your order number.</li>
                            <li>Refunds will be processed to the original payment method within 14 days of receiving the returned item, excluding shipping costs.</li>
                            <li>Personalized or custom-made items are non-returnable unless defective.</li>
                        </ul>

                        <h2>7. Intellectual Property</h2>
                        <p>All content on this website, including images, logos, and text, is the property of Male Fashion or its licensors and is protected by copyright and trademark laws. You may not reproduce, distribute, or use any content without prior written consent.</p>

                        <h2>8. User Conduct</h2>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use our website for unlawful purposes.</li>
                            <li>Attempt to hack, disrupt, or overload our systems.</li>
                            <li>Submit false or misleading information during account creation or checkout.</li>
                        </ul>

                        <h2>9. Limitation of Liability</h2>
                        <p>Male Fashion is not liable for any indirect, incidental, or consequential damages arising from your use of our website or products. Our liability is limited to the purchase price of the product(s) in question.</p>

                        <h2>10. Governing Law</h2>
                        <p>These Terms & Conditions are governed by the laws of India. Any disputes will be resolved in the courts of Mumbai, India.</p>

                        <h2>11. Contact Us</h2>
                        <p>If you have questions about these Terms & Conditions, please reach out to us:</p>
                        <ul>
                            <li>Email: info@malefashion.com</li>
                            <li>Phone: +91 123 456 789</li>
                            <li>Address: Sanpada Sec. 9, Navi Mumbai, 400705</li>
                        </ul>
                    </div>
                    <!-- Back to Home Button -->
                    <div class="back-to-home">
                        <a href="./index.php">Back to Home</a>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7X691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/mixitup@3.3.1/dist/mixitup.min.js" integrity="sha256-q4HLmF7zMEP2nKPVszwVUWZaTShZFr8jX691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main1.js"></script>
</body>
</html>