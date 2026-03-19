<?php
require_once __DIR__ . '/includes/bootstrap.php';

$flash = get_flash();
clear_old();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Enrollment System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="portal-body">
    <main class="portal-shell">
        <section class="hero-card">
            <div class="hero-copy">
                <span class="eyebrow">Enrollment Platform</span>
                <h1>Online Enrollment System</h1>
                <p class="lead">A cleaner, faster way for students to register, submit requirements, choose subjects, and track their enrollment status.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="student/login.php">Student Login</a>
                    <a class="btn btn-secondary" href="student/register.php">Create Account</a>
                    <a class="btn btn-ghost" href="admin/login.php">Admin Portal</a>
                </div>
            </div>
            <div class="hero-panel">
                <div class="hero-stat">
                    <span>Student self-service</span>
                    <strong>Register, enroll, track</strong>
                </div>
                <div class="hero-stat">
                    <span>Admin review</span>
                    <strong>Approve, reject, manage subjects</strong>
                </div>
                <div class="hero-stat">
                    <span>Built for clarity</span>
                    <strong>Responsive UI across student and admin flows</strong>
                </div>
            </div>
        </section>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> centered-alert"><?= e($flash['message']) ?></div>
        <?php endif; ?>
    </main>
</body>
</html>
