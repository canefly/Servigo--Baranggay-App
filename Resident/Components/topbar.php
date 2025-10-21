<?php
require_once(__DIR__ . "/../../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../../Database/connection.php");

/* ---------------- Get session data ---------------- */
if (!isset($_SESSION['sg_id']) || !isset($_SESSION['sg_brgy'])) {
  die("❌ Missing session data — please re-login.");
}
$resident_id = $_SESSION['sg_id'];
$barangay    = $_SESSION['sg_brgy'];

/* ---------------- Fetch resident info from DB ---------------- */
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM residents WHERE id = ?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
  die("❌ Resident not found in database.");
}
$resident = $res->fetch_assoc();
$stmt->close();

$resident_name  = htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']);
$resident_email = htmlspecialchars($resident['email']);

/* ---------------- Fetch notifications from DB ---------------- */
$notifications = [];
$unreadCount = 0;

$stmt = $conn->prepare("
  SELECT id, title, message, is_read, created_at
  FROM notifications
  WHERE recipient_type='resident'
    AND recipient_id=?
    AND barangay_name=?
  ORDER BY created_at DESC
  LIMIT 10
");
$stmt->bind_param("is", $resident_id, $barangay);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $notifications[] = $row;
  if (!$row['is_read']) $unreadCount++;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Servigo · Resident Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

<style>
:root {
  --brand-green: #047857;
  --accent-blue: #3b82f6;
  --bg: #f9fafb;
  --text: #1e293b;
  --white: #ffffff;
  --muted: #64748b;
  --shadow: 0 2px 8px rgba(0,0,0,0.08);
  --radius: 12px;
  --transition: 0.3s ease;
}

/* Reset */
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:"Parkinsans","Outfit","Roboto",system-ui,sans-serif;
  background:var(--bg);
  color:var(--text);
}

/* ============================================
   Topbar
============================================ */
.topbar{
  width:100%;
  background:var(--white);
  box-shadow:var(--shadow);
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:0.8rem 1.5rem;
  position:sticky;
  top:0;
  z-index:1000;
}

/* Left group */
.left-group{
  display:flex;
  align-items:center;
  gap:1rem;
}

/* Burger */
#menu-toggle{
  background:none;
  border:none;
  font-size:1.8rem;
  color:var(--text);
  cursor:pointer;
}

/* Title */
.title{
  font-size:1.2rem;
  font-weight:600;
  color:var(--brand-green);
}

/* Notification Bell */
.bell{
  position:relative;
  cursor:pointer;
}
.bell svg{
  width:24px;height:24px;
  fill:var(--text);
  transition:var(--transition);
}
.bell:hover svg{fill:var(--accent-blue);}
.badge{
  position:absolute;
  top:-6px;right:-6px;
  background:var(--accent-blue);
  color:#fff;
  font-size:0.7rem;
  padding:2px 5px;
  border-radius:8px;
  animation:pulse 1.6s infinite;
}
@keyframes pulse{
  0%{box-shadow:0 0 0 0 rgba(59,130,246,0.5);}
  70%{box-shadow:0 0 0 6px rgba(59,130,246,0);}
  100%{box-shadow:0 0 0 0 rgba(59,130,246,0);}
}

/* Notification Dropdown */
.notify-dropdown{
  display:none;
  position:absolute;
  top:3rem;
  right:1.5rem;
  background:var(--white);
  box-shadow:var(--shadow);
  border-radius:var(--radius);
  width:260px;
  overflow:hidden;
  z-index:2001;
}
.notify-dropdown.active{
  display:flex;
  flex-direction:column;
}
.notify-dropdown h4{
  background:var(--brand-green);
  color:#fff;
  padding:0.6rem 1rem;
  font-size:0.9rem;
}
.notify-dropdown .item{
  padding:0.75rem 1rem;
  border-bottom:1px solid #e5e7eb;
  font-size:0.9rem;
  transition:var(--transition);
}
.notify-dropdown .item:hover{
  background:var(--accent-blue);
  color:#fff;
}

/* ============================================
   Drawer Navigation (Left Side)
============================================ */
/* Drawer Navigation (Left Side) */
nav {
  position: fixed;
  top: 0;
  left: -100%;
  height: 100vh;
  width: 75%;
  max-width: 310px;
  background: var(--white);
  box-shadow: 2px 0 10px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
  transition: left 0.35s ease;
  z-index: 2000;
  overflow-y: auto;              /* ✅ enable vertical scroll */
  scroll-behavior: smooth;
  scrollbar-width: thin;         /* Firefox scrollbar */
  scrollbar-color: #d1d5db transparent; /* light grey thumb */
}
nav.active { left: 0; }
nav a {
  padding: 0.8rem 1.3rem;
}


/* ✅ Custom scrollbar for Chrome, Edge, Safari */
nav::-webkit-scrollbar {
  width: 6px;
}
nav::-webkit-scrollbar-track {
  background: transparent;
}
nav::-webkit-scrollbar-thumb {
  background: #d1d5db;          /* light grey */
  border-radius: 10px;
}
nav::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;          /* darker grey on hover */
}


