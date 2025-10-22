<?php
require_once(__DIR__ . "/../Database/session-checker.php");
require_once(__DIR__ . "/../Database/connection.php");
requireRole("admin");

/* ================================
   CONFIG
================================ */
$allowed_tables = [
  'barangay_clearance_requests' => 'Barangay Clearance',
  'business_permit_requests'    => 'Business Permit',
  'goodmoral_requests'          => 'Good Moral',
  'indigency_requests'          => 'Indigency',
  'latebirth_requests'          => 'Late Birth Registration',
  'norecord_requests'           => 'No Record',
  'ojt_requests'                => 'OJT',
  'residency_requests'          => 'Residency',
  'soloparent_requests'         => 'Solo Parent'
];

/* ================================
   SMALL HELPERS
================================ */
function safe_table($name, $allowed) {
  return array_key_exists($name, $allowed) ? $name : null;
}

/* ================================
   AJAX: VIEW DETAILS (GET)
   ?view=1&table=...&id=...
================================ */
if (isset($_GET['view'], $_GET['table'], $_GET['id'])) {
  $table = safe_table($_GET['table'], $allowed_tables);
  $id    = intval($_GET['id']);
  if (!$table) { http_response_code(400); echo json_encode(['error'=>'Invalid table']); exit; }

  // Pull full row (joins resident if available)
  $sql = "SELECT r.*, res.first_name, res.last_name
          FROM $table r
          LEFT JOIN residents res ON r.resident_id = res.id
          WHERE r.id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  header('Content-Type: application/json');
  echo json_encode($row ?: []);
  exit;
}

/* ================================
   AJAX: UPDATE STATUS (POST)
   Fields:
   - id, table, action (Ready|Completed|Rejected)
   - reason  (optional; used when Rejected)
   - resident_id (optional; for notifications)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['table'], $_POST['action'])) {
  $id        = intval($_POST['id']);
  $table     = safe_table($_POST['table'], $allowed_tables);
  $action    = $_POST['action'];
  $reason    = trim($_POST['reason'] ?? '');
  $resident  = isset($_POST['resident_id']) ? intval($_POST['resident_id']) : null;
  $okActions = ['Ready','Completed','Rejected'];

  if (!$table || !in_array($action, $okActions, true)) {
    http_response_code(400);
    echo "Invalid params";
    exit;
  }

  // Update status
  $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $action, $id);
  $stmt->execute();
  $changed = $stmt->affected_rows > 0;
  $stmt->close();

  // If Rejected + may reason + may resident_id => create notification
if ($changed && $action === 'Rejected' && $reason !== '' && $resident) {
  $q = $conn->prepare("SELECT barangay_name, permit_type FROM $table WHERE id=?");
  $q->bind_param("i", $id);
  $q->execute();
  $r = $q->get_result()->fetch_assoc();
  $q->close();

  if ($r) {
    $notif = $conn->prepare("
      INSERT INTO notifications 
      (barangay_name, recipient_type, recipient_id, source_table, source_id, type, title, message, link)
      VALUES (?, 'resident', ?, ?, ?, 'request_declined', ?, ?, ?)
    ");
    $title = "Your {$r['permit_type']} request was declined";
    $msg   = "Reason: " . $reason;
    $link  = "/Resident/requests.php";
    $notif->bind_param("sisssss", 
      $r['barangay_name'], 
      $resident, 
      $table, 
      $id, 
      $title, 
      $msg, 
      $link
    );
    $notif->execute();
    $notif->close();
  }
}

// ✅ If Completed => notify resident (auto-fetch resident_id if missing)
if ($changed && $action === 'Completed') {
  // 🟢 step 1: kung walang naipasa na resident_id, kunin ulit sa table
  if (!$resident) {
    $check = $conn->prepare("SELECT resident_id FROM $table WHERE id=?");
    $check->bind_param("i", $id);
    $check->execute();
    $resRow = $check->get_result()->fetch_assoc();
    $check->close();
    $resident = $resRow['resident_id'] ?? null;
  }

  // 🟢 step 2: kung may resident na, mag-insert ng notification
  if ($resident) {
    $q = $conn->prepare("SELECT barangay_name, permit_type FROM $table WHERE id=?");
    $q->bind_param("i", $id);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $q->close();

    if ($r) {
      $notif = $conn->prepare("
        INSERT INTO notifications 
        (barangay_name, recipient_type, recipient_id, source_table, source_id, type, title, message, link)
        VALUES (?, 'resident', ?, ?, ?, 'request_completed', ?, ?, ?)
      ");
      $title = "Your {$r['permit_type']} request is now completed!";
      $msg   = "You may now claim your document at the barangay office.";
      $link  = "/Resident/requests.php";
      $notif->bind_param("sisssss", 
        $r['barangay_name'], 
        $resident, 
        $table, 
        $id, 
        $title, 
        $msg, 
        $link
      );
      $notif->execute();
      $notif->close();
    }
  }
}

  echo $changed ? "OK" : "NOCHANGE";
  exit;
}

/* ================================
   NORMAL PAGE LOAD
================================ */
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

