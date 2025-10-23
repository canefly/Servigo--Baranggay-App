<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");

include 'Components/topbar.php';


$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';

/* ===========================================================
   DASHBOARD COUNTS
=========================================================== */

// 🗓️ Count upcoming events
$today = date('Y-m-d H:i:s');
$eventCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM barangay_events WHERE barangay_name=? AND start_date >= ?");
$stmt->bind_param("ss", $barangay, $today);
$stmt->execute();
$stmt->bind_result($eventCount);
$stmt->fetch();
$stmt->close();

// 🧾 Count all pending permits for this resident
$permitCount = 0;
if ($resident_id) {
  $tables = [
    "barangay_clearance_requests",
    "residency_requests",
    "indigency_requests",
    "goodmoral_requests",
    "soloparent_requests",
    "latebirth_requests",
    "norecord_requests",
    "ojt_requests",
    "business_permit_requests"
  ];
  foreach ($tables as $tbl) {
    if ($conn->query("SHOW TABLES LIKE '$tbl'")->num_rows > 0) {
      $stmt = $conn->prepare("SELECT COUNT(*) FROM `$tbl` WHERE resident_id=? AND status='Pending'");
      $stmt->bind_param("i", $resident_id);
      $stmt->execute();
      $stmt->bind_result($count);
      $stmt->fetch();
      $permitCount += $count;
      $stmt->close();
    }
  }
}

// 📢 Count announcements
$announcementCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM announcements WHERE barangay_name=?");
$stmt->bind_param("s", $barangay);
$stmt->execute();
$stmt->bind_result($announcementCount);
$stmt->fetch();
$stmt->close();

/* ===========================================================
   FILTERED ANNOUNCEMENTS LIST
=========================================================== */
$q        = trim($_GET['q'] ?? '');
$from     = $_GET['from'] ?? '';
$to       = $_GET['to'] ?? '';
$category = $_GET['cat'] ?? 'All';

$sql = "SELECT id, barangay_name, title, description, category, image_url, created_at 
        FROM announcements
        WHERE barangay_name = ?";
$params = [$barangay];
$types  = "s";

