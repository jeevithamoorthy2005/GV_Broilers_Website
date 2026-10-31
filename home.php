<?php
require_once 'includes/db.php';

$homeProfile = array();
$homeProfileError = false;

if (!$con) {
    error_log('Unable to connect to the database for the Home page.');
    $homeProfileError = true;
} elseif (!mysqli_set_charset($con, 'utf8mb4')) {
    error_log('Unable to set Home page database character set: ' . mysqli_error($con));
    $homeProfileError = true;
} else {
    $homeProfileQuery = mysqli_query(
        $con,
        'SELECT companyname, logourl, mobile, email, address
         FROM profile1
         LIMIT 1'
    );

    if ($homeProfileQuery === false) {
        error_log('Unable to load Home page profile: ' . mysqli_error($con));
        $homeProfileError = true;
    } else {
        $homeProfile = mysqli_fetch_assoc($homeProfileQuery);
        if ($homeProfile === null) {
            $homeProfile = array();
            $homeProfileError = true;
        }
    }
}

$companyName = trim(isset($homeProfile['companyname']) ? $homeProfile['companyname'] : '');
if ($companyName === '') {
    $companyName = 'GV Broilers';
}

$siteMobile = trim((string) (isset($homeProfile['mobile']) ? $homeProfile['mobile'] : ''));
$siteEmail = trim(isset($homeProfile['email']) ? $homeProfile['email'] : '');
$siteAddress = trim(isset($homeProfile['address']) ? $homeProfile['address'] : '');
$sitePhoneDigits = preg_replace('/\D+/', '', $siteMobile);
$siteWhatsappNumber = strlen($sitePhoneDigits) === 10
    ? '91' . $sitePhoneDigits
    : $sitePhoneDigits;
$sitePhoneDisplay = strlen($sitePhoneDigits) === 10
    ? '+91 ' . $sitePhoneDigits
    : $siteMobile;

$companyLogo = 'image/logo2.png';
$storedLogoPath = trim(isset($homeProfile['logourl']) ? $homeProfile['logourl'] : '');
if ($storedLogoPath !== '' && strpos($storedLogoPath, '..') === false) {
    $storedLogoPath = str_replace('\\', '/', $storedLogoPath);
    $logoCandidates = array($storedLogoPath);
    if (strpos($storedLogoPath, 'images/') === 0) {
        $logoCandidates[] = 'image/' . substr($storedLogoPath, strlen('images/'));
    }

    foreach ($logoCandidates as $logoCandidate) {
        $logoFile = __DIR__ . DIRECTORY_SEPARATOR .
            str_replace('/', DIRECTORY_SEPARATOR, $logoCandidate);
        if (is_file($logoFile)) {
            $companyLogo = $logoCandidate;
            break;
        }
    }

    if ($companyLogo === 'image/logo2.png') {
        error_log('Home page profile logo file does not exist: ' . $storedLogoPath);
    }
}