$barangay = $_SESSION['sg_brgy'] ?? '';

/* Build UNION for all tables */
$unionParts = [];
$params     = [];
$types      = '';
foreach ($allowed_tables as $tbl => $label) {
  // Check if table is business_permit_requests (uses owner_name instead of fullname)
  if ($tbl === 'business_permit_requests') {
    $nameField = "r.owner_name";
  } else {
    $nameField = "r.fullname";
  }

  $unionParts[] = "
    SELECT 
      '$tbl' AS table_name,
      r.id,
      r.resident_id,
      COALESCE(CONCAT(res.first_name,' ',res.last_name), $nameField) AS fullname,
      r.email,
      r.barangay_name,
      r.status,
      r.created_at,
      COALESCE(r.permit_type, '$label') AS document_type
    FROM $tbl r
    LEFT JOIN residents res ON r.resident_id = res.id
    WHERE r.barangay_name = ?
  ";
  $params[] = $barangay;
  $types .= 's';
}

$sql = implode(" UNION ALL ", $unionParts) . " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Resident Requests Dashboard</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root{
  --bg:#f9fafb;--card:#fff;--text:#222;--muted:#6b7280;--border:#e5e7eb;
  --green:#16a34a;--red:#ef4444;--gray:#e5e7eb;--radius:20px;--shadow:0 2px 8px rgba(0,0,0,.05);
}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);}
.layout{display:flex;flex-direction:column;min-height:100vh;}
.main-content{padding:24px;}
.dashboard-header{display:flex;align-items:center;justify-content:space-between;
  background:var(--card);padding:14px 20px;border:1px solid var(--border);
  border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;flex-wrap:wrap;}
.dashboard-header-left{display:flex;align-items:center;gap:14px;}
.dashboard-header img{width:46px;height:46px;border-radius:10px;}
.dashboard-title{font-size:1.4rem;font-weight:700;color:var(--green);}
.search-box{position:relative;}
.search-box input{padding:8px 12px 8px 38px;border:1px solid var(--border);
  border-radius:10px;font-size:.95rem;}
