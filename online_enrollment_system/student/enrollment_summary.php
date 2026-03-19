<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();
$currentPage = basename(__FILE__);

$student = fetch_student($conn, $auth['id']);
$enrollment = fetch_latest_enrollment($conn, $auth['id']);

if (!$enrollment) {
    set_flash('warning', 'No enrollment is available to print yet.');
    redirect('status.php');
}

$subjects = fetch_enrollment_subjects($conn, (int) $enrollment['enrollment_id']);
$totalUnits = 0;
foreach ($subjects as $subject) {
    $totalUnits += (int) $subject['units'];
}
$badgeClass = $enrollment['status'] === 'Approved' ? 'badge-approved' : ($enrollment['status'] === 'Rejected' ? 'badge-rejected' : 'badge-pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Summary</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <nav class="app-nav no-print">
            <?= nav_link('dashboard.php', 'Dashboard', $currentPage) ?>
            <?= nav_link('enroll.php', 'Enroll Now', $currentPage) ?>
            <?= nav_link('my_subjects.php', 'My Subjects', $currentPage) ?>
            <?= nav_link('status.php', 'Status', $currentPage) ?>
            <?= nav_link('profile.php', 'Profile', $currentPage) ?>
            <?= nav_link('enrollment_summary.php', 'Summary', $currentPage) ?>
            <?= nav_link('logout.php', 'Logout', $currentPage) ?>
        </nav>

        <section class="summary-card">
            <header class="page-head">
                <div class="page-title">
                    <span class="eyebrow">Printable Copy</span>
                    <h1>Enrollment summary</h1>
                    <p class="page-subtitle">A clean summary of your latest enrollment record, ready to print.</p>
                </div>
                <div class="toolbar no-print">
                    <button type="button" onclick="window.print()">Print Summary</button>
                </div>
            </header>

            <section class="summary-list">
                <div class="summary-item">
                    <span>Name</span>
                    <strong><?= e($student['fullname'] ?? '') ?></strong>
                </div>
                <div class="summary-item">
                    <span>Student ID</span>
                    <strong><?= e($student['student_id'] ?? '') ?></strong>
                </div>
                <div class="summary-item">
                    <span>Email</span>
                    <strong><?= e($student['email'] ?? '') ?></strong>
                </div>
                <div class="summary-item">
                    <span>Course</span>
                    <strong><?= e($enrollment['course']) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Year Level</span>
                    <strong><?= e($student['year_level'] ?? '') ?></strong>
                </div>
                <div class="summary-item">
                    <span>Status</span>
                    <strong><span class="badge <?= $badgeClass ?>"><?= e($enrollment['status']) ?></span></strong>
                </div>
                <div class="summary-item">
                    <span>Address</span>
                    <strong><?= e($enrollment['address']) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Contact Number</span>
                    <strong><?= e($enrollment['contact_number']) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Previous School</span>
                    <strong><?= e($enrollment['previous_school']) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Submitted On</span>
                    <strong><?= e($enrollment['date']) ?></strong>
                </div>
            </section>

            <h2 class="section-title">Selected subjects</h2>
            <?php if ($subjects): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?= e($subject['subject_name']) ?></td>
                                <td><?= e((string) $subject['units']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <th>Total Units</th>
                            <th><?= $totalUnits ?></th>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p class="muted">No subjects were attached to this enrollment.</p>
                </div>
            <?php endif; ?>

            <p class="meta print-note">Requirements on file: Birth Certificate and Form 138 uploaded during submission.</p>
        </section>
    </div>
</body>
</html>