nav .profile-section{
  padding:2rem 1.5rem 1rem;
  text-align:center;
  border-bottom:1px solid #e5e7eb;
}
nav .profile-section img{
  width:80px;
  height:80px;
  border-radius:50%;
  border:3px solid var(--accent-blue);
  object-fit:cover;
}
nav .profile-section h3{
  margin-top:0.75rem;
  font-size:1.1rem;
  color:var(--brand-green);
}
nav .profile-section p{
  color:var(--muted);
  font-size:0.9rem;
}

/* Drawer Links */
nav h4{
  margin:1rem 1.5rem 0.3rem;
  color:var(--muted);
  font-size:0.85rem;
  text-transform:uppercase;
  letter-spacing:0.5px;
}
nav hr{
  border:none;
  border-bottom:1px solid #e5e7eb;
  margin:0.8rem 0;
}
nav a{
  display:flex;
  align-items:center;
  gap:0.8rem;
  padding:0.9rem 1.5rem;
  color:var(--text);
  text-decoration:none;
  font-weight:500;
  border-bottom:1px solid #f1f5f9;
  transition:var(--transition);
}
nav a span.icon{
  font-size:1.2rem;
}
nav a:hover{
  background:var(--accent-blue);
  color:#fff;
}

nav a i {
  font-size: 1.1rem;
  width: 22px;
  text-align: center;
  color: var(--brand-green);
  transition: var(--transition);
}
nav a:hover i {
  color: #fff;
}


/* Overlay */
.overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.4);
  display:none;
  z-index:1500;
}
.overlay.active{display:block;}

/* Responsive */
@media(max-width:900px){
  .title{font-size:1.1rem;}
}
</style>
</head>
<body>

<header class="topbar">
  <div class="left-group">
    <button id="menu-toggle">&#9776;</button>
    <div class="title"><?= htmlspecialchars($barangay) ?></div>
  </div>

  <div class="bell" id="bell">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
      <path d="M12 24a2.4 2.4 0 0 0 2.4-2.4h-4.8A2.4 2.4 0 0 0 12 24zm6.7-6V11a6.7 6.7 0 0 0-5-6.4V4a1.7 1.7 0 1 0-3.4 0v.6A6.7 6.7 0 0 0 5.3 11v7L3 20v1h18v-1l-2.3-2z"/>
    </svg>
    <?php if ($unreadCount > 0): ?>
      <div class="badge" id="badge"><?= $unreadCount ?></div>
    <?php endif; ?>
  </div>

  <div class="notify-dropdown" id="notify-dropdown">
    <h4>Notifications</h4>
    <?php if (empty($notifications)): ?>
      <div class="item">No notifications yet.</div>
    <?php else: ?>
      <?php foreach ($notifications as $n): ?>
        <div class="item <?= $n['is_read'] ? '' : 'unread' ?>">
          <strong><?= htmlspecialchars($n['title']) ?></strong><br>
          <?= htmlspecialchars($n['message']) ?><br>
          <small style="color:var(--muted);font-size:0.75rem;"><?= htmlspecialchars($n['created_at']) ?></small>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</header>

<div class="overlay" id="overlay"></div>
<nav id="nav-links">
  <div class="profile-section">
    <img src="./Components/logo.png" alt="Servigo Logo">
    <h3><?= $resident_name ?></h3>
    <p><?= $resident_email ?></p>
  </div>
  <h4>Pages</h4>
  <a href="./residentsPage.php"><i class="bi bi-people-fill"></i> Residents</a>
  <a href="./permitsPage.php"><i class="bi bi-file-earmark-text-fill"></i> Permits</a>
  <a href="./registrationPage.php"><i class="bi bi-person-plus-fill"></i> Registration</a>
  <a href="./events.php"><i class="bi bi-calendar-event-fill"></i> Events</a>
  <a href="./storesPage.php"><i class="bi bi-shop"></i> Stores</a>
  <hr>
  <h4>Account</h4>
  <a href="#"><i class="bi bi-gear-fill"></i> Account Settings</a>
  <a href="./verifyAccount.php"><i class="bi bi-shield-check"></i> Verify Account</a>
  <a href="#"><i class="bi bi-question-circle-fill"></i> Help Center</a>
  <hr>
  <a href="../Database/logout.php" style="color:#b91c1c;"><i class="bi bi-box-arrow-right"></i> Log Out</a>
</nav>

<script>
const bell=document.getElementById('bell');
const dropdown=document.getElementById('notify-dropdown');
bell.onclick=e=>{
  dropdown.classList.toggle('active');
  e.stopPropagation();
};
document.addEventListener('click',e=>{
  if(!bell.contains(e.target)) dropdown.classList.remove('active');
});
const toggle=document.getElementById('menu-toggle');
const nav=document.getElementById('nav-links');
const overlay=document.getElementById('overlay');
toggle.onclick=()=>{nav.classList.add('active');overlay.classList.add('active');};
overlay.onclick=()=>{nav.classList.remove('active');overlay.classList.remove('active');};
</script>

</body>
</html>
