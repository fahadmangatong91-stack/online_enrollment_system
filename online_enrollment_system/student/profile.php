<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();
$flash = get_flash();
$currentPage = basename(__FILE__);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pic'])) {
    try {
        $profilePicture = handle_upload('profile_pic', ['jpg', 'jpeg', 'png', 'gif'], 'profile');
        $stmt = $conn->prepare('UPDATE students SET profile_pic = ? WHERE id = ?');
        $stmt->bind_param('si', $profilePicture, $auth['id']);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Profile picture updated successfully.');
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
    }

    redirect('profile.php');
}

$student = fetch_student($conn, $auth['id']);
$profilePic = !empty($student['profile_pic']) ? '../assets/uploads/' . $student['profile_pic'] : '../assets/uploads/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="page-head">
            <div class="page-title">
                <span class="eyebrow">Student Profile</span>
                <h1>Account details</h1>
                <p class="page-subtitle">Review your personal information and keep your profile image up to date.</p>
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

        <section class="profile-card">
            <aside class="profile-image-card">
                <img class="profile-image-preview" src="<?= e($profilePic) ?>" alt="Profile picture">
                <form class="auth-form" method="POST" enctype="multipart/form-data">
                    <div class="field">
                        <label for="profile_pic">Update Profile Picture</label>
                        <input id="profile_pic" type="file" name="profile_pic" accept=".jpg,.jpeg,.png,.gif" required>
                    </div>
                    <button type="submit" name="upload_pic">Upload New Photo</button>
                </form>
            </aside>

            <section class="detail-card">
                <h2 class="section-title">Student information</h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <span>Full Name</span>
                        <strong><?= e($student['fullname'] ?? '') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Email Address</span>
                        <strong><?= e($student['email'] ?? '') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Student ID</span>
                        <strong><?= e($student['student_id'] ?? '') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Course</span>
                        <strong><?= e($student['course'] ?? '') ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Year Level</span>
                        <strong><?= e($student['year_level'] ?? '') ?></strong>
                    </div>
                </div>
            </section>
        </section>
    </div>
</body>
</html>
