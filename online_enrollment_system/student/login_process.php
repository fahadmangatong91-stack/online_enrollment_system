<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

set_old(['email' => $email]);

if ($email === '' || $password === '') {
    set_flash('error', 'Please enter your email and password.');
    redirect('login.php');
}

$stmt = $conn->prepare('SELECT id, fullname, password FROM students WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student || !password_verify($password, $student['password'])) {
    set_flash('error', 'Invalid email or password.');
    redirect('login.php');
}

$_SESSION['student_id'] = (int) $student['id'];
$_SESSION['fullname'] = $student['fullname'];

clear_old();
set_flash('success', 'Welcome back, ' . $student['fullname'] . '.');
redirect('dashboard.php');