$orderMessage = rawurlencode("Hi " . $companyName . ", I'd like to place an order.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?> | Fresh Chicken and Mutton</title>

    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <style>
        h1, h2, h3, h4, h5, h6 {
            font-family: "Playfair Display", serif;
            font-optical-sizing: auto;
            font-style: normal;
            font-weight: 700;
        }

        .order-button {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .carousel-image {
            display: block;
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
<?php if ($homeProfileError) { ?>
    <div class="container-fluid alert alert-warning mb-0" role="status">
        Company profile details could not be loaded from the database.
    </div>
<?php } ?>
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
                <p class="mb-0 playfair">
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
</header>

<!-- Desktop navigation -->
<nav class="navbar navbar-expand-lg bg-white py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-success" href="home.php">
            <img src="<?php echo htmlspecialchars($companyLogo, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?>" width="140" class="p-0 m-0">
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
                    <a class="nav-link active" aria-current="page" href="home.php">HOME</a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link" href="aboutus.php">ABOUT US</a>
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
        <h4 class="text-success fw-bold mb-0" id="mobileMenuTitle"><?php echo htmlspecialchars(strtoupper($companyName), ENT_QUOTES, 'UTF-8'); ?></h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <ul class="navbar-nav">
            <li class="nav-item py-2">
                <a class="nav-link active" href="home.php">🏠 Home</a>
            </li>
            <li class="nav-item py-2">
                <a class="nav-link" href="aboutus.php">👨 About Us</a>
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

<main>
    <!-- Image carousel -->
    <div class="container-fluid p-0">
        <div id="mydiv1" class="owl-carousel owl-theme">
            <div class="item">
                <img class="carousel-image" src="image/home_carousal1.png" alt="Fresh chicken and meat">
            </div>
            <div class="item">
                <img class="carousel-image" src="image/home_carousal2.png" alt="<?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?> products">
            </div>
            <div class="item">
                <img class="carousel-image" src="image/home_carousal3.png" alt="Quality meat from <?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="item">
                <img class="carousel-image" src="image/home_carousal4.png" alt="Visit <?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
    </div>

    <!-- Shop benefits -->
    <section class="container-fluid bg-card px-lg-5 px-0 mt-2">
        <div class="row">
            <div class="col-12 col-md-6 col-lg-3 d-flex p-4">
                <div class="px-3">
                    <img src="image/card1.jpg" alt="" class="p-0 m-0">
                </div>
                <div>
                    <h6>FARM FRESH</h6>
                    <p class="mb-0">Sourced daily from trusted farms.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3 d-flex p-4">
                <div class="px-3">
                    <img src="image/card2.jpg" alt="">
                </div>
                <div>
                    <h6>HYGIENIC PROCESS</h6>
                    <p class="mb-0">Clean and safe processing at every step.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3 d-flex p-4">
                <div class="px-3">
                    <img src="image/card3.jpg" alt="">
                </div>
                <div>
                    <h6>PREMIUM QUALITY</h6>
                    <p class="mb-0">Best quality meat for your family.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3 d-flex p-4">
                <div class="px-3">
                    <img src="image/card4.jpg" alt="">
                </div>
                <div>
                    <h6>FAST DELIVERY</h6>
                    <p class="mb-0">On-time delivery at your doorstep.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop information -->
    <section class="container-fluid">
        <div class="container p-0 mt-4 mb-3 pb-0 bg-card border-4 rounded-4">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <div class="p-4">
                        <h4 class="text-color">TRUSTED QUALITY,</h4>
                        <h5 class="text-style">Every Time</h5>
                        <p class="mb-0">
                            From farm to plate, we ensure freshness, hygiene and quality
                            at every step so you can enjoy healthy and delicious meals
                            with your family.
                        </p>
                    </div>
                </div>

                <div class="col-12 col-lg-4 d-flex pt-4 mb-4 pb-5">
                    <div class="col-3 px-2 border-end text-center">
                        <img src="image/footer_card1.jpg" alt="">
                        <h4 class="text-color">5000+</h4>
                        <p class="mb-0">Happy Customers</p>
                    </div>
                    <div class="col-3 px-2 border-end text-center">
                        <img src="image/footer_card2.jpg" alt="">
                        <h4 class="text-color">15+</h4>
                        <p class="mb-0">Trusted Farms</p>
                    </div>
                    <div class="col-3 px-2 border-end text-center">
                        <img src="image/footer_card3.png" alt="">
                        <h4 class="text-color">20+</h4>
                        <p class="mb-0">Years of Experience</p>
                    </div>
                    <div class="col-3 px-2 text-center">
                        <img src="image/footer_card4.jpg" alt="">
                        <h4 class="text-color">100%</h4>
                        <p class="mb-0">Fresh &amp; Natural</p>
                    </div>
                </div>

                <div class="col-12 col-lg-4 d-flex">
                    <img src="image/home_footer.png" alt="<?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?>" width="100%">
                </div>
            </div>
        </div>
    </section>
</main>

<!-- WhatsApp order button -->
<a class="btn btn-success rounded-pill px-4 order-button" href="place-order.php">
    <i class="fa-solid fa-bag-shopping"></i>
    Place Order
</a>

<footer class="container-fluid bg-color text-white text-center fw-light">
    <p class="small p-3 mb-0">
        &copy; <span id="currentYear"></span> <?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.
        <span class="px-2">|</span><a class="text-white" href="admin-login.php">Admin login</a>
    </p>
</footer>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/aos.js"></script>

<script>
    document.getElementById("currentYear").textContent = new Date().getFullYear();

    $(document).ready(function () {
        $("#mydiv1").owlCarousel({
            items: 1,
            margin: 0,
            nav: false,
            dots: true,
            loop: true,
            autoplay: true,
            autoplayTimeout: 3500,
            autoplaySpeed: 1500,
            smartSpeed: 1000,
            autoHeight: true
        });
    });

    if (window.AOS) {
        AOS.init();
    }
</script>
</body>
</html>
