<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: admin-login.php'); exit; }
if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
$statuses = array('pending', 'confirmed', 'preparing', 'delivered', 'cancelled');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    $orderId = filter_var(isset($_POST['order_id']) ? $_POST['order_id'] : null, FILTER_VALIDATE_INT);
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    if (hash_equals($_SESSION['admin_csrf'], $token) && $orderId && in_array($status, $statuses, true)) {
        $update = mysqli_prepare($con, 'UPDATE orders SET status = ? WHERE id = ?');
        mysqli_stmt_bind_param($update, 'si', $status, $orderId);
        if (mysqli_stmt_execute($update)) $message = 'Order status updated.';
        mysqli_stmt_close($update);
    } else $message = 'Could not update order status.';
}
$orders = mysqli_query($con, 'SELECT id, customer_name, phone, total_amount, status, created_at FROM orders ORDER BY created_at DESC, id DESC');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Orders List | GV Broilers Admin</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="bg-light">
<nav class="navbar bg-success navbar-dark"><div class="container"><a class="navbar-brand" href="admin.php">GV Broilers Admin</a><a class="btn btn-outline-light btn-sm" href="admin-logout.php">Log out</a></div></nav>
<main class="container py-4"><div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Orders List</h1><a class="btn btn-outline-success" href="admin.php">Back to order cards</a></div><?php if ($message !== '') { ?><div class="alert alert-info"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
<?php if (!$orders) { ?><div class="alert alert-danger">Could not load the orders.</div><?php } elseif (mysqli_num_rows($orders) === 0) { ?><div class="alert alert-info">There are no orders yet.</div><?php } else { ?>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-striped table-hover align-middle mb-0"><thead class="table-success"><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php while ($order = mysqli_fetch_assoc($orders)) { ?><tr><td><?php echo (int) $order['id']; ?></td><td><?php echo htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td><td><a href="tel:<?php echo htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($order['phone'], ENT_QUOTES, 'UTF-8'); ?></a></td><td><?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></td><td>INR <?php echo htmlspecialchars($order['total_amount'], ENT_QUOTES, 'UTF-8'); ?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>"><select class="form-select form-select-sm" name="status" aria-label="Status for order <?php echo (int) $order['id']; ?>"><?php foreach ($statuses as $status) { ?><option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option><?php } ?></select><button class="btn btn-sm btn-success">Save</button></form></td><td><div class="d-flex gap-2"><a class="btn btn-sm btn-success" href="admin-order-details.php?id=<?php echo (int) $order['id']; ?>">Details</a><a class="btn btn-sm btn-outline-success" href="invoice.php?id=<?php echo (int) $order['id']; ?>">Invoice</a></div></td></tr><?php } ?>
</tbody></table></div></div><?php } ?></main></body></html>
