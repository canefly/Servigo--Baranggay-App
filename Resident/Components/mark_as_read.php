<?php
require_once(__DIR__ . "/../../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../../Database/connection.php");

$resident_id = $_SESSION['sg_id'] ?? null;
$id = intval($_GET['id'] ?? 0);
if (!$id || !$resident_id) exit("invalid");

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND recipient_id=?");
$stmt->bind_param("ii", $id, $resident_id);
$stmt->execute();

echo $stmt->affected_rows > 0 ? "ok" : "none";
