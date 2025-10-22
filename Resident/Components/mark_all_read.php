<?php
require_once(__DIR__ . "/../../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../../Database/connection.php");

$resident_id = $_SESSION['sg_id'] ?? null;
if (!$resident_id) exit("invalid");

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE recipient_id=? AND recipient_type='resident'");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
echo "ok";
