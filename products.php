<?php
include 'includes/site-profile.php';

function getProductImagePath($imagePath, $productName) {
    $trimmedImage = trim((string) $imagePath);
    if ($trimmedImage !== '') {
        // Keep full web URLs from the products table as-is.
        if (preg_match('~^https?://~i', $trimmedImage)) {
            return $trimmedImage;
        }

        // Database rows use "images/...", while this project folder is "image/".
        $trimmedImage = str_replace('\\', '/', $trimmedImage);
        $trimmedImage = ltrim($trimmedImage, '/');
        if (strpos($trimmedImage, 'images/') === 0) {
            $trimmedImage = 'image/' . substr($trimmedImage, strlen('images/'));
        } elseif (strpos($trimmedImage, 'image/') !== 0) {
            $trimmedImage = 'image/' . $trimmedImage;
        }

        return $trimmedImage;
    }

    $normalized = strtolower($productName);
    $normalized = preg_replace('/[^a-z0-9]+/', '', $normalized);

    $fallbacks = [
        'wholechicken' => 'image/products_chicken1.jpg',
        'bonelesschicken' => 'image/products_chicken3.jpg',
        'chickenbreast' => 'image/products_chicken3.jpg',
        'chickenlegs' => 'image/products_chicken2.jpg',
        'chickenwings' => 'image/products_chicken4.jpg',
        'muttoncurrycut' => 'image/products_mutton1.jpg',
        'muttonboneless' => 'image/products_mutton2.jpg',
        'chops' => 'image/products_mutton4.jpg',
        'muttonmince' => 'image/products_mutton3.jpg',
        'muttonliver' => 'image/products_mutton4.jpg'
    ];

    return isset($fallbacks[$normalized]) ? $fallbacks[$normalized] : 'image/products_bg_img.jpg';
}

