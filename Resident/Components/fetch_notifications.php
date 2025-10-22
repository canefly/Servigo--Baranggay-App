<?php
require_once(__DIR__ . "/../../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../../Database/connection.php");

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? null;

if (!$resident_id || !$barangay) {
  die("<div class='item'>Session expired — please re-login.</div>");
}

/* 🕒 Helper: time ago format */
function timeAgo($datetime) {
  $time = strtotime($datetime);
  $diff = time() - $time;
  if ($diff < 60) return $diff . "s ago";
  if ($diff < 3600) return floor($diff / 60) . "m ago";
  if ($diff < 86400) return floor($diff / 3600) . "h ago";
  return floor($diff / 86400) . "d ago";
}

/* 🗂️ Fetch notifications */
$stmt = $conn->prepare("
  SELECT id, title, message, type, is_read, link, created_at
  FROM notifications
  WHERE recipient_type='resident' AND recipient_id=? AND barangay_name=?
  ORDER BY created_at DESC LIMIT 10
");
$stmt->bind_param("is", $resident_id, $barangay);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  echo "<div class='item'>No notifications yet.</div>";
  exit;
}

/* 🧭 Icons by type */
$icons = [
  'cancellation' => "<i class='bi bi-x-circle-fill' style='color:#ef4444;'></i>",
  'approval'     => "<i class='bi bi-check-circle-fill' style='color:#16a34a;'></i>",
  'verification' => "<i class='bi bi-shield-check' style='color:#1e40af;'></i>",
  'system'       => "<i class='bi bi-info-circle-fill' style='color:#64748b;'></i>"
];

while ($n = $res->fetch_assoc()):
  $icon = $icons[$n['type']] ?? "<i class='bi bi-bell-fill' style='color:#64748b;'></i>";
  $unread = !$n['is_read'] ? "unread" : "";
  $link = $n['link'] ?: "#";
?>
  <div class="item <?= $unread ?>" data-id="<?= $n['id'] ?>" onclick="window.location.href='<?= htmlspecialchars($link) ?>'">
    <?= $icon ?> <strong><?= htmlspecialchars($n['title']) ?></strong><br>
    <?= htmlspecialchars($n['message']) ?><br>
    <small style="color:#6b7280;font-size:0.75rem;"><?= timeAgo($n['created_at']) ?></small>
  </div>
<?php endwhile; ?>
