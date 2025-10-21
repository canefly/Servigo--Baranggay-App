<?php
// role_check.php
session_start();
require_once "connection.php";

// ⏳ 1. Session Expiry (Idle Timeout)
$timeout_duration = 1800; // 30 mins
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// ❌ 2. No User? Bye.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthenticated");
    exit();
}

// 🧼 3. Fetch role from DB again (verify if user is still active)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = ? AND active = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=deactivated");
    exit();
}

// 🔐 4. Role check stored freshly
$_SESSION['role_id'] = $user['role_id'];

// 🎭 5. Role Verification Utility
function requireRole($roleName) {
    $roles = [
        1 => "Admin",
        2 => "Employee",
        3 => "Student"
    ];

    if (!isset($_SESSION['role_id']) || $roles[$_SESSION['role_id']] !== $roleName) {
        session_unset();
        session_destroy();
        header("Location: ../index.php?error=forbidden");
        exit();
    }
}

// 👥 6. OR Multiple Role Access (Optional Helper)
function requireRoles(array $allowedRoles) {
    $roles = [
        1 => "Admin",
        2 => "Employee",
        3 => "Student"
    ];

    if (!isset($_SESSION['role_id']) || !in_array($roles[$_SESSION['role_id']], $allowedRoles)) {
        session_unset();
        session_destroy();
        header("Location: ../index.php?error=forbidden");
        exit();
    }
}
?>
