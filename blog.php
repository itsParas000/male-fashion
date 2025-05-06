<?php
session_name('SESSION_USER');
session_start();
include 'php/config.php';

// Check if user is logged in
if (!isset($_SESSION['valid']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get blog ID from URL
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Define blog content
$blogs = [
    1 => [
        'title' => 'Top 5 Must-Have Jackets for Winter 2025',
        'date' => '16 February 2025',
        'image' => 'img/blog/blog-1.jpg',
        'content' => '
            <p>Winter 2025 is shaping up to be a stylish season, and the right jacket can make all the difference. Here are our top five must-have jackets to keep you warm and on-trend:</p>
            <h4>1. The Oversized Puffer Jacket</h4>
            <p>Bold, cozy, and effortlessly cool, oversized puffer jackets are dominating winter fashion. Opt for neutral tones like black or olive for versatility, or go bold with metallics. Pair with slim-fit jeans to balance the volume.</p>
            <h4>2. Shearling-Lined Denim Jacket</h4>
            <p>A denim jacket with shearling lining offers a rugged yet refined look. Perfect for layering over hoodies, this jacket transitions from casual outings to evening events with ease.</p>
            <h4>3. Tailored Wool Overcoat</h4>
            <p>For a sophisticated edge, a tailored wool overcoat in camel or charcoal is a timeless choice. Look for one with a modern slim fit to wear over suits or casual knits.</p>
            <h4>4. Techwear Parka</h4>
            <p>Techwear parkas combine functionality with futuristic style. Waterproof fabrics and multiple pockets make them ideal for urban adventurers facing harsh winter weather.</p>
            <h4>5. Quilted Bomber Jacket</h4>
            <p>The quilted bomber is back, blending retro vibes with modern materials. Choose one in a deep color like navy or burgundy for a versatile addition to your wardrobe.</p>
            <p>With these jackets, you’ll be ready to face winter 2025 in style. Check out our latest collection to find your perfect fit!</p>
        ',
    ],
    2 => [
        'title' => 'How to Style Sneakers for Every Occasion',
        'date' => '21 February 2025',
        'image' => 'img/blog/blog-2.jpg',
        'content' => '
            <p>Sneakers have evolved from gym wear to a fashion staple. Here’s how to style them for any occasion in 2025:</p>
            <h4>Casual Weekends</h4>
            <p>Pair classic white sneakers with relaxed chinos and a graphic tee. Add a denim jacket for a laid-back vibe perfect for coffee runs or meetups with friends.</p>
            <h4>Office Chic</h4>
            <p>Yes, sneakers can work in the office! Choose sleek, minimalist leather sneakers in black or white. Pair with tailored trousers and a crisp button-down for a smart-casual look.</p>
            <h4>Date Night</h4>
            <p>Elevate your sneakers with slim-fit dark jeans and a fitted blazer. Opt for sneakers with subtle details, like suede accents, to add sophistication without sacrificing comfort.</p>
            <h4>Festival Vibes</h4>
            <p>For concerts or festivals, go bold with high-top sneakers in vibrant colors. Match with cargo shorts and a patterned shirt to stand out in the crowd.</p>
            <h4>Athleisure on Point</h4>
            <p>Combine performance sneakers with joggers and a lightweight hoodie. Add a bomber jacket for an athleisure look that’s both functional and stylish.</p>
            <p>Whatever the occasion, there’s a sneaker style for you. Explore our sneaker collection to find your next favorite pair!</p>
        ',
    ],
    3 => [
        'title' => 'The Rise of Sustainable Fashion in 2025',
        'date' => '28 February 2025',
        'image' => 'img/blog/blog-3.jpg',
        'content' => '
            <p>Sustainable fashion is no longer a trend—it’s a movement. In 2025, eco-conscious style is taking center stage. Here’s how it’s shaping the industry:</p>
            <h4>Eco-Friendly Materials</h4>
            <p>Brands are embracing materials like organic cotton, recycled polyester, and biodegradable leather. These reduce environmental impact without compromising style.</p>
            <h4>Second-Hand and Upcycling</h4>
            <p>Thrifting and upcycled clothing are booming. Designers are reimagining vintage pieces into modern masterpieces, giving old garments new life.</p>
            <h4>Transparent Supply Chains</h4>
            <p>Consumers demand transparency, and brands are responding with detailed supply chain information. From sourcing to production, ethical practices are a priority.</p>
            <h4>Slow Fashion Movement</h4>
            <p>Quality over quantity is the mantra of slow fashion. Investing in timeless pieces that last reduces waste and promotes mindful consumption.</p>
            <h4>Innovative Technologies</h4>
            <p>From 3D-printed clothing to lab-grown fabrics, technology is revolutionizing sustainable fashion, offering creative solutions to environmental challenges.</p>
            <p>At Male Fashion, we’re committed to sustainability. Shop our eco-friendly collection and join the movement for a greener future!</p>
        ',
    ],
];

// Get blog details or show default
$blog = isset($blogs[$blog_id]) ? $blogs[$blog_id] : null;
if (!$blog) {
    $blog = [
        'title' => 'Blog Not Found',
        'date' => date('d F Y'),
        'image' => 'img/blog/default.jpg',
        'content' => '<p>Sorry, the blog post you are looking for does not exist.</p>',
    ];
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> | Male Fashion</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">

    <style>
        .blog__details {
            padding: 50px 0;
        }
        .blog__details__pic img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .blog__details__text h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .blog__details__text span {
            font-size: 14px;
            color: #888;
            display: block;
            margin-bottom: 20px;
        }
        .blog__details__text p {
            font-size: 16px;
            line-height: 28px;
            margin-bottom: 20px;
        }
        .blog__details__text h4 {
            font-size: 20px;
            font-weight: 600;
            margin: 30px 0 15px;
        }
        .back-to-home {
            display: inline-block;
            margin-top: 30px;
            color: #e53637;
            font-weight: 600;
        }
        .back-to-home:hover {
            text-decoration: underline;
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
                        <h4>Blog</h4>
                        <div class="breadcrumb__links">
                            <a href="./index.php">Home</a>
                            <span><?php echo htmlspecialchars($blog['title']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Details -->
    <section class="blog__details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="blog__details__pic">
                        <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                    </div>
                    <div class="blog__details__text">
                        <h2><?php echo htmlspecialchars($blog['title']); ?></h2>
                        <span><?php echo htmlspecialchars($blog['date']); ?></span>
                        <?php echo $blog['content']; ?>
                        <a href="./index.php" class="back-to-home">Back to Home</a>
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
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7XgwQHe6OaUs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main1.js"></script>
</body>
</html>