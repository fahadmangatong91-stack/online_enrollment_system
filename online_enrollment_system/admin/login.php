<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isset($_SESSION['admin_logged_in'])) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    set_old(['email' => $email]);

    $adminEmail = 'admin@school.com';
    $adminPassword = 'admin123';

    if ($email === $adminEmail && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        clear_old();
        set_flash('success', 'Welcome to the admin portal.');
        redirect('dashboard.php');
    }

    set_flash('error', 'Invalid administrator credentials.');
    redirect('login.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-panel">
                <span class="eyebrow">Administrator Access</span>
                <h1>Enrollment review portal</h1>
                <p class="auth-copy">Review student submissions, manage subjects, and monitor enrollment counts from one dashboard.</p>
            </div>

            <div>
                <span class="eyebrow">Admin Login</span>
                <h1>Sign in</h1>
                <p class="auth-copy">Use the configured administrator credentials for this project.</p>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endif; ?>

                <form class="auth-form" method="POST">
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="<?= old('email') ?>" required>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <button type="submit">Open Admin Dashboard</button>
                </form>

                <p class="muted">Student user? <a class="link-button" href="../student/login.php">Go to student login</a></p>
            </div>
        </section>
    </main>
</body>
</html>
<?php clear_old(); ?>
