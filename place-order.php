<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!$con) {
    http_response_code(500);
    exit('Database connection failed.');
}
mysqli_set_charset($con, 'utf8mb4');

if (empty($_SESSION['order_csrf'])) {
    $_SESSION['order_csrf'] = bin2hex(random_bytes(32));
}

$products = array();
$quantityOptions = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10');
$result = mysqli_query($con, "SELECT id, name, price FROM products WHERE LOWER(status) = 'available' ORDER BY name");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

$error = '';
$success = isset($_GET['success']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(isset($_POST['customer_name']) ? $_POST['customer_name'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $address = trim(isset($_POST['address']) ? $_POST['address'] : '');
    $notes = trim(isset($_POST['notes']) ? $_POST['notes'] : '');
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    $quantities = isset($_POST['quantity']) && is_array($_POST['quantity']) ? $_POST['quantity'] : array();

    if (!hash_equals($_SESSION['order_csrf'], $token)) {
        $error = 'Your session expired. Please refresh and try again.';
    } elseif ($name === '' || $phone === '') {
        $error = 'Please enter your name and phone number.';
    } else {
        $orderItems = array();
        $total = 0;
        $stmt = mysqli_prepare($con, "SELECT name, price FROM products WHERE id = ? AND LOWER(status) = 'available'");
        foreach ($quantities as $productId => $quantityValue) {
            if (!in_array((string) $quantityValue, $quantityOptions, true)) continue;
            $quantity = filter_var($quantityValue, FILTER_VALIDATE_FLOAT);
            if (!$quantity || $quantity <= 0) continue;
            $productId = filter_var($productId, FILTER_VALIDATE_INT);
            if (!$productId) continue;
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            $productResult = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($productResult);
            if (!$product || !is_numeric($product['price'])) continue;
            $unitPrice = (float) $product['price'];
            $lineTotal = round($unitPrice * $quantity, 2);
            $total += $lineTotal;
            $orderItems[] = array($productId, $product['name'], $quantity, $unitPrice, $lineTotal);
        }
        mysqli_stmt_close($stmt);

        if (!$orderItems) {
            $error = 'Choose a quantity for at least one available product.';
        } elseif (mysqli_begin_transaction($con)) {
            $orderStmt = mysqli_prepare($con, "INSERT INTO orders (customer_name, phone, address, notes, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            mysqli_stmt_bind_param($orderStmt, 'ssssd', $name, $phone, $address, $notes, $total);
            if (mysqli_stmt_execute($orderStmt)) {
                $orderId = mysqli_insert_id($con);
                $itemStmt = mysqli_prepare($con, 'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?)');
                $inserted = true;
                foreach ($orderItems as $item) {
                    list($productId, $productName, $quantity, $unitPrice, $lineTotal) = $item;
                    mysqli_stmt_bind_param($itemStmt, 'iisddd', $orderId, $productId, $productName, $quantity, $unitPrice, $lineTotal);
                    if (!mysqli_stmt_execute($itemStmt)) { $inserted = false; break; }
                }
                mysqli_stmt_close($itemStmt);
                mysqli_stmt_close($orderStmt);
                if ($inserted) {
                    mysqli_commit($con);
                    header('Location: place-order.php?success=1');
                    exit;
                }
            }
            if (isset($orderStmt)) mysqli_stmt_close($orderStmt);
            mysqli_rollback($con);
            $error = 'Could not save your order. Please try again or contact us.';
        } else {
            $error = 'Could not start the order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Place an Order | GV Broilers</title><link rel="stylesheet" href="css/bootstrap.min.css"><link rel="stylesheet" href="css/style.css"></head>
<body class="bg-light"><main class="container py-5" style="max-width:800px"><a href="products.php" class="text-success">&larr; Back to products</a><div class="card shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 text-success">Place your order</h1>
<?php if ($success) { ?><div class="alert alert-success">Thanks! Your order has been received. We’ll contact you to confirm it.</div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
<?php if (!$products) { ?><div class="alert alert-warning">No available products were found.</div><?php } else { ?>
<form method="post"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['order_csrf'], ENT_QUOTES, 'UTF-8'); ?>"><div class="row g-3"><div class="col-md-6"><label class="form-label">Your name *</label><input class="form-control" name="customer_name" required maxlength="150"></div><div class="col-md-6"><label class="form-label">Phone number *</label><input class="form-control" name="phone" required maxlength="20" type="tel"></div><div class="col-12"><label class="form-label">Delivery address</label><textarea class="form-control" name="address" rows="2"></textarea></div><div class="col-12"><h2 class="h5">Products and quantities</h2><?php foreach ($products as $product) { ?><div class="row align-items-center border-bottom py-2"><label class="col" for="qty-<?php echo (int) $product['id']; ?>"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?> — ₹<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?></label><div class="col-auto"><select id="qty-<?php echo (int) $product['id']; ?>" class="form-select" style="width:125px" name="quantity[<?php echo (int) $product['id']; ?>]" aria-label="Quantity for <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"><?php foreach ($quantityOptions as $quantityOption) { ?><option value="<?php echo $quantityOption; ?>"><?php echo $quantityOption === '0' ? '0 - None' : $quantityOption; ?></option><?php } ?></select></div></div><?php } ?></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2" maxlength="2000"></textarea></div><div class="col-12"><button class="btn btn-success rounded-pill px-4" type="submit">Submit Order</button></div></div></form><?php } ?></div></div></main></body></html>