if ($q !== '') {
  $sql .= " AND (title LIKE ? OR description LIKE ?)";
  $params[] = "%$q%";
  $params[] = "%$q%";
  $types .= "ss";
}
if ($from !== '') {
  $sql .= " AND DATE(created_at) >= ?";
  $params[] = $from;
  $types .= "s";
}
if ($to !== '') {
  $sql .= " AND DATE(created_at) <= ?";
  $params[] = $to;
  $types .= "s";
}
if ($category !== 'All') {
  $sql .= " AND category = ?";
  $params[] = $category;
  $types .= "s";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Home (Residents)</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Parkinsans:wght@400;700&display=swap" rel="stylesheet">
<style>
/* ===========================================================
   SERVIGO DASHBOARD — Civic-Modern UX for Residents
=========================================================== */
:root {
  --brand-green:#047857;
  --brand-blue:#1e40af;
  --accent:#16a34a;
  --muted:#6b7280;
  --text:#111827;
  --white:#fff;
  --bg:#f5f7fa;
  --border:#e5e7eb;
  --shadow:0 4px 14px rgba(0,0,0,0.08);
  --radius:16px;
  --transition:0.3s ease;
}
*{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:"Parkinsans","Outfit","Roboto",system-ui,sans-serif;
  background:var(--bg);
  color:var(--text);
  line-height:1.6;
}
a{text-decoration:none;color:inherit}

/* ======= Layout ======= */
.container{max-width:1200px;margin:auto;padding:24px 16px}
section{margin-bottom:2rem}

/* ======= Dashboard Summary Cards ======= */
.summary-grid{
  display:grid;gap:1rem;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  margin-bottom:2rem;
}
.summary-card{
  background:var(--white);
  border-radius:var(--radius);
  border:1px solid var(--border);
  box-shadow:var(--shadow);
  padding:1rem 1.25rem;
  display:flex;align-items:center;gap:1rem;
  transition:transform var(--transition),box-shadow var(--transition);
}
.summary-card:hover{
  transform:translateY(-3px);
  box-shadow:0 6px 18px rgba(0,0,0,.1);
}
.summary-icon{
  width:46px;height:46px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;color:#fff;
}
.summary-icon.events{background:var(--brand-blue)}
.summary-icon.permits{background:var(--accent)}
.summary-icon.announcements{background:#f59e0b}
.summary-info h4{margin:0;font-size:1.05rem;color:var(--text)}
.summary-info p{margin:0;font-size:.9rem;color:var(--muted)}

/* ======= News Section ======= */
.dashboard-card{
  background:var(--white);
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:1.5rem;
  transition:var(--transition);
}
.dashboard-card:hover{box-shadow:0 6px 16px rgba(0,0,0,.08)}
.dashboard-card h2{
  color:var(--brand-blue);
  font-size:1.4rem;
  margin-bottom:.25rem;
}
.dashboard-card p.muted{color:var(--muted);margin-bottom:1rem}

/* ======= Filters ======= */
.controls{
  display:flex;flex-wrap:wrap;gap:10px;
  margin-bottom:1rem;align-items:center;
}
.input{
  border:1px solid var(--border);
  border-radius:12px;
  padding:10px 12px;
  font-size:.95rem;
}
.ghost{
  all:unset;
  cursor:pointer;
  border:1px solid var(--border);
  padding:8px 14px;
  border-radius:10px;
  font-weight:600;
  background:#f9fafb;
  transition:var(--transition);
}
.ghost:hover{background:#e5e7eb}

/* ======= News Cards ======= */
.news-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
  gap:1rem;
}
.news{
  background:var(--white);
  border:1px solid var(--border);
  border-radius:14px;
  padding:1rem 1.25rem;
  box-shadow:var(--shadow);
  display:flex;flex-direction:column;
  gap:.75rem;
  animation:fadeIn .5s ease;
}
@keyframes fadeIn{
  from{opacity:0;transform:translateY(10px)}
  to{opacity:1;transform:translateY(0)}
}
.news h3{margin:0;font-size:1.05rem;color:var(--brand-blue)}
.news .meta{
  font-size:.85rem;
  color:var(--muted);
}
.news .desc{
  font-size:.92rem;
  color:var(--text);
  line-height:1.45;
  overflow:hidden;
  white-space:pre-wrap;
  max-height:6.2em;
  transition:max-height .3s ease;
}
.news.expanded .desc{max-height:none}
.news .tag{
  display:inline-block;
  background:var(--brand-green);
  color:#fff;
  padding:2px 8px;
  border-radius:6px;
  font-size:.75rem;
  font-weight:600;
  margin-right:4px;
}
.news .see-toggle{
  all:unset;
  color:var(--brand-blue);
  font-weight:600;
  cursor:pointer;
  font-size:.85rem;
}

/* ======= Empty State ======= */
.empty{
  text-align:center;
  color:var(--muted);
  padding:1.5rem;
  border:1px dashed var(--border);
  border-radius:var(--radius);
  font-size:.95rem;
}

/* ======= Footer ======= */
footer{
  text-align:center;
  color:var(--muted);
  font-size:.85rem;
  padding:20px;
  margin-top:2rem;
}
</style>
</head>

<body>
  <?php include 'chat.html'; ?>
<main class="container">

  <!-- ======= TOP SUMMARY CARDS ======= -->
  <section class="summary-grid">
    <div class="summary-card">
      <div class="summary-icon events"><i class='bx bx-calendar-event'></i></div>
      <div class="summary-info">
        <h4>Upcoming Events</h4>
        <p><?= $eventCount ?> active</p>
      </div>
    </div>
    <div class="summary-card">
      <div class="summary-icon permits"><i class='bx bx-file'></i></div>
      <div class="summary-info">
        <h4>Pending Permits</h4>
        <p><?= $permitCount ?> awaiting approval</p>
      </div>
    </div>
    <div class="summary-card">
      <div class="summary-icon announcements"><i class='bx bx-broadcast'></i></div>
      <div class="summary-info">
        <h4>New Announcements</h4>
        <p><?= $announcementCount ?> updates</p>
      </div>
    </div>
  </section>

  <!-- ======= NEWS FEED ======= -->
  <section class="dashboard-card">
    <h2>Barangay News & Advisories</h2>
    <p class="muted">Showing updates for <strong><?= htmlspecialchars($barangay) ?></strong>.</p>

    <form method="GET" class="controls">
      <input id="q" name="q" class="input" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Search news or advisories...">
      <input id="from" name="from" type="date" class="input" value="<?= htmlspecialchars($from) ?>">
      <input id="to" name="to" type="date" class="input" value="<?= htmlspecialchars($to) ?>">
      <button type="submit" class="ghost">Apply</button>
      <a href="residentsPage.php" class="ghost">Clear</a>
    </form>

    <div class="news-grid">
      <?php if (empty($announcements)): ?>
        <div class="empty">No announcements found for your filters.</div>
      <?php else: ?>
        <?php foreach ($announcements as $item): ?>
          <article class="news">
            <div class="meta">
              <span class="tag"><?= htmlspecialchars($item['category']) ?></span>
              <?= htmlspecialchars($item['barangay_name']) ?> • <?= date('F j, Y', strtotime($item['created_at'])) ?>
            </div>
            <h3><?= htmlspecialchars($item['title']) ?></h3>
            <p class="desc"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
            <?php if (!empty($item['image_url'])): ?>
              <img src="/<?= ltrim(htmlspecialchars($item['image_url']), '/') ?>" alt="Announcement Image" style="width:100%;border-radius:10px;margin-top:.5rem;">
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<footer>© 2025 Servigo Civic Portal — Designed for Residents</footer>

<script>
// Toggle "See More" for long descriptions
document.querySelectorAll('.news').forEach(el=>{
  const desc = el.querySelector('.desc');
  if(desc && desc.textContent.length>150){
    const btn = document.createElement('button');
    btn.className = 'see-toggle';
    btn.textContent = 'See More';
    btn.onclick = ()=>{
      el.classList.toggle('expanded');
      btn.textContent = el.classList.contains('expanded')?'See Less':'See More';
    };
    el.appendChild(btn);
  }
});
</script>

</body>
</html>