.search-box i{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:18px;}
.filter-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;}
.filter-tab{all:unset;cursor:pointer;padding:8px 16px;border:1px solid var(--border);
  border-radius:999px;background:#f3f4f6;color:var(--green);font-weight:600;font-size:.9rem;}
.filter-tab.active{background:var(--green);color:#fff;border:none;}
.requests-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;}
.request-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:18px;box-shadow:var(--shadow);transition:.2s;}
.request-card:hover{transform:translateY(-2px);}
.request-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;}
.resident-name{font-weight:700;color:var(--text);font-size:1.05rem;}
.request-type{background:#f1f5f9;color:var(--green);font-weight:600;padding:4px 10px;border-radius:999px;font-size:.8rem;}
.status-badge{padding:4px 10px;border-radius:999px;color:#fff;font-size:.8rem;font-weight:600;display:inline-block;margin-top:6px;}
.status-Pending{background:#f59e0b;}
.status-Ready{background:#16a34a;}
.status-Completed{background:#3b82f6;}
.status-Rejected{background:#ef4444;}
.date{color:var(--muted);font-size:.9rem;margin-top:8px;display:flex;align-items:center;gap:4px;}
.request-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;}
.btn{all:unset;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;
  padding:8px 16px;border-radius:8px;font-weight:600;font-size:.9rem;border:1px solid transparent;}
.print-btn{background:var(--green);color:#fff;}
.decline-btn{background:var(--red);color:#fff;}
.ready-btn{background:#22c55e;color:#fff;}
.done-btn{background:#3b82f6;color:#fff;}
.view-btn{background:var(--gray);color:#111;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:22px;width:95%;max-width:520px;}
.modal h3{margin-top:0;color:var(--green);}
.modal textarea{width:100%;height:110px;border-radius:10px;border:1px solid var(--border);padding:10px;}
.modal-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px;}
.no-requests{text-align:center;color:var(--muted);margin-top:40px;}
.view-modal .row{display:flex;gap:8px;margin:6px 0;flex-wrap:wrap;}
.view-modal .lbl{min-width:160px;color:#6b7280;font-weight:600;}
.view-modal img{max-width:100%;border:1px solid var(--border);border-radius:10px;margin-top:4px;}
</style>
</head>
<body>
<div class="layout">
  <main class="main-content">
    <div class="dashboard-container">
      <div class="dashboard-header">
        <div class="dashboard-header-left">
          <img src="B.png" alt="Barangay Logo">
          <div class="dashboard-title">Resident Requests Dashboard</div>
        </div>
        <div class="search-box">
          <i class='bx bx-search'></i>
          <input type="text" id="searchInput" placeholder="Search requests...">
        </div>
      </div>

      <nav class="filter-tabs">
        <button class="filter-tab active" data-filter="all">All</button>
        <button class="filter-tab" data-filter="Completed">Completed</button>
        <button class="filter-tab" data-filter="Rejected">Declined</button>
        <?php foreach ($allowed_tables as $t => $lbl): ?>
          <button class="filter-tab" data-filter="<?=htmlspecialchars($lbl)?>"><?=htmlspecialchars($lbl)?></button>
        <?php endforeach; ?>
      </nav>

      <section id="requestsList" class="requests-list">
        <?php if(empty($requests)): ?>
          <div class="no-requests"><i class='bx bx-user-x' style="font-size:2rem;"></i><br>No requests found.</div>
        <?php else: foreach($requests as $r): ?>
          <div class="request-card"
               data-status="<?=$r['status']?>"
               data-type="<?=$r['document_type']?>"
               data-id="<?=$r['id']?>"
               data-table="<?=$r['table_name']?>"
               data-resident="<?=$r['resident_id']?>">
            <div class="request-header">
              <span class="resident-name"><i class='bx bx-user'></i> <?=htmlspecialchars($r['fullname'] ?: '—')?></span>
              <span class="request-type"><?=htmlspecialchars($r['document_type'])?></span>
            </div>
            <div class="date"><i class='bx bx-calendar'></i><?=date("M d, Y",strtotime($r['created_at']))?></div>
            <span class="status-badge status-<?=$r['status']?>"><?=$r['status']?></span>
            <div class="request-actions">
              <?php if($r['status']==='Pending'): ?>
                <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
                <button class="btn decline-btn" onclick="openReject(this)"><i class='bx bx-x'></i> Decline</button>
                <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>
              <?php elseif($r['status']==='Ready'): ?>
                <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
                <button class="btn ready-btn" onclick="markDone(this)"><i class='bx bx-check'></i> Mark as Done</button>
                <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>
              <?php else: ?>
                <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </section>
    </div>
  </main>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-bg">
  <form class="modal" onsubmit="submitReject(event)">
    <h3>Decline Request</h3>
    <input type="hidden" id="rej_id">
    <input type="hidden" id="rej_table">
    <input type="hidden" id="rej_resident">
    <label>Reason:</label>
    <textarea id="rej_reason" required placeholder="Enter reason for decline..."></textarea>
    <div class="modal-actions">
      <button type="button" class="btn view-btn" onclick="closeReject()">Cancel</button>
      <button type="submit" class="btn decline-btn">Confirm Decline</button>
    </div>
  </form>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal-bg">
  <div class="modal view-modal" style="max-width:680px">
    <h3>Resident Request Details</h3>
    <div id="viewBody"></div>
    <div class="modal-actions">
      <button class="btn view-btn" onclick="closeView()">Close</button>
    </div>
  </div>
</div>

<script>
/* ========== Filters ========== */
document.querySelectorAll('.filter-tab').forEach(tab => {
  tab.onclick = () => {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const f = tab.dataset.filter;

    const cards = document.querySelectorAll('.request-card');
    let shown = 0;
    cards.forEach(c => {
      const s = c.dataset.status;
      const t = c.dataset.type;
      let show = false;

      if (f === 'all') {
        show = (s !== 'Completed' && s !== 'Rejected');
      } else if (f === 'Completed') {
        show = (s === 'Completed');
      } else if (f === 'Rejected') {
        show = (s === 'Rejected');
      } else {
        show = (t === f && s !== 'Completed' && s !== 'Rejected');
      }

      c.style.display = show ? 'block' : 'none';
      if (show) shown++;
    });

    toggleNoRequests(shown === 0);
  };
});

/* 🟢 Auto-run the filter for "All" when page first loads */
window.addEventListener('DOMContentLoaded', () => {
  const defaultTab = document.querySelector('.filter-tab[data-filter="all"]');
  if (defaultTab) defaultTab.click();
});


function toggleNoRequests(show) {
  let n = document.querySelector('.no-requests');
  if (!n) {
    n = document.createElement('div');
    n.className = 'no-requests';
    n.innerHTML = "<i class='bx bx-user-x' style='font-size:2rem;'></i><br>No requests found.";
    document.getElementById('requestsList').appendChild(n);
  }
  n.style.display = show ? 'block' : 'none';
}

/* ========== Search ========== */
document.getElementById('searchInput').oninput = e => {
  const q = e.target.value.toLowerCase();
  const cards = document.querySelectorAll('.request-card');
  let shown = 0;
  cards.forEach(c => {
    const text = c.textContent.toLowerCase();
    const visible = text.includes(q);
    c.style.display = visible ? 'block' : 'none';
    if (visible) shown++;
  });
  toggleNoRequests(shown === 0);
};

/* ========== Reject Flow ========== */
function openReject(btn) {
  const card = btn.closest('.request-card');
  document.getElementById('rej_id').value = card.dataset.id;
  document.getElementById('rej_table').value = card.dataset.table;
  document.getElementById('rej_resident').value = card.dataset.resident || '';
  document.getElementById('rejectModal').classList.add('active');
}
function closeReject() {
  document.getElementById('rejectModal').classList.remove('active');
}
function submitReject(e) {
  e.preventDefault();
  const id       = document.getElementById('rej_id').value;
  const table    = document.getElementById('rej_table').value;
  const resident = document.getElementById('rej_resident').value;
  const reason   = document.getElementById('rej_reason').value.trim();
  if (!reason) return;

  const f = new FormData();
  f.append('id', id);
  f.append('table', table);
  f.append('action', 'Rejected');
  f.append('reason', reason);
  if (resident) f.append('resident_id', resident);

  fetch('', { method: 'POST', body: f })
    .then(r => r.text())
    .then(() => location.reload());
}

/* ========== Print → Ready → open print_template.php ========== */
function printRequest(btn) {
  const card  = btn.closest('.request-card');
  const id    = card.dataset.id;
  const table = card.dataset.table;

  // Mark Ready in DB
  const f = new FormData();
  f.append('id', id);
  f.append('table', table);
  f.append('action', 'Ready');

  fetch('', { method: 'POST', body: f })
    .then(() => {
      // Update UI
      card.dataset.status = 'Ready';
      const badge = card.querySelector('.status-badge');
      badge.textContent = 'Ready';
      badge.className   = 'status-badge status-Ready';

      // Show Done button if not there yet
      const actions = card.querySelector('.request-actions');
      if (!actions.querySelector('.ready-btn')) {
        actions.innerHTML = `
          <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
          <button class="btn ready-btn" onclick="markDone(this)"><i class='bx bx-check'></i> Mark as Done</button>
          <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>`;
      }

      // Open print window
      window.open('print_template.php?table='+encodeURIComponent(table)+'&id='+encodeURIComponent(id), '_blank');
    });
}

/* ========== Mark Completed ========== */
function markDone(btn) {
  const card  = btn.closest('.request-card');
  const id    = card.dataset.id;
  const table = card.dataset.table;
  const resident = card.dataset.resident; // ✅ get resident id

  const f = new FormData();
  f.append('id', id);
  f.append('table', table);
  f.append('action', 'Completed');
  if (resident) f.append('resident_id', resident); // ✅ include this!

  fetch('', { method: 'POST', body: f })
    .then(res => res.text())
    .then(() => {
      card.dataset.status = 'Completed';
      const badge = card.querySelector('.status-badge');
      badge.textContent = 'Completed';
      badge.className   = 'status-badge status-Completed';
      card.style.display = 'none'; // instantly hide from All
    });
}


/* ========== View Modal ========== */
function openView(btn){
  const card  = btn.closest('.request-card');
  const id    = card.dataset.id;
  const table = card.dataset.table;

  fetch(`?view=1&table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}`)
    .then(r => r.json())
    .then(data => {
      const b = document.getElementById('viewBody');
      if (!data || Object.keys(data).length === 0) {
        b.innerHTML = "<div style='color:#ef4444'>No data found.</div>";
      } else {
        // Build rows (common fields first; fallbacks included)
        const safe = v => (v==null || v==='') ? '—' : String(v);
        const lines = [];
        lines.push(row("Full Name",  safe(data.fullname || ((data.first_name||'') + ' ' + (data.last_name||'')))));
        lines.push(row("Email",      safe(data.email)));
        lines.push(row("Phone",      safe(data.phone)));
        lines.push(row("House/Street", safe(data.house_street || (data.house_no ? (data.house_no+' '+(data.street||'')) : '—'))));
        lines.push(row("City",       safe(data.city)));
        lines.push(row("Province",   safe(data.province)));
        lines.push(row("Date of Birth", safe(data.date_of_birth)));
        lines.push(row("Date of Residency", safe(data.date_of_residency)));
        lines.push(row("Years of Residency", safe(data.years_residency)));
        lines.push(row("Purpose",    safe(data.purpose)));
        if (data.valid_id_url) {
          lines.push(`<div class="row"><div class="lbl">Valid ID:</div><div><img src="${data.valid_id_url}" alt="Valid ID"></div></div>`);
        }
        // Table-specific extras
        if (data.business_name)      lines.push(row("Business Name", safe(data.business_name)));
        if (data.business_type)      lines.push(row("Business Type", safe(data.business_type)));
        if (data.school_name)        lines.push(row("School", safe(data.school_name)));
        if (data.proof_of_income_url)lines.push(linkRow("Proof of Income", data.proof_of_income_url));
        if (data.proof_of_solo_status_url) lines.push(linkRow("Solo Parent Proof", data.proof_of_solo_status_url));
        if (data.barangay_clearance_url)   lines.push(linkRow("Barangay Clearance", data.barangay_clearance_url));
        if (data.birth_record_url)         lines.push(linkRow("Birth Record", data.birth_record_url));
        if (data.dti_cert_url)             lines.push(linkRow("DTI Certificate", data.dti_cert_url));
        if (data.lease_contract_url)       lines.push(linkRow("Lease Contract", data.lease_contract_url));

        lines.push(`<hr>`);
        lines.push(row("Status", safe(data.status)));
        lines.push(row("Submitted", safe(data.created_at)));

        b.innerHTML = lines.join('');
      }
      document.getElementById('viewModal').classList.add('active');
    });
}
function closeView(){ document.getElementById('viewModal').classList.remove('active'); }
function row(label, value){ return `<div class="row"><div class="lbl">${label}:</div><div>${value}</div></div>`; }
function linkRow(label, url){ return `<div class="row"><div class="lbl">${label}:</div><div><a href="${url}" target="_blank">${url}</a></div></div>`; }
</script>
</body>
</html>
