<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();
$flash = get_flash();
$currentPage = basename(__FILE__);

$student = fetch_student($conn, $auth['id']);
$latestEnrollment = fetch_latest_enrollment($conn, $auth['id']);
$subjects = $conn->query('SELECT subject_id, subject_name, units FROM subjects ORDER BY subject_name ASC')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Now</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Enrollment Form</span>
                <h1>Submit your enrollment</h1>
                <p class="page-subtitle">Review your profile, complete your contact details, choose your subjects, and upload your requirements.</p>
            </div>
        </header>

        <nav class="app-nav">
            <?= nav_link('dashboard.php', 'Dashboard', $currentPage) ?>
            <?= nav_link('enroll.php', 'Enroll Now', $currentPage) ?>
            <?= nav_link('my_subjects.php', 'My Subjects', $currentPage) ?>
            <?= nav_link('status.php', 'Status', $currentPage) ?>
            <?= nav_link('profile.php', 'Profile', $currentPage) ?>
            <?= nav_link('enrollment_summary.php', 'Summary', $currentPage) ?>
            <?= nav_link('logout.php', 'Logout', $currentPage) ?>
        </nav>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if ($latestEnrollment && $latestEnrollment['status'] === 'Pending'): ?>
            <div class="alert alert-warning">You already have a pending enrollment on file. Submitting again will create a new record for review.</div>
        <?php endif; ?>

        <section class="form-card">
            <div class="detail-list" style="margin-bottom: 22px;">
                <div class="detail-item">
                    <span>Student</span>
                    <strong><?= e($student['fullname'] ?? '') ?></strong>
                </div>
                <div class="detail-item">
                    <span>Program</span>
                    <strong><?= e($student['course'] ?? '') ?></strong>
                </div>
            </div>

            <form class="auth-form" action="enroll_process.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="field">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?= old('address') ?></textarea>
                    </div>

                    <div class="field">
                        <label for="previous_school">Previous School</label>
                        <input id="previous_school" type="text" name="previous_school" value="<?= old('previous_school') ?>" required>
                    </div>

                    <div class="field">
                        <label for="contact_number">Contact Number</label>
                        <input id="contact_number" type="text" name="contact_number" value="<?= old('contact_number') ?>" required>
                    </div>

                    <div class="field">
                        <label for="course">Course</label>
                        <select id="course" name="course" required>
                            <option value="">Select course</option>
                            <option value="BSCS" <?= old('course', $student['course'] ?? '') === 'BSCS' ? 'selected' : '' ?>>BS Computer Science</option>
                            <option value="BSECE" <?= old('course', $student['course'] ?? '') === 'BSECE' ? 'selected' : '' ?>>BS Electronics Engineering</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="birth_certificate">Birth Certificate</label>
                        <input id="birth_certificate" type="file" name="birth_certificate" accept=".jpg,.jpeg,.png,.pdf" required>
                        <span class="helper-text">Accepted formats: JPG, PNG, PDF</span>
                    </div>

                    <div class="field">
                        <label for="form_138">Form 138</label>
                        <input id="form_138" type="file" name="form_138" accept=".jpg,.jpeg,.png,.pdf" required>
                        <span class="helper-text">Accepted formats: JPG, PNG, PDF</span>
                    </div>
                </div>

                <div class="field-full">
                    <label>Select Subjects</label>
                    <?php if ($subjects): ?>
                        <div class="subject-grid">
                            <?php
                            $selectedSubjects = $_SESSION['old']['subjects'] ?? [];
                            foreach ($subjects as $subject):
                                $checked = in_array((string) $subject['subject_id'], array_map('strval', $selectedSubjects), true) ? 'checked' : '';
                            ?>
                                <label class="subject-pill">
                                    <input type="checkbox" name="subjects[]" value="<?= e((string) $subject['subject_id']) ?>" <?= $checked ?>>
                                    <strong><?= e($subject['subject_name']) ?></strong>
                                    <span><?= e((string) $subject['units']) ?> units</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>No subjects available yet</h3>
                            <p class="muted">An administrator needs to add subjects before students can enroll.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit">Submit Enrollment</button>
            </form>
        </section>
    </div>
</body>
</html>
<?php clear_old(); ?>
