<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (isset($_SESSION['admin_id'])) { header('Location: admin-orders-list.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $stmt = mysqli_prepare($con, 'SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: admin-orders-list.php'); exit;
    }
    $error = 'Incorrect username or password.';
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login | GV Broilers</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="bg-light"><main class="container py-5" style="max-width:500px"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h3">Admin login</h1><?php if ($error !== '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?><form method="post"><label class="form-label" for="username">Username</label><input class="form-control mb-3" id="username" name="username" required autocomplete="username"><label class="form-label" for="password">Password</label><input class="form-control mb-3" id="password" name="password" type="password" required autocomplete="current-password"><button class="btn btn-success w-100">Sign in</button></form></div></div><p class="mt-3"><a href="home.php">Return to website</a></p></main></body></html>
