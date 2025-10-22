<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("admin");
require_once(__DIR__ . "/../Database/connection.php");

include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

/* ─────────────────────────────────────────────────────────────────────────────
   Context
───────────────────────────────────────────────────────────────────────────── */
$admin_id      = $_SESSION['sg_id']    ?? null;
$barangay_name = $_SESSION['sg_brgy']  ?? 'Unknown Barangay';

$flash_msg  = "";
$flash_type = ""; // 'ok' or 'error'

/* ─────────────────────────────────────────────────────────────────────────────
   Helpers
───────────────────────────────────────────────────────────────────────────── */
function dtlocal_to_mysql(?string $v): ?string {
  if (!$v) return null;                       // e.g., "2025-10-21T13:00"
  $ts = strtotime($v);
  if ($ts === false) return null;
  return date('Y-m-d H:i:s', $ts);            // "2025-10-21 13:00:00"
}

/* ─────────────────────────────────────────────────────────────────────────────
   Actions: Create / Update / Delete
───────────────────────────────────────────────────────────────────────────── */
// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $title       = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $venue       = trim($_POST['venue'] ?? '');
  $category    = $_POST['category'] ?? 'General';
  $visibility  = $_POST['visibility'] ?? 'public';
  $start_date  = dtlocal_to_mysql($_POST['start_date'] ?? null);
  $end_date    = dtlocal_to_mysql($_POST['end_date'] ?? null);

  if ($title === '' || !$start_date) {
    $flash_msg = "❌ Title and Start Date/Time are required.";
    $flash_type = "error";
  } else {
    $stmt = $conn->prepare("
      INSERT INTO barangay_events
      (barangay_name, title, description, category, venue, start_date, end_date, visibility)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
      "ssssssss",
      $barangay_name, $title, $description, $category, $venue, $start_date, $end_date, $visibility
    );
    if ($stmt->execute()) {
      $new_event_id = $stmt->insert_id;

      // Broadcast notification to residents of the barangay
      $n_stmt = $conn->prepare("
        INSERT INTO notifications
          (barangay_name, recipient_type, recipient_id, source_table, source_id, type, title, message, link, is_read)
        VALUES (?, 'resident', NULL, 'barangay_events', ?, 'new_event', ?, ?, NULL, 0)
      ");
      $notif_title = "New Barangay Event Posted";
      $notif_msg   = "A new event \"$title\" has been scheduled in your barangay.";
      $n_stmt->bind_param("siss", $barangay_name, $new_event_id, $notif_title, $notif_msg);
      $n_stmt->execute();
      $n_stmt->close();

      $flash_msg = "✅ Event created!";
      $flash_type = "ok";
    } else {
      $flash_msg = "❌ Failed to create event.";
      $flash_type = "error";
    }
    $stmt->close();
  }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  $id          = intval($_POST['id'] ?? 0);
  $title       = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $venue       = trim($_POST['venue'] ?? '');
  $category    = $_POST['category'] ?? 'General';
  $visibility  = $_POST['visibility'] ?? 'public';
  $start_date  = dtlocal_to_mysql($_POST['start_date'] ?? null);
  $end_date    = dtlocal_to_mysql($_POST['end_date'] ?? null);

  if ($id <= 0 || $title === '' || !$start_date) {
    $flash_msg = "❌ Missing required fields for update.";
    $flash_type = "error";
  } else {
    // Restrict update to the admin's barangay
    $stmt = $conn->prepare("
      UPDATE barangay_events
      SET title=?, description=?, venue=?, category=?, visibility=?, start_date=?, end_date=?
      WHERE id=? AND barangay_name=?
    ");
    $stmt->bind_param(
      "sssssssis",
      $title, $description, $venue, $category, $visibility, $start_date, $end_date, $id, $barangay_name
    );
    if ($stmt->execute()) {
      $flash_msg = "✅ Event updated.";
      $flash_type = "ok";
    } else {
      $flash_msg = "❌ Failed to update event.";
      $flash_type = "error";
    }
    $stmt->close();
  }
}

// DELETE
if (isset($_GET['delete_id'])) {
  $del_id = intval($_GET['delete_id']);
  if ($del_id > 0) {
    $stmt = $conn->prepare("DELETE FROM barangay_events WHERE id=? AND barangay_name=?");
    $stmt->bind_param("is", $del_id, $barangay_name);
    if ($stmt->execute()) {
      $flash_msg = "🗑️ Event deleted.";
      $flash_type = "ok";
    } else {
      $flash_msg = "❌ Failed to delete event.";
      $flash_type = "error";
    }
    $stmt->close();
  }
}

/* ─────────────────────────────────────────────────────────────────────────────
   Fetch Events (with interest counts)
───────────────────────────────────────────────────────────────────────────── */
$events = [];
// LEFT JOIN interest count
$q = $conn->prepare("
  SELECT e.id, e.barangay_name, e.title, e.description, e.category, e.venue,
         e.start_date, e.end_date, e.visibility, e.created_at,
         COALESCE(ic.cnt, 0) AS interest_count
  FROM barangay_events e
  LEFT JOIN (
    SELECT event_id, COUNT(*) AS cnt
    FROM event_interest
    GROUP BY event_id
  ) ic ON ic.event_id = e.id
  WHERE e.barangay_name = ?
  ORDER BY e.start_date DESC
");
$q->bind_param("s", $barangay_name);
$q->execute();
$events = $q->get_result()->fetch_all(MYSQLI_ASSOC);
$q->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Barangay · Events Manager</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root {
  --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
  --brand:#047857; --accent:#10b981; --error:#b91c1c; --ok:#166534;
  --shadow:0 2px 8px rgba(0,0,0,.08); --radius:14px; --gap:16px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,sans-serif;}
.layout{display:flex;min-height:100vh;}
.main-content{flex:1;padding:var(--gap);transition:margin-left .3s ease;max-width:100%;}
@media(min-width:1024px){.main-content{margin-left:275px;}}
.card{background:var(--card);padding:var(--gap);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:var(--gap);width:100%;}
h2{margin-bottom:12px;font-size:1.25rem;color:var(--brand);}
label{font-weight:600;display:block;margin-top:12px;}
input,textarea,select{width:100%;padding:12px;font-size:15px;margin-top:6px;border:1px solid #e5e7eb;border-radius:10px}
textarea{resize:vertical;min-height:100px;}
.btn{width:100%;margin-top:16px;padding:12px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;font-weight:600;cursor:pointer;transition:.2s;}
.btn:hover{opacity:.9;}
.error,.ok{margin-top:10px;padding:10px;border-radius:8px;font-size:.9rem}
.error{background:#fee2e2;color:var(--error);border:1px solid #ef4444}
.ok{background:#dcfce7;color:var(--ok);border:1px solid #22c55e}
.post{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:16px;box-shadow:0 2px 6px rgba(0,0,0,.05);display:flex;flex-direction:column;gap:10px;word-wrap:break-word}
.post .meta{font-size:13px;color:var(--muted)}
.post h3{margin:0;font-size:16px;color:#111}
.post small{color:var(--muted)}
.edit-btn,.delete-btn{all:unset;cursor:pointer;font-size:14px;font-weight:600}
.edit-btn{color:var(--brand)}
.delete-btn{color:var(--error)}
.counter{font-size:13px;color:var(--muted)}
@media(max-width:600px){.card,.post{padding:12px;border-radius:10px}h2{font-size:1.1rem}.btn{font-size:14px;padding:10px}}
.modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;z-index:1000}
.modal.active{display:flex}
.modal-content{background:var(--card);border-radius:14px;box-shadow:var(--shadow);width:95%;max-width:500px;padding:20px;position:relative}
.modal h3{color:var(--brand);margin-bottom:10px}
.modal-close{position:absolute;top:20px;right:20px;background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted)}
</style>
</head>
<body>
<div class="layout">
  <main class="main-content">

    <!-- Create -->
    <section class="card">
      <h2>Create Event</h2>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <label>Title</label>
        <input type="text" name="title" required>
        <label>Description</label>
        <textarea name="description"></textarea>
        <label>Venue</label>
        <input type="text" name="venue">
        <label>Category</label>
        <select name="category" required>
          <option>General</option>
          <option>Health</option>
          <option>Clean-up Drive</option>
          <option>Sports</option>
          <option>Emergency</option>
        </select>
        <label>Visibility</label>
        <select name="visibility" required>
          <option value="public">Public</option>
          <option value="verified_only">Verified Residents Only</option>
        </select>
        <label>Start Date & Time</label>
        <input type="datetime-local" name="start_date" required>
        <label>End Date & Time</label>
        <input type="datetime-local" name="end_date">
        <button type="submit" class="btn">Create Event</button>
        <?php if ($flash_msg): ?>
          <p class="<?= $flash_type === 'ok' ? 'ok' : 'error' ?>"><?= $flash_msg ?></p>
        <?php endif; ?>
      </form>
    </section>

    <!-- Feed -->
    <section class="card">
      <h2>My Events</h2>
      <div id="posts">
        <?php if (empty($events)): ?>
          <p>No events yet.</p>
        <?php else: ?>
          <?php foreach ($events as $ev): ?>
            <div class="post"
                 data-id="<?= $ev['id'] ?>"
                 data-title="<?= htmlspecialchars($ev['title'], ENT_QUOTES) ?>"
                 data-description="<?= htmlspecialchars($ev['description'] ?? '', ENT_QUOTES) ?>"
                 data-venue="<?= htmlspecialchars($ev['venue'] ?? '', ENT_QUOTES) ?>"
                 data-category="<?= htmlspecialchars($ev['category'], ENT_QUOTES) ?>"
                 data-visibility="<?= htmlspecialchars($ev['visibility'], ENT_QUOTES) ?>"
                 data-start="<?= $ev['start_date'] ? date('Y-m-d\TH:i', strtotime($ev['start_date'])) : '' ?>"
                 data-end="<?= $ev['end_date'] ? date('Y-m-d\TH:i', strtotime($ev['end_date'])) : '' ?>">
              <div class="meta"><strong><?= htmlspecialchars($ev['category']) ?></strong> • <?= date('M j, Y', strtotime($ev['start_date'])) ?></div>
              <h3><?= htmlspecialchars($ev['title']) ?></h3>
              <p><?= nl2br(htmlspecialchars($ev['description'] ?? '')) ?></p>
              <small>📍 <?= htmlspecialchars($ev['venue'] ?: 'TBA') ?></small>
              <small>⭐ <?= (int)$ev['interest_count'] ?> interested</small>
              <div style="margin-top:6px;">
                <button class="edit-btn" onclick="openEdit(this)">✏ Edit</button> |
                <a class="delete-btn" href="?delete_id=<?= $ev['id'] ?>" onclick="return confirm('Delete this event?')">🗑 Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

  </main>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal()">&times;</button>
    <h3>Edit Event</h3>
    <form method="POST" id="editForm">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_id">
      <label>Title</label>
      <input type="text" name="title" id="edit_title" required>
      <label>Description</label>
      <textarea name="description" id="edit_description"></textarea>
      <label>Venue</label>
      <input type="text" name="venue" id="edit_venue">
      <label>Category</label>
      <select name="category" id="edit_category">
        <option>General</option>
        <option>Health</option>
        <option>Clean-up Drive</option>
        <option>Sports</option>
        <option>Emergency</option>
      </select>
      <label>Visibility</label>
      <select name="visibility" id="edit_visibility">
        <option value="public">Public</option>
        <option value="verified_only">Verified Residents Only</option>
      </select>
      <label>Start Date & Time</label>
      <input type="datetime-local" name="start_date" id="edit_start_date" required>
      <label>End Date & Time</label>
      <input type="datetime-local" name="end_date" id="edit_end_date">
      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>
</div>

<script>
// Modal controls (no AJAX)
const modal = document.getElementById('editModal');

function openEdit(btn){
  const card = btn.closest('.post');
  document.getElementById('edit_id').value          = card.dataset.id;
  document.getElementById('edit_title').value       = card.dataset.title || '';
  document.getElementById('edit_description').value = card.dataset.description || '';
  document.getElementById('edit_venue').value       = card.dataset.venue || '';
  document.getElementById('edit_category').value    = card.dataset.category || 'General';
  document.getElementById('edit_visibility').value  = card.dataset.visibility || 'public';
  document.getElementById('edit_start_date').value  = card.dataset.start || '';
  document.getElementById('edit_end_date').value    = card.dataset.end || '';
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal(){
  modal.classList.remove('active');
  document.body.style.overflow = '';
}
</script>
</body>
</html>
