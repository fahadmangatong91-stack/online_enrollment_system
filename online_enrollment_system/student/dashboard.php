<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();
$flash = get_flash();
$currentPage = basename(__FILE__);

$student = fetch_student($conn, $auth['id']);
$latestEnrollment = fetch_latest_enrollment($conn, $auth['id']);

$statusCounts = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
$stmt = $conn->prepare('SELECT status, COUNT(*) AS total FROM enrollments WHERE student_id = ? GROUP BY status');
$stmt->bind_param('i', $auth['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $statusCounts[$row['status']] = (int) $row['total'];
}
$stmt->close();

$chartLabels = [];
$chartData = [];
$stmt = $conn->prepare(
    'SELECT e.enrollment_id, COUNT(es.subject_id) AS total_subjects
     FROM enrollments e
     LEFT JOIN enrollment_subjects es ON es.enrollment_id = e.enrollment_id
     WHERE e.student_id = ?
     GROUP BY e.enrollment_id
     ORDER BY e.date ASC, e.enrollment_id ASC'
);
$stmt->bind_param('i', $auth['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chartLabels[] = 'Enrollment #' . $row['enrollment_id'];
    $chartData[] = (int) $row['total_subjects'];
}
$stmt->close();

$statusClass = 'badge-pending';
$statusText = 'No enrollment submitted yet';
if ($latestEnrollment) {
    $statusText = $latestEnrollment['status'];
    $statusClass = $latestEnrollment['status'] === 'Approved' ? 'badge-approved' : ($latestEnrollment['status'] === 'Rejected' ? 'badge-rejected' : 'badge-pending');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Student Dashboard</span>
                <h1><?= e($student['fullname'] ?? $auth['fullname']) ?></h1>
                <p class="page-subtitle">Track your enrollment activity, current review status, and subject load from one place.</p>
            </div>
            <div class="toolbar">
                <a class="btn btn-secondary" href="enroll.php">Submit Enrollment</a>
                <a class="btn btn-ghost" href="../index.php">Home</a>
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

        <section class="status-banner">
            <div>
                <span class="kicker">Current Status</span>
                <h3><?= e($latestEnrollment ? 'Latest enrollment review' : 'No active enrollment yet') ?></h3>
                <p class="muted"><?= e($latestEnrollment ? 'Last submitted course: ' . $latestEnrollment['course'] : 'Start by completing your enrollment form.') ?></p>
            </div>
            <div>
                <span class="badge <?= $statusClass ?>"><?= e($statusText) ?></span>
            </div>
        </section>

        <section class="stats-grid">
            <article class="stats-card accent-primary">
                <span class="label">Pending</span>
                <strong class="value"><?= $statusCounts['Pending'] ?></strong>
            </article>
            <article class="stats-card accent-success">
                <span class="label">Approved</span>
                <strong class="value"><?= $statusCounts['Approved'] ?></strong>
            </article>
            <article class="stats-card accent-danger">
                <span class="label">Rejected</span>
                <strong class="value"><?= $statusCounts['Rejected'] ?></strong>
            </article>
            <article class="stats-card accent-secondary">
                <span class="label">Student ID</span>
                <strong class="value"><?= e($student['student_id'] ?? '-') ?></strong>
            </article>
        </section>

        <section class="content-grid">
            <article class="chart-card">
                <span class="eyebrow">Progress</span>
                <h2 class="section-title">Subjects per enrollment</h2>
                <?php if ($chartLabels): ?>
                    <div class="chart-wrap">
                        <canvas id="subjectsChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No data yet</h3>
                        <p class="muted">Your chart will appear after your first enrollment submission.</p>
                    </div>
                <?php endif; ?>
            </article>

            <article class="detail-card">
                <span class="eyebrow">Account Snapshot</span>
                <h2 class="section-title">Profile highlights</h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <span>Email</span>
                        <strong><?= e($student['email'] ?? '-') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Course</span>
                        <strong><?= e($student['course'] ?? '-') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Year Level</span>
                        <strong><?= e($student['year_level'] ?? '-') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Latest Submission</span>
                        <strong><?= e($latestEnrollment['date'] ?? 'Not yet submitted') ?></strong>
                    </div>
                </div>
            </article>
        </section>
    </div>

    <?php if ($chartLabels): ?>
        <script>
            const ctx = document.getElementById('subjectsChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Subjects',
                        data: <?= json_encode($chartData) ?>,
                        backgroundColor: ['#c46b2d', '#2f6f6d', '#d89a56', '#557c7b']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
