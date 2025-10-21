<?php
session_start();
require_once __DIR__ . "/connection.php"; // adjust path if needed

// If user is logged in, log the logout event
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Insert into login_audit (optional but recommended)
    $stmt = $conn->prepare("INSERT INTO login_audit (user_id, action_type) VALUES (?, 'logout')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// Destroy session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../index.php");
exit();
?>
