<?php require_once __DIR__ . '/includes/bootstrap.php';
require_login(); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Change password</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <main class="page">
        <section class="card narrow"><a href="dashboard.php">← Dashboard</a>
            <h1>Change password</h1>
            <div id="notice" class="notice"></div>
            <form id="changeForm"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><label>Current password<input name="current_password" type="password" required></label><label>New password<input name="new_password" type="password" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}" title="At least 8 characters, one uppercase, one lowercase, and one special character." required></label>
                <p class="small password-rule">Use 8+ characters, one uppercase, one lowercase, and one special character.</p><label>Confirm password<input name="confirm_password" type="password" minlength="8" required></label><button class="btn primary full">Update password</button>
            </form>
        </section>
    </main>
    <script src="assets/app.js"></script>
</body>

</html>