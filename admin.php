<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }
if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
$message = '';
$statuses = array('pending', 'confirmed', 'preparing', 'delivered', 'cancelled');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    $orderId = filter_var(isset($_POST['order_id']) ? $_POST['order_id'] : null, FILTER_VALIDATE_INT);
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    if (hash_equals($_SESSION['admin_csrf'], $token) && $orderId && in_array($status, $statuses, true)) {
        $stmt = mysqli_prepare($con, 'UPDATE orders SET status = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);
        if (mysqli_stmt_execute($stmt)) $message = 'Order status updated.';
        mysqli_stmt_close($stmt);
    } else $message = 'Could not update the order status.';
}
$orders = mysqli_query($con, 'SELECT id, customer_name, phone, address, notes, total_amount, status, created_at FROM orders ORDER BY created_at DESC, id DESC');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Orders | GV Broilers Admin</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="bg-light"><nav class="navbar bg-success navbar-dark"><div class="container"><a class="navbar-brand" href="admin.php">GV Broilers Admin</a><span class="text-white ms-auto me-3"><?php echo htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8'); ?></span><a class="btn btn-outline-light btn-sm" href="admin-logout.php">Log out</a></div></nav>
<main class="container py-4"><div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Customer orders</h1><a class="btn btn-outline-success" href="admin-orders-list.php">Orders List</a></div><?php if ($message !== '') { ?><div class="alert alert-info"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
<?php if (!$orders) { ?><div class="alert alert-danger">Could not load orders. Check that the orders table has the expected columns.</div><?php } elseif (mysqli_num_rows($orders) === 0) { ?><div class="alert alert-info">There are no orders yet.</div><?php } else { while ($order = mysqli_fetch_assoc($orders)) { ?>
<section class="card shadow-sm mb-3"><div class="card-body"><div class="d-flex flex-wrap justify-content-between gap-2"><h2 class="h5 mb-0">Order #<?php echo (int) $order['id']; ?> - <?php echo htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></h2><strong>INR <?php echo htmlspecialchars($order['total_amount'], ENT_QUOTES, 'UTF-8'); ?></strong></div><p class="mb-1 mt-2">Phone: <a href="tel:<?php echo htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8'); ?></a> | <?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></p><?php if ($order['address']) { ?><p class="mb-1">Address: <?php echo nl2br(htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8')); ?></p><?php } if ($order['notes']) { ?><p class="mb-2">Notes: <?php echo nl2br(htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8')); ?></p><?php } ?>
<?php $itemStmt = mysqli_prepare($con, 'SELECT product_name, quantity, unit_price, line_total FROM order_items WHERE order_id = ?'); mysqli_stmt_bind_param($itemStmt, 'i', $order['id']); mysqli_stmt_execute($itemStmt); $items = mysqli_stmt_get_result($itemStmt); ?>
<ul class="mb-3"><?php while ($item = mysqli_fetch_assoc($items)) { ?><li><?php echo htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?> x INR <?php echo htmlspecialchars($item['unit_price'], ENT_QUOTES, 'UTF-8'); ?> = INR <?php echo htmlspecialchars($item['line_total'], ENT_QUOTES, 'UTF-8'); ?></li><?php } ?></ul><?php mysqli_stmt_close($itemStmt); ?>
<div class="d-flex flex-wrap gap-2 align-items-center"><form method="post" class="d-flex gap-2"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>"><select class="form-select" style="max-width:200px" name="status"><?php foreach ($statuses as $status) { ?><option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option><?php } ?></select><button class="btn btn-success">Update status</button></form><a class="btn btn-outline-success" href="invoice.php?id=<?php echo (int) $order['id']; ?>">Invoice</a></div></div></section>
<?php } } ?></main></body></html>
