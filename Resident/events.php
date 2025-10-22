<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';

/* ---------- Filters ---------- */
$cat  = $_GET['cat'] ?? 'All';
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

/* ---------- Toggle interest ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_interest'], $_POST['event_id']) && $resident_id) {
    $event_id = (int)$_POST['event_id'];

    $chk = $conn->prepare("SELECT id FROM event_interest WHERE event_id=? AND resident_id=?");
    $chk->bind_param("ii", $event_id, $resident_id);
    $chk->execute();
    $have = $chk->get_result();

    if ($have && $have->num_rows > 0) {
        $row = $have->fetch_assoc();
        $del = $conn->prepare("DELETE FROM event_interest WHERE id=?");
        $del->bind_param("i", $row['id']);
        $del->execute();
        $del->close();
    } else {
        $ins = $conn->prepare("INSERT INTO event_interest (event_id,resident_id) VALUES (?,?)");
        $ins->bind_param("ii", $event_id, $resident_id);
        $ins->execute();
        $ins->close();
    }
    $chk->close();

    header("Location: events.php?cat=" . urlencode($cat) . "&from=" . urlencode($from) . "&to=" . urlencode($to));
    exit();
}

/* ---------- Fetch events ---------- */
$sql = "SELECT id, title, description, category, venue, start_date, end_date, visibility, created_at
        FROM barangay_events
        WHERE barangay_name = ?";
$params = [$barangay];
$types = "s";

if ($cat !== '' && $cat !== 'All') { $sql .= " AND category = ?"; $params[] = $cat; $types .= "s"; }
if ($from !== '') { $sql .= " AND DATE(start_date) >= ?"; $params[] = $from; $types .= "s"; }
if ($to !== '') { $sql .= " AND DATE(start_date) <= ?"; $params[] = $to; $types .= "s"; }
$sql .= " ORDER BY start_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$events = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

/* ---------- Categories for filters ---------- */
$cq = $conn->prepare("SELECT DISTINCT category FROM barangay_events WHERE barangay_name=? ORDER BY category");
$cq->bind_param("s", $barangay);
$cq->execute();
$cres = $cq->get_result();
$categories = $cres ? array_column($cres->fetch_all(MYSQLI_ASSOC), 'category') : [];
$cq->close();

/* ---------- Interest counters ---------- */
$ids = array_column($events, 'id');
$interestCount = []; $mine = [];
if (!empty($ids)) {
    $in = implode(",", array_fill(0, count($ids), "?"));
    $t = str_repeat("i", count($ids));

    $q1 = $conn->prepare("SELECT event_id, COUNT(*) c FROM event_interest WHERE event_id IN ($in) GROUP BY event_id");
    $q1->bind_param($t, ...$ids);
    $q1->execute();
    $r1 = $q1->get_result();
    foreach ($r1->fetch_all(MYSQLI_ASSOC) as $row) {
        $interestCount[(int)$row['event_id']] = (int)$row['c'];
    }
    $q1->close();

    if ($resident_id) {
        $q2 = $conn->prepare("SELECT event_id FROM event_interest WHERE resident_id=? AND event_id IN ($in)");
        $typesMine = "i" . $t;
        $paramsMine = array_merge([$resident_id], $ids);
        $q2->bind_param($typesMine, ...$paramsMine);
        $q2->execute();
        $r2 = $q2->get_result();
        foreach ($r2->fetch_all(MYSQLI_ASSOC) as $row) {
            $mine[(int)$row['event_id']] = true;
        }
        $q2->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Servigo · Events</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
<style>
:root {
  --brand-green: #047857;
  --accent-blue: #3b82f6;
  --bg: #f3f4f6;
  --white: #ffffff;
  --text: #1e293b;
  --muted: #6b7280;
  --radius: 14px;
  --shadow: 0 2px 10px rgba(0,0,0,.08);
  --transition: 0.3s ease;
  --maxw: 1600px;
}

/* Base */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: "Parkinsans","Outfit","Roboto",system-ui,sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Layout */
main {
  flex: 1;
  width: 100%;
  max-width: var(--maxw);
  margin: 0 auto;
  padding: 1.25rem 1.25rem 3rem;
}

.page-header {
  display: flex;
  align-items: center;
  gap: .6rem;
  margin: .5rem 0 1rem 0;
}
.page-header i { color: var(--brand-green); font-size: 1.35rem; }
.page-header h2 {
  font-family: "Parkinsans","Outfit","Roboto",system-ui,sans-serif;
  color: var(--brand-green);
  font-size: 1.6rem;
  font-weight: 700;
}

/* Tabs */
.tabs {
  display: flex;
  gap: .75rem;
  border-bottom: 2px solid #e5e7eb;
  margin-bottom: 1.25rem;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 0 0.75rem;
  scroll-behavior: smooth;
  white-space: nowrap;
}
.tabs::-webkit-scrollbar {
  height: 6px;
}
.tabs::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 10px;
}
.tabs::-webkit-scrollbar-thumb {
  background: var(--accent-blue);
  border-radius: 10px;
}
.tabs::-webkit-scrollbar-thumb:hover {
  background: #2563eb;
}

.filter-tab {
  font-family: "Parkinsans","Outfit","Roboto",system-ui,sans-serif;
  background: none;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  color: var(--muted);
  padding: .55rem 1.1rem;
  cursor: pointer;
  border-radius: 999px;
  flex-shrink: 0;
  transition: background 0.3s, color 0.3s;
}
.filter-tab:hover { background: #e9eefb; }
.filter-tab.active {
  color: #fff;
  background: var(--brand-green);
}

/* Events Grid */
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.25rem;
  transition: opacity .28s ease, transform .28s ease;
}
.events-grid.fading {
  opacity: 0;
  transform: translateY(8px);
}

.event-card {
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: .75rem;
  transition: transform .22s var(--transition), box-shadow .22s var(--transition);
}
.event-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,.08);
}

