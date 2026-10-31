<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (!$con) { http_response_code(500); exit('Database connection failed.'); }
mysqli_set_charset($con, 'utf8mb4');
$countResult = mysqli_query($con, 'SELECT COUNT(*) AS total FROM admins');
$adminCount = $countResult ? (int) mysqli_fetch_assoc($countResult)['total'] : 1;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminCount === 0) {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (strlen($username) < 3 || strlen($username) > 50 || preg_match('/[\x00-\x1F\x7F]/', $username)) $error = 'Use a username between 3 and 50 characters.';
    elseif (strlen($password) < 12) $error = 'Choose a password with at least 12 characters.';
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($con, 'INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $username, $hash);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['admin_id'] = mysqli_insert_id($con);
            $_SESSION['admin_username'] = $username;
            header('Location: admin-orders-list.php'); exit;
        }
        $error = 'Could not create the admin. The username may already exist.';
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Setup | GV Broilers</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="bg-light"><main class="container py-5" style="max-width:500px"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h3">Create first admin</h1>
<?php if ($adminCount > 0) { ?><div class="alert alert-info">An admin account already exists. <a href="admin-login.php">Sign in</a>.</div><?php } else { ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
<form method="post"><label class="form-label" for="username">Username</label><input class="form-control mb-3" id="username" name="username" required minlength="3" maxlength="50"><label class="form-label" for="password">Password (12+ characters)</label><input class="form-control mb-3" id="password" name="password" type="password" required minlength="12" autocomplete="new-password"><button class="btn btn-success w-100">Create admin account</button></form><?php } ?>
</div></div><p class="mt-3"><a href="home.php">Return to website</a></p></main></body></html>
