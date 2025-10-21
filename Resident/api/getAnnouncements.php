<?php
require_once(__DIR__ . "/../../Database/connection.php");
session_start();

header("Content-Type: application/json");

$barangay = $_SESSION['sg_brgy'] ?? ($_GET['barangay'] ?? 'Unknown Barangay');

$sql = "SELECT id, barangay_name, title, description, category, image_url, created_at 
        FROM announcements 
        WHERE barangay_name = ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $barangay);
$stmt->execute();
$result = $stmt->get_result();

$announcements = [];
while ($row = $result->fetch_assoc()) {
  $announcements[] = $row;
}

echo json_encode($announcements);
exit;
?>
