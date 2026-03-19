# Online Enrollment System

A PHP and MySQL based online enrollment system for managing student registration, enrollment submission, subject selection, document uploads, and admin review.

This project is built for a local XAMPP environment and includes separate interfaces for students and administrators.

## Features

### Student Side
- Student account registration
- Secure login with password hashing
- Enrollment form with subject selection
- Upload of required documents
- Dashboard with enrollment statistics
- Enrollment status tracking
- Subject list view
- Profile page with profile picture upload
- Printable enrollment summary

### Admin Side
- Admin login
- Dashboard with enrollment analytics
- Review and update student enrollment status
- View complete enrollment details
- Manage available subjects

## Tech Stack

- PHP
- MySQL
- HTML/CSS
- Chart.js
- XAMPP

## Project Structure

```text
online_enrollment_system/
|-- admin/
|-- assets/
|   |-- css/
|   `-- uploads/
|-- config/
|-- includes/
|-- student/
|-- index.php
`-- README.md
```

## Requirements

- XAMPP
- PHP 8+
- MySQL / MariaDB
- Web browser

## Setup Instructions

1. Copy the project folder into your XAMPP `htdocs` directory.

   Example:

   ```text
   C:\xampp\htdocs\online_enrollment_system
   ```

2. Start `Apache` and `MySQL` from the XAMPP Control Panel.

3. Create a database named:

   ```text
   enrollment_db
   ```

4. Create the required tables in `enrollment_db`.

   The application expects these tables:

   - `students`
   - `subjects`
   - `enrollments`
   - `enrollment_subjects`

5. Update the database connection if needed in [config/db.php](C:\xampp\htdocs\online_enrollment_system\config\db.php).

6. Open the project in your browser:

   ```text
   http://localhost/online_enrollment_system/
   ```

## Expected Database Tables

### `students`

- `id`
- `fullname`
- `email`
- `password`
- `student_id`
- `course`
- `year_level`
- `profile_pic`

### `subjects`

- `subject_id`
- `subject_name`
- `units`

### `enrollments`

- `enrollment_id`
- `student_id`
- `address`
- `contact_number`
- `previous_school`
- `course`
- `birth_certificate`
- `form_138`
- `status`
- `date`

### `enrollment_subjects`

- `id`
- `enrollment_id`
- `subject_id`

Note:
The app automatically adds `profile_pic`, `birth_certificate`, and `form_138` if they are missing from the database schema.

## Default Admin Login

Use the default admin credentials below:

```text
Email: admin@school.com
Password: admin123
```

## Main Pages

### Public
- `/index.php`

### Student
- `/student/register.php`
- `/student/login.php`
- `/student/dashboard.php`
- `/student/enroll.php`
- `/student/status.php`
- `/student/my_subjects.php`
- `/student/profile.php`
- `/student/enrollment_summary.php`

### Admin
- `/admin/login.php`
- `/admin/dashboard.php`
- `/admin/manage_students.php`
- `/admin/manage_subjects.php`

## Notes

- Uploaded files are stored in `assets/uploads/`
- The project is designed for local development with XAMPP
- Admin credentials are hardcoded for demo purposes
- This project is suitable for academic, demo, and portfolio use

## Testing

You can test the database connection using:

```text
http://localhost/online_enrollment_system/test_connection.php
```

## Future Improvements

- Role-based admin accounts from the database
- Email notifications for enrollment status updates
- Better reporting and export features
- More detailed validation and audit logs
- Semester and school year management

## Author

Fahad Gornez Mangatong


