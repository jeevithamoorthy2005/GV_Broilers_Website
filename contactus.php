<?php
ob_start();
require_once 'includes/db.php';

if (!$con) {
    http_response_code(500);
    exit('Unable to connect to the GV Broilers database.');
}

if (!mysqli_set_charset($con, 'utf8mb4')) {
    error_log('Unable to set database character set: ' . mysqli_error($con));
    http_response_code(500);
    exit('Unable to prepare the database connection.');
}

$contactQuery = mysqli_query(
    $con,
    'SELECT name, email, phone, address, opening_hours, map_url
     FROM contactus
     LIMIT 1'
);

if ($contactQuery === false) {
    error_log('Unable to load Contact Us details: ' . mysqli_error($con));
    http_response_code(500);
    exit('Unable to load Contact Us details from the contactus table.');
}

$contactDetails = mysqli_fetch_assoc($contactQuery);
if ($contactDetails === null) {
    http_response_code(500);
    exit('No Contact Us details were found in the contactus table.');
}

$contactName = trim($contactDetails['name']);
$contactEmail = trim($contactDetails['email']);
$contactPhone = trim($contactDetails['phone']);
$contactPhoneLink = preg_replace('/\D+/', '', $contactPhone);
$contactWhatsappNumber = strlen($contactPhoneLink) === 10
    ? '91' . $contactPhoneLink
    : $contactPhoneLink;
$contactAddress = trim($contactDetails['address']);
$contactOpeningHours = trim($contactDetails['opening_hours']);
$contactMapUrl = trim($contactDetails['map_url']);
$contactMapUrlParts = parse_url($contactMapUrl);
$hasValidContactMapUrl = is_array($contactMapUrlParts) &&
    isset($contactMapUrlParts['scheme'], $contactMapUrlParts['host']) &&
    strtolower($contactMapUrlParts['scheme']) === 'https';
$contactDirectionsUrl = $hasValidContactMapUrl
    ? $contactMapUrl
    : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($contactAddress);
$contactEmbedUrl = $hasValidContactMapUrl
    ? $contactMapUrl
    : 'https://www.google.com/maps?q=' . rawurlencode($contactAddress) . '&output=embed';

