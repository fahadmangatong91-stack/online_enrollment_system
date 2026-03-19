<?php

$servername = "localhost";
$username = "root";
$password = ""; // <-- empty, because root has no password
$database = "enrollment_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional: set charset
$conn->set_charset("utf8");

?>