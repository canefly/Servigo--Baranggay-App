<?php
function addSystemLog($conn, $level, $message, $origin, $user_id = null) {
    $stmt = $conn->prepare("INSERT INTO system_logs (level, message, origin, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $level, $message, $origin, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>
