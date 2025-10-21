<?php
session_start();
require_once(__DIR__ . "/connection.php");

// 1. Idle timeout: 30 minutes
$timeout_duration = 1800;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// 2. No login session? Redirect.
if (!isset($_SESSION['sg_id']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php?error=unauthenticated");
    exit();
}

// 3. Optional: Still active in DB? (Skip if not needed)
$account_id = $_SESSION['sg_id'];
$role       = $_SESSION['role'];

if ($role === 'resident') {
    $stmt = $conn->prepare("SELECT id FROM residents WHERE id = ? LIMIT 1");
} elseif ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id FROM barangay_admins WHERE id = ? LIMIT 1");
} else {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=invalidrole");
    exit();
}

$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result->fetch_assoc()) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=deactivated");
    exit();
}

// 4. Role Guard Function
function requireRole(string $roleName) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $roleName) {
        session_unset();
        session_destroy();
        header("Location: ../index.php?error=forbidden");
        exit();
    }
}

// 5. Multi-role guard (optional)
function requireRoles(array $allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        session_unset();
        session_destroy();
        header("Location: ../index.php?error=forbidden");
        exit();
    }
}
?>
