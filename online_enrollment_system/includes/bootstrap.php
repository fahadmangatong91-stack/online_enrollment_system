<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function ensure_column(mysqli $conn, string $table, string $column, string $definition): void
{
    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);

    $result = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE `{$safeTable}` ADD COLUMN `{$safeColumn}` {$definition}");
    }
}

function ensure_database_schema(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    ensure_column($conn, 'students', 'profile_pic', "VARCHAR(255) NULL AFTER `year_level`");
    ensure_column($conn, 'enrollments', 'birth_certificate', "VARCHAR(255) NULL AFTER `course`");
    ensure_column($conn, 'enrollments', 'form_138', "VARCHAR(255) NULL AFTER `birth_certificate`");

    $checked = true;
}

ensure_database_schema($conn);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit();
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function set_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function old(string $key, string $default = ''): string
{
    return isset($_SESSION['old'][$key]) ? e((string) $_SESSION['old'][$key]) : e($default);
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function require_student(): array
{
    if (!isset($_SESSION['student_id'])) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }

    return [
        'id' => (int) $_SESSION['student_id'],
        'fullname' => $_SESSION['fullname'] ?? 'Student',
    ];
}

function require_admin(): void
{
    if (!isset($_SESSION['admin_logged_in'])) {
        set_flash('error', 'Please log in as an administrator.');
        redirect('login.php');
    }
}

function fetch_student(mysqli $conn, int $studentId): ?array
{
    $stmt = $conn->prepare('SELECT * FROM students WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $student;
}

function fetch_latest_enrollment(mysqli $conn, int $studentId): ?array
{
    $stmt = $conn->prepare('SELECT * FROM enrollments WHERE student_id = ? ORDER BY date DESC, enrollment_id DESC LIMIT 1');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $enrollment = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $enrollment;
}

function fetch_enrollment_subjects(mysqli $conn, int $enrollmentId): array
{
    $stmt = $conn->prepare(
        'SELECT s.subject_id, s.subject_name, s.units
         FROM enrollment_subjects es
         INNER JOIN subjects s ON s.subject_id = es.subject_id
         WHERE es.enrollment_id = ?
         ORDER BY s.subject_name ASC'
    );
    $stmt->bind_param('i', $enrollmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $subjects;
}

function handle_upload(string $field, array $allowedExtensions, string $prefix): string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please upload all required files.');
    }

    $originalName = $_FILES[$field]['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Unsupported file format uploaded.');
    }

    $uploadDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to save uploaded file.');
    }

    return $filename;
}

function nav_link(string $href, string $label, string $currentPage): string
{
    $active = basename($href) === $currentPage ? 'active' : '';

    return '<a class="' . $active . '" href="' . e($href) . '">' . e($label) . '</a>';
}

