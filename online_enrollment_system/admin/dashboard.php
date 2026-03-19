<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$flash = get_flash();
$currentPage = basename(__FILE__);

$totals = [
    'students' => (int) $conn->query('SELECT COUNT(*) AS total FROM students')->fetch_assoc()['total'],
    'pending' => (int) $conn->query("SELECT COUNT(*) AS total FROM enrollments WHERE status = 'Pending'")->fetch_assoc()['total'],
    'approved' => (int) $conn->query("SELECT COUNT(*) AS total FROM enrollments WHERE status = 'Approved'")->fetch_assoc()['total'],
    'rejected' => (int) $conn->query("SELECT COUNT(*) AS total FROM enrollments WHERE status = 'Rejected'")->fetch_assoc()['total'],
];

$statusLabels = [];
$statusCounts = [];
$result = $conn->query('SELECT status, COUNT(*) AS total FROM enrollments GROUP BY status');
while ($row = $result->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusCounts[] = (int) $row['total'];
}

$courseLabels = [];
$courseCounts = [];
$result = $conn->query('SELECT course, COUNT(*) AS total FROM enrollments GROUP BY course ORDER BY total DESC');
while ($row = $result->fetch_assoc()) {
    $courseLabels[] = $row['course'];
    $courseCounts[] = (int) $row['total'];
}

$recentEnrollments = $conn->query(
    'SELECT e.enrollment_id, e.course, e.status, e.date, s.fullname, s.student_id
     FROM enrollments e
     INNER JOIN students s ON s.id = e.student_id
     ORDER BY e.date DESC, e.enrollment_id DESC
     LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Admin Dashboard</span>
                <h1>Enrollment overview</h1>
                <p class="page-subtitle">Monitor enrollment volume, track review outcomes, and jump directly into student submissions.</p>
            </div>
            <div class="toolbar">
                <a class="btn btn-secondary" href="manage_students.php">Review Students</a>
                <a class="btn btn-ghost" href="../index.php">Home</a>
            </div>
        </header>

        <nav class="app-nav">
            <?= nav_link('dashboard.php', 'Dashboard', $currentPage) ?>
            <?= nav_link('manage_students.php', 'Manage Students', $currentPage) ?>
            <?= nav_link('manage_subjects.php', 'Manage Subjects', $currentPage) ?>
            <?= nav_link('logout.php', 'Logout', $currentPage) ?>
        </nav>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="stats-grid">
            <article class="stats-card accent-secondary">
                <span class="label">Registered Students</span>
                <strong class="value"><?= $totals['students'] ?></strong>
            </article>
            <article class="stats-card accent-primary">
                <span class="label">Pending Reviews</span>
                <strong class="value"><?= $totals['pending'] ?></strong>
            </article>
            <article class="stats-card accent-success">
                <span class="label">Approved</span>
                <strong class="value"><?= $totals['approved'] ?></strong>
            </article>
            <article class="stats-card accent-danger">
                <span class="label">Rejected</span>
                <strong class="value"><?= $totals['rejected'] ?></strong>
            </article>
        </section>

        <section class="content-grid">
            <article class="chart-card">
                <span class="eyebrow">Review Distribution</span>
                <h2 class="section-title">Enrollment status overview</h2>
                <?php if ($statusLabels): ?>
                    <div class="chart-wrap">
                        <canvas id="statusChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p class="muted">No enrollments have been submitted yet.</p>
                    </div>
                <?php endif; ?>
            </article>

            <article class="chart-card">
                <span class="eyebrow">Program Demand</span>
                <h2 class="section-title">Enrollments by course</h2>
                <?php if ($courseLabels): ?>
                    <div class="chart-wrap">
                        <canvas id="courseChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p class="muted">Course activity will appear once students begin enrolling.</p>
                    </div>
                <?php endif; ?>
            </article>
        </section>

        <section class="table-card" style="margin-top: 24px;">
            <span class="eyebrow">Latest Activity</span>
            <h2 class="section-title">Recent enrollments</h2>
            <?php if ($recentEnrollments): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentEnrollments as $row): ?>
                            <?php $badgeClass = $row['status'] === 'Approved' ? 'badge-approved' : ($row['status'] === 'Rejected' ? 'badge-rejected' : 'badge-pending'); ?>
                            <tr>
                                <td><a class="link-button" href="view_student.php?id=<?= e((string) $row['enrollment_id']) ?>"><?= e($row['fullname']) ?></a></td>
                                <td><?= e($row['student_id']) ?></td>
                                <td><?= e($row['course']) ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= e($row['status']) ?></span></td>
                                <td><?= e($row['date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p class="muted">No recent records to display.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($statusLabels): ?>
        <script>
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($statusLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($statusCounts) ?>,
                        backgroundColor: ['#c98a17', '#1f8f63', '#c44c44']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        </script>
    <?php endif; ?>

    <?php if ($courseLabels): ?>
        <script>
            new Chart(document.getElementById('courseChart'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($courseLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($courseCounts) ?>,
                        backgroundColor: ['#2f6f6d', '#c46b2d', '#557c7b', '#d89a56']
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
