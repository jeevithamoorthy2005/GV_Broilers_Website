<?php
require_once __DIR__ . '/includes/site-profile.php';

$aboutUs = null;
$aboutUsError = false;

$aboutUsQuery = mysqli_query(
    $con,
    'SELECT title, description, mission, vision, image FROM aboutus ORDER BY id ASC LIMIT 1'
);

if ($aboutUsQuery === false) {
    error_log('Unable to load About Us content: ' . mysqli_error($con));
    $aboutUsError = true;
} else {
    $aboutUs = mysqli_fetch_assoc($aboutUsQuery);
}

function getAboutUsImagePath($imagePath) {
    $imagePath = trim((string) $imagePath);
    $fallback = 'image/aboutus_sec2.jpg';

    if ($imagePath === '') {
        return $fallback;
    }

    // Accept a complete image URL stored in the database.
    if (preg_match('~^https?://~i', $imagePath)) {
        return $imagePath;
    }

    // The database uses "images/", but this project stores assets in "image/".
    $imagePath = str_replace('\\', '/', $imagePath);
    $imagePath = ltrim($imagePath, '/');
    if (strpos($imagePath, 'images/') === 0) {
        $imagePath = 'image/' . substr($imagePath, strlen('images/'));
    } elseif (strpos($imagePath, 'image/') !== 0) {
        $imagePath = 'image/' . $imagePath;
    }

    // Reject traversal and use the existing page image if the stored file is absent.
    $projectRoot = realpath(__DIR__);
    $resolvedImage = realpath(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagePath));
    if ($projectRoot !== false && $resolvedImage !== false && is_file($resolvedImage)) {
        $rootPrefix = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($resolvedImage, $rootPrefix) === 0) {
            return $imagePath;
        }
    }

    error_log('About Us image not found; using fallback image. Database path: ' . $imagePath);
    return $fallback;
}