.event-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.event-title { font-size: 1.1rem; font-weight: 700; }
.event-status {
  font-size: .8rem;
  color: #fff;
  background: var(--accent-blue);
  padding: .28rem .6rem;
  border-radius: 7px;
  font-weight: 600;
}
.event-status.upcoming { background: var(--brand-green); }
.event-status.past { background: #9ca3af; }

.event-meta { font-size: .95rem; color: var(--muted); display: grid; gap: .15rem; }
.event-meta i { margin-right: .35rem; color: var(--accent-blue); }

.event-description { font-size: .95rem; line-height: 1.5; color: var(--text); }

.event-actions {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.interested-btn {
  background: none;
  border: 1.6px solid var(--accent-blue);
  color: var(--accent-blue);
  border-radius: 12px;
  padding: .5rem .95rem;
  font-size: .95rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  cursor: pointer;
  transition: var(--transition);
}
.interested-btn i { font-size: 1.05rem; }
.interested-btn:hover { background: var(--accent-blue); color: #fff; }
.interested-btn.active { background: var(--brand-green); border-color: var(--brand-green); color: #fff; }

.interest-count { font-size: .95rem; color: var(--muted); }

.empty-state {
  text-align: center;
  color: var(--muted);
  margin-top: 1.4rem;
  font-size: .98rem;
}

@media (max-width: 640px) {
  .page-header h2 { font-size: 1.35rem; }
  .events-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<main>
  <div class="page-header">
    <i class="bi bi-calendar-event"></i>
    <h2>Events</h2>
  </div>

  <div class="tabs" id="tabs">
    <button class="filter-tab active" data-filter="all">All</button>
    <button class="filter-tab" data-filter="upcoming">Upcoming</button>
    <button class="filter-tab" data-filter="ongoing">Ongoing</button>
    <button class="filter-tab" data-filter="past">Past</button>
  </div>

  <div class="events-grid" id="eventGrid">
  <?php if (empty($events)): ?>
    <div class="empty-state"><i class="bi bi-emoji-frown"></i> No events found for this category.</div>
  <?php else: ?>
    <?php foreach ($events as $e): 
      $start = new DateTime($e['start_date']);
      $end = new DateTime($e['end_date'] ?? $e['start_date']);
      $count = $interestCount[$e['id']] ?? 0;
      $active = isset($mine[$e['id']]);
    ?>
    <article class="event-card"
      data-status=""
      data-start="<?= htmlspecialchars($e['start_date']) ?>"
      data-end="<?= htmlspecialchars($e['end_date'] ?? $e['start_date']) ?>">
      <div class="event-header">
        <span class="event-title"><?= htmlspecialchars($e['title']) ?></span>
        <span class="event-status">Upcoming</span>
      </div>
      <div class="event-meta">
        <div><i class="bi bi-calendar-check"></i> <?= $start->format('F j, Y g:i A') ?></div>
        <div><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['venue'] ?? 'TBA') ?></div>
      </div>
      <p class="event-description"><?= nl2br(htmlspecialchars($e['description'] ?? '')) ?></p>
      <div class="event-actions">
        <form method="post" style="margin:0;">
          <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
          <button class="interested-btn <?= $active ? 'active' : '' ?>" name="toggle_interest">
            <i class="bi <?= $active ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' ?>"></i> Interested
          </button>
        </form>
        <span class="interest-count"><?= $count ?> interested</span>
      </div>
    </article>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</main>
<script>
/* ===========================================================
   INTERACTIVE UI (Frontend)
=========================================================== */
const tabs = document.querySelectorAll('.filter-tab');
const grid = document.getElementById('eventGrid');
const cards = Array.from(document.querySelectorAll('.event-card'));
const empty = document.getElementById('emptyState');

/* 1) Auto status detection */
(function deriveStatus() {
  const now = new Date();
  cards.forEach(card => {
    const start = new Date(card.dataset.start);
    const end = new Date(card.dataset.end || card.dataset.start);
    let status = 'upcoming';
    if (now >= start && now <= end) status = 'ongoing';
    if (now > end) status = 'past';
    card.dataset.status = status;

    const badge = card.querySelector('.event-status');
    if (badge) {
      badge.classList.remove('upcoming','past');
      if (status === 'upcoming') { badge.textContent = 'Upcoming'; badge.classList.add('upcoming'); }
      else if (status === 'past') { badge.textContent = 'Past'; badge.classList.add('past'); }
      else { badge.textContent = 'Ongoing'; }
    }
  });
})();

/* 2) Choose initial tab */
(function setInitialTab() {
  let hasOngoing = cards.some(c => c.dataset.status === 'ongoing');
  let hasUpcoming = cards.some(c => c.dataset.status === 'upcoming');
  const target = hasOngoing ? 'ongoing' : (hasUpcoming ? 'upcoming' : 'all');
  const btn = document.querySelector(`.filter-tab[data-filter="${target}"]`) || document.querySelector('.filter-tab[data-filter="all"]');
  tabs.forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  applyFilter(target, false);
  window.addEventListener('load', () => centerTab(btn));
})();

/* 3) Click handler + smooth scroll centering */
tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    if (tab.classList.contains('active')) return;
    tabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    applyFilter(tab.dataset.filter, true);
    centerTab(tab);
  });
});

