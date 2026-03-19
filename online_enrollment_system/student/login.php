<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isset($_SESSION['student_id'])) {
    redirect('dashboard.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-panel">
                <span class="eyebrow">Student Access</span>
                <h1>Welcome back</h1>
                <p class="auth-copy">Sign in to submit your enrollment, review selected subjects, print your summary, and keep track of approval updates.</p>
                <div class="hero-panel">
                    <div class="hero-stat">
                        <span>Enrollment</span>
                        <strong>Submit requirements and subject load in one place</strong>
                    </div>
                    <div class="hero-stat">
                        <span>Status</span>
                        <strong>See the latest review result anytime</strong>
                    </div>
                </div>
            </div>

            <div>
                <span class="eyebrow">Student Login</span>
                <h1>Sign in</h1>
                <p class="auth-copy">Use the email and password from your student account.</p>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endif; ?>

                <form class="auth-form" action="login_process.php" method="POST">
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="<?= old('email') ?>" required>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <button type="submit">Login to Dashboard</button>
                </form>

                <p class="muted">No account yet? <a class="link-button" href="register.php">Create your student account</a></p>
                <p class="muted">Administrator? <a class="link-button" href="../admin/login.php">Open admin portal</a></p>
            </div>
        </section>
    </main>
</body>
</html>
<?php clear_old(); ?>