$aboutTitle = trim((string) ($aboutUs['title'] ?? ''));
$aboutDescription = trim((string) ($aboutUs['description'] ?? ''));
$aboutMission = trim((string) ($aboutUs['mission'] ?? ''));
$aboutVision = trim((string) ($aboutUs['vision'] ?? ''));
$aboutImage = getAboutUsImagePath($aboutUs['image'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us | GV Broilers</title>

    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <style>
        @media (min-width: 992px) {
            .about-side-image {
                margin-top: 1in;
            }
        }
    </style>
</head>

<body>
<header>
    <div class="container-fluid bg-color py-2 m-0 text-white top-header">
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
                        <a class="nav-link active" aria-current="page" href="aboutus.php">ABOUT US</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="products.php">PRODUCTS</a>
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
                    <a class="nav-link active" href="aboutus.php">👨 About Us</a>
                </li>
                <li class="nav-item py-2">
                    <a class="nav-link" href="products.php">🥩 Products</a>
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
    <!-- About banner -->
    <section>
        <div class="container-fluid p-0">
            <img src="image/aboutus_banner.jpg" alt="About GV Broilers" width="100%">
        </div>
    </section>

    <!-- Who we are -->
    <section>
        <div class="container-fluid px-0">
            <div class="container">
                <div class="row my-4">
                    <div class="col-lg-6 col-12 p-3 about-side-image">
                        <img src="<?php echo htmlspecialchars($aboutImage, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($aboutTitle !== '' ? $aboutTitle : 'About GV Broilers', ENT_QUOTES, 'UTF-8'); ?>"
                             class="border-4 rounded-4"
                             width="100%">
                    </div>

                    <div class="col-lg-6 col-12 p-3 py-4 mt-5">
                        <?php if ($aboutUsError) { ?>
                            <p class="alert alert-warning" role="status">
                                About Us information could not be loaded right now.
                            </p>
                        <?php } elseif ($aboutUs === null) { ?>
                            <p class="alert alert-info" role="status">
                                About Us information has not been added yet.
                            </p>
                        <?php } ?>

                        <h2 class="text-color fw-bold">
                            <i class="fa-solid fa-store"></i>
                            <?php echo htmlspecialchars($aboutTitle !== '' ? $aboutTitle : 'WHO WE ARE', ENT_QUOTES, 'UTF-8'); ?>
                        </h2>
                        <h6 class="pt-3">
                            <?php echo nl2br(htmlspecialchars($aboutDescription !== '' ? $aboutDescription : 'About Us information is not available yet.', ENT_QUOTES, 'UTF-8')); ?>
                        </h6>

                        <div class="row pt-3">
                            <div class="col-12 col-md-6">
                                <h5 class="text-color fw-bold">OUR MISSION</h5>
                                <p><?php echo nl2br(htmlspecialchars($aboutMission !== '' ? $aboutMission : 'Mission information will be available soon.', ENT_QUOTES, 'UTF-8')); ?></p>
                            </div>
                            <div class="col-12 col-md-6">
                                <h5 class="text-color fw-bold">OUR VISION</h5>
                                <p><?php echo nl2br(htmlspecialchars($aboutVision !== '' ? $aboutVision : 'Vision information will be available soon.', ENT_QUOTES, 'UTF-8')); ?></p>
                            </div>
                        </div>

                        <div class="w-100 d-flex flex-column align-items-center text-center pt-4">
                            <h4 class="w-100 text-color text-center fst-italic">
                                Thank you for supporting our business.
                            </h4>
                            <h3 class="w-100 text-center text-color mb-0">
                                <i class="fa-solid fa-heart" aria-hidden="true"></i>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What we believe -->
    <section>
        <div class="container-fluid">
            <div class="container p-3 border-4 rounded-4 bg-card">
                <div class="row">
                    <div class="col-lg-3 col-6 fw-bold text-center text-color p-4 border-end">
                        <h2>WHAT</h2>
                        <h2>WE</h2>
                        <h2>BELIEVE</h2>
                    </div>

                    <div class="col-lg-3 col-6 text-center p-4 border-end">
                        <img src="image/aboutus_card1.jpg" alt="">
                        <h4 class="fw-bold">QUALITY</h4>
                        <p>We never compromise on the quality of our product.</p>
                    </div>

                    <div class="col-lg-3 col-6 text-center p-4 border-end">
                        <img src="image/aboutus_card2.jpg" alt="">
                        <h4 class="fw-bold">HYGIENE</h4>
                        <p>Clean shop, clean cutting and safe packing for your family.</p>
                    </div>

                    <div class="col-lg-3 col-6 text-center p-4">
                        <img src="image/aboutus_card3.jpg" alt="">
                        <h4 class="fw-bold">TRUST</h4>
                        <p>We value every customer and believe in building long-term trust.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why choose us -->
    <section>
        <div class="container-fluid px-lg-5 px-0 mt-2 mb-2">
            <div class="container">
                <h2 class="text-center text-color fw-bold">WHY CHOOSE US?</h2>

                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3 d-flex p-4 border-end">
                        <div class="px-3">
                            <img src="image/aboutus_card4.jpg" alt="">
                        </div>
                        <div>
                            <h6 class="fw-bold">FRESH DAILY</h6>
                            <p class="mb-0">Chicken is delivered daily for maximum freshness.</p>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3 d-flex p-4 border-end">
                        <div class="px-3">
                            <img src="image/aboutus_card5.jpg" alt="">
                        </div>
                        <div>
                            <h6 class="fw-bold">BEST QUALITY</h6>
                            <p class="mb-0">Clean and safe processing at every step.</p>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3 d-flex p-4 border-end">
                        <div class="px-3">
                            <img src="image/aboutus_card6.jpg" alt="">
                        </div>
                        <div>
                            <h6 class="fw-bold">CLEAN CUTTING</h6>
                            <p class="mb-0">Best quality meat for your family.</p>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3 d-flex p-4">
                        <div class="px-3">
                            <img src="image/aboutus_card7.jpg" alt="">
                        </div>
                        <div>
                            <h6 class="fw-bold">FRIENDLY SERVICE</h6>
                            <p class="mb-0">On-time delivery at your doorstep.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Closing message -->
    <section>
        <div class="container-fluid bg-footerimg py-4 px-0">
            <div class="container text-center py-4">
                <h5 class="fst-italic text-white playfair">Freshness is our promise.</h5>
                <h4 class="text-light fw-bold playfair-display">Quality is our tradition.</h4>
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