$productsQuery = mysqli_query($con, "
    SELECT p.id, p.name AS product_name, p.description, p.price, p.image, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE LOWER(p.status) = 'available'
    ORDER BY c.id, p.id
");

$groupedProducts = [];
$queryError = null;

if ($productsQuery === false) {
    $queryError = mysqli_error($con);
} else {
    while ($row = mysqli_fetch_assoc($productsQuery)) {
        $categoryName = trim((string) (isset($row['category_name']) ? $row['category_name'] : 'Products'));
        if ($categoryName === '') {
            $categoryName = 'Products';
        }
        $groupedProducts[$categoryName][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | GV Broilers</title>

    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
<header>
    <div class="container-fluid py-2 m-0 bg-color text-white top-header">
        <div class="row">
            <div class="col-12 col-lg-10 d-none d-lg-flex text-start left-header">
                <p class="mb-0 pb-0">
                    <i class="fa-solid fa-phone px-2"></i><?php echo htmlspecialchars($sitePhoneDisplay, ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="px-4 mb-0">|</p>
                <p class="mb-0">
                    <i class="fa-solid fa-envelope px-2"></i><?php echo htmlspecialchars($siteEmail, ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="px-4 mb-0">|</p>
                <p class="mb-0">
                    <i class="fa-solid fa-location-dot px-2"></i>
                    <?php echo htmlspecialchars($siteAddress, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <div class="col-12 col-lg-2 text-center text-lg-end px-4 right-header">
                <p class="mb-0">
                    Follow us:
                    <a class="text-white"
                       href="https://wa.me/<?php echo htmlspecialchars($siteWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>"
                       target="_blank"
                       rel="noopener"
                       aria-label="WhatsApp">
                        <i class="fa-brands fa-whatsapp p-1"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Desktop navigation -->
    <nav class="navbar navbar-expand-lg bg-white py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="home.php">
                <img src="image/logo2.png" alt="GV Broilers" width="140" class="p-0 m-0">
            </a>

            <button class="navbar-toggler border-0 shadow-none"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobileMenu"
                    aria-controls="mobileMenu"
                    aria-label="Open navigation menu">
                <i class="fa-solid fa-bars fs-2 text-success"></i>
            </button>

            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav">
                    <li class="nav-item px-2">
                        <a class="nav-link" href="home.php">HOME</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="aboutus.php">ABOUT US</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link active" aria-current="page" href="products.php">PRODUCTS</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="contactus.php">CONTACT</a>
                    </li>
                </ul>
            </div>

            <div class="d-none d-lg-flex gap-2">
            <a href="place-order.php" class="btn btn-success rounded-pill px-3">Place Order</a>
            <a href="tel:+<?php echo htmlspecialchars($siteWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>"
               class="btn btn-success rounded-pill px-4 d-none d-lg-block">
                <i class="fa-solid fa-phone"></i>
                Get in touch
            </a>
            </div>
        </div>
    </nav>

    <!-- Mobile navigation -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuTitle">
        <div class="offcanvas-header border-bottom">
            <h4 class="text-success fw-bold mb-0" id="mobileMenuTitle">GV BROILERS</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <ul class="navbar-nav">
                <li class="nav-item py-2">
                    <a class="nav-link" href="home.php">🏠 Home</a>
                </li>
                <li class="nav-item py-2">
                    <a class="nav-link" href="aboutus.php">👨 About Us</a>
                </li>
                <li class="nav-item py-2">
                    <a class="nav-link active" href="products.php">🥩 Products</a>
                </li>
                <li class="nav-item py-2">
                    <a class="nav-link" href="contactus.php">📞 Contact</a>
                </li>
            </ul>

            <hr>

            <a href="place-order.php" class="btn btn-success w-100 rounded-pill mb-2">Place Order</a>
            <a href="tel:+<?php echo htmlspecialchars($siteWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success w-100 rounded-pill">
                <i class="fa-solid fa-phone"></i>
                Call Now
            </a>
        </div>
    </div>
</header>

<main>
    <!-- Top banner -->
    <section>
        <div class="container-fluid p-0">
            <img src="image/products_bg_img.jpg" alt="GV Broilers products" width="100%">
        </div>
    </section>

    <!-- Product category links -->
    <section>
        <div class="container-fluid">
            <h3 class="text-center text-color fw-bold p-4">--OUR PRODUCTS--</h3>

            <div class="container">
                <div class="row">
                    <div data-aos="fade-up" class="col-12 col-lg-6">
                        <div class="position-relative">
                            <img src="image/products_card1.png" alt="Chicken products" width="100%">
                        </div>
                        <div class="position-absolute product-btn">
                            <a class="btn btn-success rounded-4 px-md-2" href="#chicken">
                                View our chicken products
                            </a>
                        </div>
                    </div>

                    <div data-aos="fade-down" class="col-12 col-lg-6">
                        <div class="position-relative">
                            <img src="image/products_card2.png" alt="Mutton products" width="100%">
                        </div>
                        <div class="position-absolute product-btn">
                            <a class="btn btn-success px-md-2 rounded-4" href="#mutton">
                                View our mutton products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($queryError) { ?>
        <p class="container text-center text-danger py-4">
            Products could not be loaded. Check the products table and try again.
        </p>
    <?php } elseif (empty($groupedProducts)) { ?>
        <p class="container text-center py-4">No products are currently available.</p>
    <?php } else { ?>
        <?php foreach ($groupedProducts as $categoryName => $products) { ?>
            <?php
                $sectionId = strtolower(str_replace(' ', '-', $categoryName));
                $isMutton = strtolower($categoryName) === 'mutton';
                $headingClass = $isMutton ? 'mutton-text' : 'text-color';
            ?>
            <section id="<?php echo htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8'); ?>" class="container-fluid">
                <h3 class="text-center fw-bold p-4 <?php echo $headingClass; ?>">
                    --<?php echo htmlspecialchars(strtoupper($categoryName), ENT_QUOTES, 'UTF-8'); ?> PRODUCTS--
                </h3>
                <div class="container p-0">
                    <div class="row">
                        <?php foreach ($products as $product) { ?>
                            <?php
                                $productName = trim((string) (isset($product['product_name']) ? $product['product_name'] : ''));
                                $imagePath = getProductImagePath(isset($product['image']) ? $product['image'] : '', $productName);
                                $description = trim((string) (isset($product['description']) ? $product['description'] : ''));
                            ?>
                            <div class="col-6 col-lg-3 mb-3 text-center">
                                <div data-aos="fade-right" class="card h-100">
                                    <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
                                         class="border rounded-top">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold <?php echo $headingClass; ?>">
                                            <?php echo htmlspecialchars(strtoupper($productName), ENT_QUOTES, 'UTF-8'); ?>
                                        </h6>
                                        <p class="mb-0">
                                            <?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>
                                        </p>
                                        <?php if (!empty($product['price'])) { ?>
                                            <p class="fw-bold text-success mb-0">
                                                ₹<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
        <?php } ?>
    <?php } ?>

    <!-- Order footer -->
    <section>
        <div class="container-fluid bg-footerimg py-4">
            <div class="container text-center py-4">
                <h5 class="fst-italic text-white playfair">Fresh meat. Healthy life.</h5>
                <h4 class="text-success playfair-display">Order now for fresh chicken and mutton</h4>

                <div class="d-lg-flex justify-content-center gap-5">
                    <a href="place-order.php" class="btn btn-success rounded-pill px-4 py-2">
                        <i class="fa-solid fa-bag-shopping"></i>
                        Place Order
                    </a>
                    <a href="tel:+<?php echo htmlspecialchars($siteWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-phone"></i>
                        Call Us
                    </a>

                    <a href="https://wa.me/<?php echo htmlspecialchars($siteWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>?text=Hi%20GV%20Broilers%2C%20I%27d%20like%20to%20place%20an%20order."
                       target="_blank"
                       rel="noopener"
                       class="btn btn-success rounded-pill px-4 py-2">
                        <i class="fa-brands fa-whatsapp"></i>
                        WhatsApp Us
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/aos.js"></script>
<script>
    if (window.AOS) {
        AOS.init();
    }
</script>
</body>
</html>
