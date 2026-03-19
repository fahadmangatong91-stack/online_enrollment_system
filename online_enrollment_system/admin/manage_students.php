<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enrollment_id'], $_POST['status'])) {
    $enrollmentId = (int) $_POST['enrollment_id'];
    $status = $_POST['status'] === 'Approved' ? 'Approved' : ($_POST['status'] === 'Rejected' ? 'Rejected' : 'Pending');

    $stmt = $conn->prepare('UPDATE enrollments SET status = ? WHERE enrollment_id = ?');
    $stmt->bind_param('si', $status, $enrollmentId);
    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Enrollment status updated.');
    redirect('manage_students.php');
}

$flash = get_flash();
$currentPage = basename(__FILE__);
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = 'SELECT e.enrollment_id, e.course, e.status, e.date, s.fullname, s.student_id, s.email
        FROM enrollments e
        INNER JOIN students s ON s.id = e.student_id
        WHERE 1 = 1';
$types = '';
$params = [];

if ($statusFilter !== '' && in_array($statusFilter, ['Pending', 'Approved', 'Rejected'], true)) {
    $sql .= ' AND e.status = ?';
    $types .= 's';
    $params[] = $statusFilter;
}

if ($search !== '') {
    $sql .= ' AND (s.fullname LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)';
    $types .= 'sss';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= ' ORDER BY e.date DESC, e.enrollment_id DESC';
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Student Review Queue</span>
                <h1>Manage student enrollments</h1>
                <p class="page-subtitle">Search submissions, filter by review state, and update decisions without leaving the page.</p>
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

        <section class="table-card">
            <div class="filters">
                <form method="GET">
                    <div class="field">
                        <label for="q">Search</label>
                        <input id="q" type="text" name="q" value="<?= e($search) ?>" placeholder="Name, student ID, or email">
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All statuses</option>
                            <?php foreach (['Pending', 'Approved', 'Rejected'] as $option): ?>
                                <option value="<?= e($option) ?>" <?= $statusFilter === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field" style="justify-content: end;">
                        <label>&nbsp;</label>
                        <button type="submit">Apply Filters</button>
                    </div>
                </form>
            </div>

            <?php if ($enrollments): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $row): ?>
                            <?php $badgeClass = $row['status'] === 'Approved' ? 'badge-approved' : ($row['status'] === 'Rejected' ? 'badge-rejected' : 'badge-pending'); ?>
                            <tr>
                                <td>
                                    <strong><?= e($row['fullname']) ?></strong><br>
                                    <span class="meta"><?= e($row['email']) ?></span>
                                </td>
                                <td><?= e($row['student_id']) ?></td>
                                <td><?= e($row['course']) ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= e($row['status']) ?></span></td>
                                <td><?= e($row['date']) ?></td>
                                <td>
                                    <div class="inline-actions">
                                        <a class="btn btn-ghost btn-sm" href="view_student.php?id=<?= e((string) $row['enrollment_id']) ?>">Details</a>
                                        <form method="POST" style="display:inline-flex;">
                                            <input type="hidden" name="enrollment_id" value="<?= e((string) $row['enrollment_id']) ?>">
                                            <input type="hidden" name="status" value="Approved">
                                            <button class="btn btn-secondary btn-sm" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" style="display:inline-flex;">
                                            <input type="hidden" name="enrollment_id" value="<?= e((string) $row['enrollment_id']) ?>">
                                            <input type="hidden" name="status" value="Rejected">
                                            <button class="btn btn-danger btn-sm" type="submit">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p class="muted">No enrollments matched your filters.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