/* Helper: Center the active tab */
function centerTab(tab) {
  const tabsContainer = document.getElementById('tabs');
  const tabRect = tab.getBoundingClientRect();
  const containerRect = tabsContainer.getBoundingClientRect();
  const offset = (tabRect.left + tabRect.width/2) - (containerRect.left + containerRect.width/2);
  tabsContainer.scrollBy({ left: offset * 0.9, behavior: 'smooth' });
}

/* 4) Filter logic with fade animation */
function applyFilter(filter, animated) {
  if (animated) grid.classList.add('fading');
  setTimeout(() => {
    let visible = 0;
    cards.forEach(card => {
      const show = filter === 'all' || card.dataset.status === filter;
      card.style.display = show ? 'flex' : 'none';
      if (show) visible++;
    });
    empty.hidden = visible > 0;
    if (animated) requestAnimationFrame(() => grid.classList.remove('fading'));
  }, animated ? 160 : 0);
}

/* 5) Interested toggle */
document.querySelectorAll('.interested-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const countEl = btn.parentElement.querySelector('.interest-count');
    let count = parseInt(countEl.textContent);
    const ON = btn.classList.toggle('active');
    btn.innerHTML = ON
      ? '<i class="bi bi-hand-thumbs-up-fill"></i> Interested'
      : '<i class="bi bi-hand-thumbs-up"></i> Interested';
    countEl.textContent = `${ON ? count + 1 : count - 1} interested`;
  });
});

</script>
</body>
</html>
