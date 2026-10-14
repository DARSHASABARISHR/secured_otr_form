<?php require_once __DIR__ . '/includes/bootstrap.php';
$reg = trim((string)($_GET['reg'] ?? '')); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Success</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <main class="page">
        <section class="card center">
            <h1>Registration successful!</h1>
            <p>Thank you for registering.</p>
            <p>Your registration number:</p>
            <div class="regno"><?= h($reg) ?></div><a class="btn primary" href="login.php">Go to login</a>
        </section>
    </main>
</body>

</html>