$formValues = array(
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
);
$formError = '';
$messageSent = isset($_GET['sent']) && $_GET['sent'] === '1';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formValues as $field => $unusedValue) {
        $formValues[$field] = isset($_POST[$field]) && is_string($_POST[$field])
            ? trim($_POST[$field])
            : '';
    }

    $validText = true;
    foreach ($formValues as $value) {
        if (preg_match('//u', $value) !== 1) {
            $validText = false;
            break;
        }
    }

    if (!$validText) {
        $formError = 'Please enter valid text in all fields.';
    } elseif (
        $formValues['name'] === '' ||
        $formValues['email'] === '' ||
        $formValues['subject'] === '' ||
        $formValues['message'] === ''
    ) {
        $formError = 'Please complete all fields.';
    } elseif (filter_var($formValues['email'], FILTER_VALIDATE_EMAIL) === false) {
        $formError = 'Please enter a valid email address.';
    } else {
        $fieldLimits = array(
            'name' => 150,
            'email' => 255,
            'subject' => 255,
            'message' => 5000
        );

        foreach ($fieldLimits as $field => $limit) {
            preg_match_all('/./us', $formValues[$field], $characters);
            if (count($characters[0]) > $limit) {
                $formError = 'Please shorten the ' . $field . ' field.';
                break;
            }
        }
    }

    if ($formError === '') {
        $insertStatement = mysqli_prepare(
            $con,
            'INSERT INTO contact_messages (name, email, subject, message)
             VALUES (?, ?, ?, ?)'
        );

        if ($insertStatement === false) {
            error_log('Unable to prepare Contact Us submission: ' . mysqli_error($con));
            $formError = 'Your message could not be saved. Please try again later.';
        } elseif (!mysqli_stmt_bind_param(
            $insertStatement,
            'ssss',
            $formValues['name'],
            $formValues['email'],
            $formValues['subject'],
            $formValues['message']
        )) {
            error_log('Unable to bind Contact Us submission: ' . mysqli_stmt_error($insertStatement));
            $formError = 'Your message could not be saved. Please try again later.';
        } elseif (!mysqli_stmt_execute($insertStatement)) {
            error_log('Unable to save Contact Us submission: ' . mysqli_stmt_error($insertStatement));
            $formError = 'Your message could not be saved. Please try again later.';
        } else {
            mysqli_stmt_close($insertStatement);
            header('Location: contactus.php?sent=1#contact-form');
            exit;
        }

        if ($insertStatement !== false) {
            mysqli_stmt_close($insertStatement);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us | GV Broilers</title>

    <link rel="stylesheet" href="css/aos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Frijole&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
<header>
    <div class="container-fluid bg-color py-2 m-0 text-white top-header">
        <div class="row">
            <div class="col-12 col-lg-10 d-none d-lg-flex text-start left-header">
                <p class="mb-0 pb-0">
                    <i class="fa-solid fa-phone px-2"></i><?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="px-4 mb-0">|</p>
                <p class="mb-0">
                    <i class="fa-solid fa-envelope px-2"></i><?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="px-4 mb-0">|</p>
                <p class="mb-0">
                    <i class="fa-solid fa-location-dot px-2"></i>
                    <?php echo htmlspecialchars($contactAddress, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <div class="col-12 col-lg-2 text-center text-lg-end px-4 right-header">
                <p class="mb-0">
                    Follow us:
                    <a class="text-white"
                       href="https://wa.me/<?php echo htmlspecialchars($contactWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>"
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
                        <a class="nav-link" href="products.php">PRODUCTS</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link active" aria-current="page" href="contactus.php">CONTACT</a>
                    </li>
                </ul>
            </div>

            <div class="d-none d-lg-flex gap-2">
            <a href="place-order.php" class="btn btn-success rounded-pill px-3">Place Order</a>
            <a href="tel:<?php echo htmlspecialchars($contactPhoneLink, ENT_QUOTES, 'UTF-8'); ?>"
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
                    <a class="nav-link" href="products.php">🥩 Products</a>
                </li>
                <li class="nav-item py-2">
                    <a class="nav-link active" href="contactus.php">📞 Contact</a>
                </li>
            </ul>

            <hr>

            <a href="place-order.php" class="btn btn-success w-100 rounded-pill mb-2">Place Order</a>
            <a href="tel:<?php echo htmlspecialchars($contactPhoneLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success w-100 rounded-pill">
                <i class="fa-solid fa-phone"></i>
                Call Now
            </a>
        </div>
    </div>
</header>

<main>
    <!-- Map -->
    <section>
        <div class="container-fluid px-0">
            <iframe
                title="GV Broilers location map"
                src="<?php echo htmlspecialchars($contactEmbedUrl, ENT_QUOTES, 'UTF-8'); ?>"
                width="100%"
                height="450"
                style="border:0;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin">
            </iframe>
        </div>
    </section>

    <!-- Contact details and message form -->
    <section>
        <div class="container-fluid mt-3">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-lg-6 d-flex justify-content-center">
                        <div class="card mb-3 mb-lg-0 mt-4 bg-card text-center pt-4 px-3">
                            <h1><i class="fa-solid fa-location-dot"></i></h1>
                            <h4><?php echo htmlspecialchars($contactName, ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="mb-0">Phone: <?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-0">Email: <?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-3"><?php echo htmlspecialchars($contactAddress, ENT_QUOTES, 'UTF-8'); ?></p>

                            <a class="btn btn-success mb-3"
                               href="<?php echo htmlspecialchars($contactDirectionsUrl, ENT_QUOTES, 'UTF-8'); ?>"
                               target="_blank"
                               rel="noopener">
                                Get Directions
                            </a>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h4 class="text-color fw-bold">
                            <i class="fa-regular fa-comment-dots"></i>
                            SEND US A MESSAGE
                        </h4>

                        <form id="contact-form" action="contactus.php#contact-form" method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input id="name" name="name" type="text" class="form-control"
                                       maxlength="150" value="<?php echo htmlspecialchars($formValues['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                       placeholder="Enter your name" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" name="email" type="email" class="form-control"
                                       maxlength="255" value="<?php echo htmlspecialchars($formValues['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                       placeholder="Enter your email" required>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input id="subject" name="subject" type="text" class="form-control"
                                       maxlength="255" value="<?php echo htmlspecialchars($formValues['subject'], ENT_QUOTES, 'UTF-8'); ?>"
                                       placeholder="Enter a subject" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea id="message" name="message" class="form-control" rows="4"
                                          maxlength="5000" placeholder="Enter your message" required><?php echo htmlspecialchars($formValues['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">Send Message</button>
                            <?php if ($messageSent) { ?>
                                <p class="mt-2 text-success" role="status">Your message was sent successfully.</p>
                            <?php } elseif ($formError !== '') { ?>
                                <p class="mt-2 text-danger" role="alert">
                                    <?php echo htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact information cards -->
    <section>
        <div class="container-fluid mt-4">
            <div class="container text-center mb-3 p-3 border rounded bg-card">
                <div class="row">
                    <div class="col-6 col-lg-3 border-end">
                        <img src="image/contact_card1.jpg" alt="">
                        <h4>Call us</h4>
                        <img src="image/contactus_design.jpg" alt="">
                        <h5 class="p-0 m-0"><?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?></h5>
                        <a href="tel:<?php echo htmlspecialchars($contactPhoneLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success rounded-pill w-100">
                            <i class="fa-solid fa-phone"></i>
                            Call Us
                        </a>
                    </div>

                    <div class="col-6 col-lg-3 border-end">
                        <img src="image/contact_card2.jpg" alt="">
                        <h4>WhatsApp</h4>
                        <img src="image/contactus_design.jpg" alt="">
                        <p class="mb-0">Have an order or enquiry?</p>
                        <p>Message us on WhatsApp.</p>
                        <a href="https://wa.me/<?php echo htmlspecialchars($contactWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?>"
                           target="_blank"
                           rel="noopener"
                           class="btn btn-success rounded-pill w-100">
                            <i class="fa-brands fa-whatsapp"></i>
                            Chat on WhatsApp
                        </a>
                    </div>

                    <div class="col-6 col-lg-3 border-end mt-4 mt-lg-0">
                        <img src="image/contact_card3.jpg" alt="">
                        <h4>Opening hours</h4>
                        <img src="image/contactus_design.jpg" alt="">
                        <p><?php echo htmlspecialchars($contactOpeningHours, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <div class="col-6 col-lg-3 mt-4 mt-lg-0">
                        <img src="image/contact_card4.jpg" alt="">
                        <h4>Our shop</h4>
                        <img src="image/contactus_design.jpg" alt="">
                        <p class="mb-0">
                            We welcome you to visit our shop and experience our fresh meat quality.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Closing message -->
    <section>
        <div class="container-fluid bg-footerimg py-4 px-0">
            <div class="container text-center py-4">
                <h5 class="fst-italic text-white">Fresh meat. Clean meat. Safe meat.</h5>
                <h3 class="text-light fw-bold playfair-display">Thank you for supporting us!</h3>
            </div>
        </div>
    </section>
</main>

<footer class="container-fluid bg-color text-white text-center fw-light">
    <p class="small p-3 mb-0">
        &copy; <span id="currentYear"></span> GV Broilers. All rights reserved.
    </p>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("currentYear").textContent = new Date().getFullYear();
</script>
</body>
</html>
