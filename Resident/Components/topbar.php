<?php
require_once(__DIR__ . "/../../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../../Database/connection.php");

$resident_id = $_SESSION['sg_id'] ?? null;
$resident_name = $_SESSION['sg_name'] ?? "Resident";
$barangay_name = $_SESSION['sg_brgy'] ?? "—";

// 🔔 Fetch Notifications from MySQL
$notifications = [];
if ($resident_id) {
  $stmt = $conn->prepare("
      SELECT id, title, message, created_at, is_read
      FROM notifications
      WHERE recipient_type='resident' AND recipient_id=?
      ORDER BY created_at DESC
  ");
  $stmt->bind_param("i", $resident_id);
  $stmt->execute();
  $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root{
  --bg:#f5f7fa; --card:#ffffff; --text:#1f2937; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb; --hover:#f3f4f6; --danger:#dc2626;
}
.sg-topbar{position:sticky; top:0; z-index:40; background:var(--card); border-bottom:1px solid var(--border);
  display:flex; justify-content:space-between; align-items:center; padding:10px 20px; transition:.3s;}
.sg-topbar .sg-brand{display:flex; align-items:center; gap:10px;}
.sg-topbar .sg-brand img{width:36px; height:36px; border-radius:10px; object-fit:cover;}
.sg-topbar .sg-brand h1{margin:0; font-size:1rem; font-weight:700; color:var(--brand);}
.sg-topbar .sg-right{display:flex; align-items:center; gap:10px; position:relative; flex-wrap:wrap; justify-content:flex-end;}
.sg-topbar .sg-chip{background:var(--hover); color:var(--text); padding:5px 10px; border-radius:6px; font-size:13px;}
.sg-topbar .sg-icon-btn{position:relative; width:40px; height:40px; border-radius:50%; background:var(--hover);
  display:flex; align-items:center; justify-content:center; cursor:pointer;}
.sg-topbar .sg-icon-btn:hover{background:rgba(30,64,175,.1);}
.sg-topbar .sg-icon-btn i{font-size:22px; color:var(--brand);}
.sg-topbar .sg-badge{position:absolute; top:6px; right:6px; background:var(--danger); color:#fff;
  font-size:10px; font-weight:600; padding:2px 5px; border-radius:10px; display:none;}
.sg-topbar .sg-dropdown{position:absolute; top:110%; background:#fff; border:1px solid var(--border);
  border-radius:8px; box-shadow:0 4px 14px rgba(0,0,0,.08); display:none; flex-direction:column;
  min-width:200px; max-height:350px; overflow-y:auto; z-index:999;}
.sg-topbar .sg-dropdown.sg-show{display:flex;}
.sg-topbar #sg-notifDropdown{right:60px;}
.sg-topbar #sg-userDropdown{right:0;}
.sg-topbar .sg-dropdown a{padding:10px 14px; text-decoration:none; color:var(--text); font-size:14px;
  display:flex; align-items:center; gap:6px; transition:background .2s;}
.sg-topbar .sg-dropdown a:hover{background:var(--hover); color:var(--brand);}
.sg-topbar .sg-notif-item{padding:10px 14px; border-bottom:1px solid var(--border); font-size:14px; color:var(--text);}
.sg-topbar .sg-notif-item.sg-unread{border-left:4px solid var(--accent); background:#f0fdf4;}
.sg-topbar .sg-notif-item small{color:var(--muted); font-size:12px; display:block; margin-top:2px;}
</style>

<header class="sg-topbar" id="sg-topbar">
  <div class="sg-brand">
    <img src="/SERVIGO/RESIDENT/Components/logo.png" alt="Servigo Logo">
    <h1>Servigo · Residents</h1>
  </div>

  <div class="sg-right">
    <span class="sg-chip">Logged in as: <strong><?= htmlspecialchars($resident_name) ?></strong></span>
    <span class="sg-chip">Barangay: <strong><?= htmlspecialchars($barangay_name) ?></strong></span>

    <!-- Notifications -->
    <div class="sg-icon-btn" id="sg-notifBtn" title="Notifications" aria-haspopup="true" aria-expanded="false">
      <i class='bx bx-bell'></i>
      <?php
      $unread_count = count(array_filter($notifications, fn($n) => !$n['is_read']));
      if ($unread_count > 0) {
        echo "<span class='sg-badge' id='sg-notifBadge'>{$unread_count}</span>";
      }
      ?>
    </div>
    <div class="sg-dropdown" id="sg-notifDropdown" role="menu" aria-label="Notifications">
      <div id="sg-notifList">
        <?php if (empty($notifications)): ?>
          <p style="padding:10px;color:var(--muted);font-size:13px;">No notifications yet.</p>
        <?php else: ?>
          <?php foreach ($notifications as $n): ?>
            <div class="sg-notif-item <?= !$n['is_read'] ? 'sg-unread' : '' ?>">
              <strong><?= htmlspecialchars($n['title']) ?></strong>
              <p style="margin:4px 0"><?= htmlspecialchars($n['message']) ?></p>
              <small><?= date("M j, Y g:i A", strtotime($n['created_at'])) ?></small>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- User -->
    <div class="sg-icon-btn" id="sg-userIcon" title="Account" aria-haspopup="true" aria-expanded="false">
      <i class='bx bx-user'></i>
    </div>
    <div class="sg-dropdown" id="sg-userDropdown" role="menu" aria-label="Account">
      <a href="verifyAccount.php"><i class='bx bx-check-shield'></i> Verify Account</a>
      <a href="../Database/logout.php"><i class='bx bx-log-out'></i> Logout</a>
    </div>
  </div>
</header>

<script>
(() => {
  const topbar = document.getElementById('sg-topbar');
  const userIcon = document.getElementById('sg-userIcon');
  const userDd   = document.getElementById('sg-userDropdown');
  const notifBtn = document.getElementById('sg-notifBtn');
  const notifDd  = document.getElementById('sg-notifDropdown');

  const show = el => el.classList.add('sg-show');
  const hide = el => el.classList.remove('sg-show');
  const toggle = el => el.classList.toggle('sg-show');

  userIcon.addEventListener('click', (e) => { e.stopPropagation(); toggle(userDd); hide(notifDd); });
  notifBtn.addEventListener('click', (e) => { e.stopPropagation(); toggle(notifDd); hide(userDd); });

  document.addEventListener('click', (e) => {
    if (!topbar.contains(e.target)) { hide(userDd); hide(notifDd); }
  });
})();
</script>
