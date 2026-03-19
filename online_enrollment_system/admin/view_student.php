<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

$enrollmentId = (int) ($_GET['id'] ?? 0);
if ($enrollmentId <= 0) {
    set_flash('error', 'No student enrollment was selected.');
    redirect('manage_students.php');
}

$stmt = $conn->prepare(
    'SELECT e.*, s.fullname, s.student_id, s.email, s.course AS student_course, s.year_level, s.profile_pic
     FROM enrollments e
     INNER JOIN students s ON s.id = e.student_id
     WHERE e.enrollment_id = ?
     LIMIT 1'
);
$stmt->bind_param('i', $enrollmentId);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$record) {
    set_flash('error', 'Enrollment record not found.');
    redirect('manage_students.php');
}

$subjects = fetch_enrollment_subjects($conn, $enrollmentId);
$badgeClass = $record['status'] === 'Approved' ? 'badge-approved' : ($record['status'] === 'Rejected' ? 'badge-rejected' : 'badge-pending');
$profilePic = !empty($record['profile_pic']) ? '../assets/uploads/' . $record['profile_pic'] : '../assets/uploads/default.png';
$currentPage = 'manage_students.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Enrollment</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Enrollment Detail</span>
                <h1><?= e($record['fullname']) ?></h1>
                <p class="page-subtitle">Review this submission in full, including documents, selected subjects, and current decision.</p>
            </div>
            <div class="toolbar">
                <a class="btn btn-ghost" href="manage_students.php">Back to Queue</a>
            </div>
        </header>

        <nav class="app-nav">
            <?= nav_link('dashboard.php', 'Dashboard', $currentPage) ?>
            <?= nav_link('manage_students.php', 'Manage Students', $currentPage) ?>
            <?= nav_link('manage_subjects.php', 'Manage Subjects', $currentPage) ?>
            <?= nav_link('logout.php', 'Logout', $currentPage) ?>
        </nav>

        <section class="profile-card">
            <aside class="profile-image-card">
                <img class="profile-image-preview" src="<?= e($profilePic) ?>" alt="Student profile image">
                <p><strong>Student ID:</strong> <?= e($record['student_id']) ?></p>
                <p><strong>Email:</strong> <?= e($record['email']) ?></p>
                <p><strong>Status:</strong> <span class="badge <?= $badgeClass ?>"><?= e($record['status']) ?></span></p>
            </aside>

            <section class="detail-card">
                <h2 class="section-title">Enrollment information</h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <span>Course</span>
                        <strong><?= e($record['course']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Year Level</span>
                        <strong><?= e($record['year_level']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Address</span>
                        <strong><?= e($record['address']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Contact Number</span>
                        <strong><?= e($record['contact_number']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Previous School</span>
                        <strong><?= e($record['previous_school']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Submitted On</span>
                        <strong><?= e($record['date']) ?></strong>
                    </div>
                </div>

                <div class="status-actions" style="margin-top: 22px;">
                    <a class="file-link" href="../assets/uploads/<?= e($record['birth_certificate']) ?>" target="_blank" rel="noopener">View Birth Certificate</a>
                    <a class="file-link" href="../assets/uploads/<?= e($record['form_138']) ?>" target="_blank" rel="noopener">View Form 138</a>
                </div>
            </section>
        </section>

        <section class="table-card" style="margin-top: 24px;">
            <span class="eyebrow">Subject Selection</span>
            <h2 class="section-title">Chosen subjects</h2>
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
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p class="muted">No subjects were attached to this submission.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
