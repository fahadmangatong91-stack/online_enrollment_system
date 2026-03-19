<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();
$flash = get_flash();
$currentPage = basename(__FILE__);

$latestEnrollment = fetch_latest_enrollment($conn, $auth['id']);
$subjects = $latestEnrollment ? fetch_enrollment_subjects($conn, (int) $latestEnrollment['enrollment_id']) : [];
$badgeClass = 'badge-pending';

if ($latestEnrollment) {
    $badgeClass = $latestEnrollment['status'] === 'Approved' ? 'badge-approved' : ($latestEnrollment['status'] === 'Rejected' ? 'badge-rejected' : 'badge-pending');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Status</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Enrollment Status</span>
                <h1>Latest application status</h1>
                <p class="page-subtitle">Review the most recent enrollment you submitted, including selected subjects and document status.</p>
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

        <?php if ($latestEnrollment): ?>
            <section class="status-banner">
                <div>
                    <span class="kicker">Review Status</span>
                    <h3><?= e($latestEnrollment['course']) ?></h3>
                    <p class="muted">Submitted on <?= e($latestEnrollment['date']) ?></p>
                </div>
                <div class="status-actions">
                    <span class="badge <?= $badgeClass ?>"><?= e($latestEnrollment['status']) ?></span>
                    <a class="btn btn-secondary btn-sm" href="enrollment_summary.php">Print Summary</a>
                </div>
            </section>

            <section class="summary-card">
                <div class="summary-list">
                    <div class="summary-item">
                        <span>Address</span>
                        <strong><?= e($latestEnrollment['address']) ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Contact Number</span>
                        <strong><?= e($latestEnrollment['contact_number']) ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Previous School</span>
                        <strong><?= e($latestEnrollment['previous_school']) ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Documents</span>
                        <strong><?= e($latestEnrollment['birth_certificate'] ? 'Uploaded requirements on file' : 'No documents saved') ?></strong>
                    </div>
                </div>

                <h2 class="section-title">Selected subjects</h2>
                <?php if ($subjects): ?>
                    <div class="subject-grid">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="subject-pill">
                                <strong><?= e($subject['subject_name']) ?></strong>
                                <span><?= e((string) $subject['units']) ?> units</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p class="muted">No subjects were saved for this enrollment.</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <section class="empty-state">
                <h2>No enrollment found</h2>
                <p class="muted">You have not submitted an enrollment yet.</p>
                <a class="btn btn-primary" href="enroll.php">Start Enrollment</a>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>
