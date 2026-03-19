<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();
$currentPage = basename(__FILE__);

$latestEnrollment = fetch_latest_enrollment($conn, $auth['id']);
$subjects = $latestEnrollment ? fetch_enrollment_subjects($conn, (int) $latestEnrollment['enrollment_id']) : [];
$totalUnits = 0;
foreach ($subjects as $subject) {
    $totalUnits += (int) $subject['units'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Subject Load</span>
                <h1>My selected subjects</h1>
                <p class="page-subtitle">This table reflects the subject list attached to your latest enrollment.</p>
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

        <?php if ($subjects): ?>
            <section class="table-card">
                <div class="status-banner">
                    <div>
                        <span class="kicker">Current Load</span>
                        <h3><?= count($subjects) ?> subject<?= count($subjects) === 1 ? '' : 's' ?></h3>
                    </div>
                    <div><span class="badge badge-approved"><?= $totalUnits ?> total units</span></div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Subject Name</th>
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
                    </tbody>
                </table>
            </section>
        <?php else: ?>
            <section class="empty-state">
                <h2>No subjects enrolled yet</h2>
                <p class="muted">Submit an enrollment first to generate your subject list.</p>
                <a class="btn btn-primary" href="enroll.php">Enroll Now</a>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>
