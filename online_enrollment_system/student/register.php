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
    <title>Student Registration</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-panel">
                <span class="eyebrow">Create Account</span>
                <h1>Start your enrollment journey</h1>
                <p class="auth-copy">Create your student account first. Once registered, you can log in, choose subjects, upload requirements, and print your enrollment summary.</p>
            </div>

            <div>
                <span class="eyebrow">Registration Form</span>
                <h1>Student registration</h1>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endif; ?>

                <form class="auth-form" action="register_process.php" method="POST">
                    <div class="form-grid">
                        <div class="field">
                            <label for="fullname">Full Name</label>
                            <input id="fullname" type="text" name="fullname" value="<?= old('fullname') ?>" required>
                        </div>

                        <div class="field">
                            <label for="student_id">Student ID</label>
                            <input id="student_id" type="text" name="student_id" value="<?= old('student_id') ?>" required>
                        </div>

                        <div class="field">
                            <label for="email">Email Address</label>
                            <input id="email" type="email" name="email" value="<?= old('email') ?>" required>
                        </div>

                        <div class="field">
                            <label for="course">Course</label>
                            <select id="course" name="course" required>
                                <option value="">Select course</option>
                                <option value="BSCS" <?= old('course') === 'BSCS' ? 'selected' : '' ?>>BS Computer Science</option>
                                <option value="BSECE" <?= old('course') === 'BSECE' ? 'selected' : '' ?>>BS Electronics Engineering</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="year_level">Year Level</label>
                            <select id="year_level" name="year_level" required>
                                <option value="">Select year</option>
                                <?php foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year): ?>
                                    <option value="<?= e($year) ?>" <?= old('year_level') === $year ? 'selected' : '' ?>><?= e($year) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" minlength="8" required>
                        </div>
                    </div>

                    <p class="helper-text">Use at least 8 characters for your password.</p>
                    <button type="submit">Create Account</button>
                </form>

                <p class="muted">Already registered? <a class="link-button" href="login.php">Sign in here</a></p>
            </div>
        </section>
    </main>
</body>
</html>
<?php clear_old(); ?>
