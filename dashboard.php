<?php require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$q = db()->prepare('SELECT registration_number,name,email,phone FROM users WHERE id=?');
$q->execute([$_SESSION['user_id']]);
$u = $q->fetch();
if (!$u) {
    header('Location: logout.php');
    exit;
} ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <main class="page">
        <section class="card">
            <h1>Welcome, <?= h($u['name']) ?></h1>
            <p>Registration number: <b><?= h($u['registration_number']) ?></b></p>
            <p>Email: <?= h($u['email']) ?></p>
            <p>Phone: <?= h($u['phone']) ?></p><a class="btn secondary" href="change_password.php">Change password</a> <a class="btn primary" href="logout.php">Logout</a>
        </section>
    </main>
</body>

</html>