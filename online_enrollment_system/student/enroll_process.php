<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('enroll.php');
}

$address = trim($_POST['address'] ?? '');
$contact = trim($_POST['contact_number'] ?? '');
$previousSchool = trim($_POST['previous_school'] ?? '');
$course = trim($_POST['course'] ?? '');
$subjects = isset($_POST['subjects']) && is_array($_POST['subjects']) ? array_map('intval', $_POST['subjects']) : [];

set_old([
    'address' => $address,
    'contact_number' => $contact,
    'previous_school' => $previousSchool,
    'course' => $course,
    'subjects' => $subjects,
]);

if ($address === '' || $contact === '' || $previousSchool === '' || $course === '') {
    set_flash('error', 'Please complete the enrollment form.');
    redirect('enroll.php');
}

if (!$subjects) {
    set_flash('error', 'Please select at least one subject.');
    redirect('enroll.php');
}

try {
    $birthCertificate = handle_upload('birth_certificate', ['jpg', 'jpeg', 'png', 'pdf'], 'birth_certificate');
    $form138 = handle_upload('form_138', ['jpg', 'jpeg', 'png', 'pdf'], 'form138');
} catch (RuntimeException $exception) {
    set_flash('error', $exception->getMessage());
    redirect('enroll.php');
}

$conn->begin_transaction();

try {
    $status = 'Pending';
    $stmt = $conn->prepare(
        'INSERT INTO enrollments (student_id, address, contact_number, previous_school, course, birth_certificate, form_138, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssssss', $auth['id'], $address, $contact, $previousSchool, $course, $birthCertificate, $form138, $status);
    $stmt->execute();
    $enrollmentId = $stmt->insert_id;
    $stmt->close();

    $subjectStmt = $conn->prepare('INSERT INTO enrollment_subjects (enrollment_id, subject_id) VALUES (?, ?)');
    foreach ($subjects as $subjectId) {
        $subjectStmt->bind_param('ii', $enrollmentId, $subjectId);
        $subjectStmt->execute();
    }
    $subjectStmt->close();

    $conn->commit();
} catch (Throwable $throwable) {
    $conn->rollback();
    set_flash('error', 'We could not submit your enrollment right now. Please try again.');
    redirect('enroll.php');
}

clear_old();
set_flash('success', 'Enrollment submitted successfully. Your record is now pending review.');
redirect('status.php');
