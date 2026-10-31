<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }
$orderId = filter_var(isset($_GET['id']) ? $_GET['id'] : null, FILTER_VALIDATE_INT);
if (!$orderId) { http_response_code(400); exit('Invalid invoice number.'); }
$stmt = mysqli_prepare($con, 'SELECT id, customer_name, phone, address, notes, total_amount, status, created_at FROM orders WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $orderId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$order) { http_response_code(404); exit('Order not found.'); }
$itemStmt = mysqli_prepare($con, 'SELECT product_name, quantity, unit_price, line_total FROM order_items WHERE order_id = ?');
mysqli_stmt_bind_param($itemStmt, 'i', $orderId);
mysqli_stmt_execute($itemStmt);
$items = mysqli_stmt_get_result($itemStmt);
$profileResult = mysqli_query($con, 'SELECT companyname, mobile, email, address FROM profile1 LIMIT 1');
$profile = $profileResult ? mysqli_fetch_assoc($profileResult) : array();
$companyName = !empty($profile['companyname']) ? $profile['companyname'] : 'GV Broilers';
function invoiceEscape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Invoice #<?php echo (int) $order['id']; ?> | <?php echo invoiceEscape($companyName); ?></title><link rel="stylesheet" href="css/bootstrap.min.css"><style>body{background:#f3f5f4}.invoice{max-width:900px;margin:32px auto;padding:40px;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.08)}@media print{body{background:#fff}.invoice{max-width:none;margin:0;padding:0;box-shadow:none}.no-print{display:none!important}}</style></head>
<body><div class="container no-print pt-3 d-flex gap-2"><a class="btn btn-outline-secondary" href="admin-orders-list.php">Back to orders</a><button class="btn btn-success" onclick="window.print()">Print / Save as PDF</button></div><main class="invoice">
<div class="d-flex justify-content-between border-bottom pb-3 mb-4"><div><h1 class="h2 text-success mb-1"><?php echo invoiceEscape($companyName); ?></h1><?php if (!empty($profile['address'])) { ?><div><?php echo nl2br(invoiceEscape($profile['address'])); ?></div><?php } ?><?php if (!empty($profile['mobile'])) { ?><div>Phone: <?php echo invoiceEscape($profile['mobile']); ?></div><?php } ?><?php if (!empty($profile['email'])) { ?><div><?php echo invoiceEscape($profile['email']); ?></div><?php } ?></div><div class="text-end"><h2 class="h3">INVOICE</h2><div><strong>Invoice #:</strong> <?php echo (int) $order['id']; ?></div><div><strong>Date:</strong> <?php echo invoiceEscape($order['created_at']); ?></div><div><strong>Status:</strong> <?php echo invoiceEscape(ucfirst($order['status'])); ?></div></div></div>
<h3 class="h6 text-uppercase text-muted">Bill to</h3><p><strong><?php echo invoiceEscape($order['customer_name']); ?></strong><br>Phone: <?php echo invoiceEscape($order['phone']); ?><?php if (!empty($order['address'])) { ?><br><?php echo nl2br(invoiceEscape($order['address'])); ?><?php } ?></p><?php if (!empty($order['notes'])) { ?><p><strong>Order notes:</strong> <?php echo nl2br(invoiceEscape($order['notes'])); ?></p><?php } ?>
<div class="table-responsive"><table class="table table-bordered"><thead class="table-success"><tr><th>Item</th><th class="text-end">Quantity</th><th class="text-end">Unit price</th><th class="text-end">Amount</th></tr></thead><tbody><?php while ($item = mysqli_fetch_assoc($items)) { ?><tr><td><?php echo invoiceEscape($item['product_name']); ?></td><td class="text-end"><?php echo invoiceEscape($item['quantity']); ?></td><td class="text-end">INR <?php echo invoiceEscape($item['unit_price']); ?></td><td class="text-end">INR <?php echo invoiceEscape($item['line_total']); ?></td></tr><?php } ?></tbody><tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end">INR <?php echo invoiceEscape($order['total_amount']); ?></th></tr></tfoot></table></div><p class="text-center text-muted mt-5">Thank you for ordering from <?php echo invoiceEscape($companyName); ?>.</p>
</main></body></html>
