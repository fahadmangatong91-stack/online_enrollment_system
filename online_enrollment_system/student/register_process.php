<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$studentCode = trim($_POST['student_id'] ?? '');
$course = trim($_POST['course'] ?? '');
$yearLevel = trim($_POST['year_level'] ?? '');
$password = $_POST['password'] ?? '';

set_old([
    'fullname' => $fullname,
    'email' => $email,
    'student_id' => $studentCode,
    'course' => $course,
    'year_level' => $yearLevel,
]);

if ($fullname === '' || $email === '' || $studentCode === '' || $course === '' || $yearLevel === '' || $password === '') {
    set_flash('error', 'Please complete all required fields.');
    redirect('register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please enter a valid email address.');
    redirect('register.php');
}

if (strlen($password) < 8) {
    set_flash('error', 'Password must be at least 8 characters long.');
    redirect('register.php');
}

$stmt = $conn->prepare('SELECT id FROM students WHERE email = ? OR student_id = ? LIMIT 1');
$stmt->bind_param('ss', $email, $studentCode);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    set_flash('error', 'A student account already exists with that email or student ID.');
    redirect('register.php');
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    'INSERT INTO students (fullname, email, password, student_id, course, year_level)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssss', $fullname, $email, $hashedPassword, $studentCode, $course, $yearLevel);

if (!$stmt->execute()) {
    $stmt->close();
    set_flash('error', 'We could not create your account right now. Please try again.');
    redirect('register.php');
}

$stmt->close();
clear_old();
set_flash('success', 'Registration successful. You can now log in.');
redirect('login.php');
