<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        $name = trim($_POST['subject_name'] ?? '');
        $units = (int) ($_POST['units'] ?? 0);

        if ($name === '' || $units <= 0) {
            set_flash('error', 'Please provide a subject name and a valid unit count.');
        } else {
            $stmt = $conn->prepare('INSERT INTO subjects (subject_name, units) VALUES (?, ?)');
            $stmt->bind_param('si', $name, $units);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Subject added successfully.');
        }

        redirect('manage_subjects.php');
    }

    if (isset($_POST['delete_subject'])) {
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM subjects WHERE subject_id = ?');
        $stmt->bind_param('i', $subjectId);
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Subject removed.');
        redirect('manage_subjects.php');
    }
}

$flash = get_flash();
$currentPage = basename(__FILE__);
$subjects = $conn->query(
    'SELECT s.subject_id, s.subject_name, s.units, COUNT(es.id) AS enrollment_count
     FROM subjects s
     LEFT JOIN enrollment_subjects es ON es.subject_id = s.subject_id
     GROUP BY s.subject_id, s.subject_name, s.units
     ORDER BY s.subject_name ASC'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Subject Catalog</span>
                <h1>Manage available subjects</h1>
                <p class="page-subtitle">Add subjects that students can choose during enrollment and monitor how often each subject is selected.</p>
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

        <section class="content-grid">
            <article class="form-card">
                <span class="eyebrow">New Subject</span>
                <h2 class="section-title">Add a subject</h2>
                <form class="auth-form" method="POST">
                    <input type="hidden" name="add_subject" value="1">
                    <div class="field">
                        <label for="subject_name">Subject Name</label>
                        <input id="subject_name" type="text" name="subject_name" required>
                    </div>
                    <div class="field">
                        <label for="units">Units</label>
                        <input id="units" type="number" name="units" min="1" required>
                    </div>
                    <button type="submit">Add Subject</button>
                </form>
            </article>

            <article class="table-card">
                <span class="eyebrow">Current Catalog</span>
                <h2 class="section-title">All subjects</h2>
                <?php if ($subjects): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Units</th>
                                <th>Times Selected</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?= e($subject['subject_name']) ?></td>
                                    <td><?= e((string) $subject['units']) ?></td>
                                    <td><?= e((string) $subject['enrollment_count']) ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Delete this subject?');">
                                            <input type="hidden" name="delete_subject" value="1">
                                            <input type="hidden" name="subject_id" value="<?= e((string) $subject['subject_id']) ?>">
                                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p class="muted">No subjects are available yet.</p>
                    </div>
                <?php endif; ?>
            </article>
        </section>
    </div>
</body>
</html>
