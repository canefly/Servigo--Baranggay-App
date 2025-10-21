<?php
$host = 'localhost';
$dbname = 'sms';
$username = 'root';
$password = 'Scara1313'; // Leave blank for XAMPP

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
