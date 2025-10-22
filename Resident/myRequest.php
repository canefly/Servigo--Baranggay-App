<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? "Unknown Barangay";

/* ---------------- Handle AJAX Cancel Request ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'], $_POST['table'])) {
  $cancel_id = intval($_POST['cancel_id']);
  $table     = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']); // sanitize

  $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id=? AND resident_id=?");
  $stmt->bind_param("ii", $cancel_id, $resident_id);
  $stmt->execute();
  $req = $stmt->get_result()->fetch_assoc();

  if ($req) {
    $fullname = $req['fullname'] ?? $req['name'] ?? $req['resident_name'] ?? 'Resident';
    $permit_type = $req['permit_type'] ?? $req['request_type'] ?? 'Request';

    $update = $conn->prepare("UPDATE `$table` SET status='Cancelled' WHERE id=? AND resident_id=?");
    $update->bind_param("ii", $cancel_id, $resident_id);
    $update->execute();

    // Notify admin (if barangay_notifications table exists)
    $notifCheck = $conn->query("SHOW TABLES LIKE 'barangay_notifications'");
    if ($notifCheck->num_rows > 0) {
      $notif = $conn->prepare("
        INSERT INTO barangay_notifications (barangay_name, message, type, created_at)
        VALUES (?,?,?,NOW())
      ");
      $msg = "$fullname cancelled their $permit_type request.";
      $type = "cancellation";
      $notif->bind_param("sss", $barangay, $msg, $type);
      $notif->execute();
    }

    echo "success";
  } else {
    echo "not_found";
  }
  exit;
}

/* ---------------- Fetch All Requests (Safe Version) ---------------- */
$tables = [
  'barangay_clearance_requests',
  'residency_requests',
  'indigency_requests',
  'goodmoral_requests',
  'soloparent_requests',
  'latebirth_requests',
  'norecord_requests',
  'ojt_requests',
  'business_permit_requests'
];

// Verify tables exist
$existing = [];
$resCheck = $conn->query("SHOW TABLES");
while ($row = $resCheck->fetch_array()) $existing[] = $row[0];
$tables = array_intersect($tables, $existing);

$unions = [];
foreach ($tables as $tbl) {
  $colRes = $conn->query("SHOW COLUMNS FROM `$tbl`");
  $cols = [];
  while ($row = $colRes->fetch_assoc()) $cols[] = $row['Field'];

  $name_col = in_array('fullname', $cols) ? 'fullname' :
              (in_array('name', $cols) ? 'name' :
              (in_array('resident_name', $cols) ? 'resident_name' : "'N/A'"));

  $type_col = in_array('permit_type', $cols) ? 'permit_type' :
              (in_array('request_type', $cols) ? 'request_type' : "'Barangay Request'");

  $status_col = in_array('status', $cols) ? 'status' : "'Unknown'";
  $created_col = in_array('created_at', $cols) ? 'created_at' :
                 (in_array('date_requested', $cols) ? 'date_requested' : "NOW()");

  $unions[] = "
    SELECT 
      id, 
      $name_col AS fullname, 
      $type_col AS permit_type, 
      $status_col AS status, 
      $created_col AS created_at, 
      '$tbl' AS source_table
    FROM `$tbl`
    WHERE resident_id = $resident_id
  ";
}

$requests = [];
if (!empty($unions)) {
  $sql = implode(" UNION ALL ", $unions) . " ORDER BY created_at DESC";
  $res = $conn->query($sql);
  if ($res) $requests = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Servigo · My Requests</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root {
  --accent:#16a34a;--bg:#fff;--border:#e5e7eb;
  --brand:#1e40af;--muted:#6b7280;--radius:14px;--text:#1e1e1e;
}
body{background:var(--bg);color:var(--text);font-family:"Parkinsans","Outfit",sans-serif;margin:0;padding:0;}
.container-custom{max-width:1400px;margin:auto;padding:40px 6vw 80px;}
.hero{text-align:left;margin-bottom:1.5rem;}
.hero h1{color:var(--brand);font-family:"Outfit";font-size:2.2rem;font-weight:700;margin-bottom:6px;}
.hero p{color:var(--muted);font-size:1rem;max-width:600px;line-height:1.5;}
.request-tabs{display:flex;gap:0.75rem;overflow-x:auto;scrollbar-width:none;border-bottom:1px solid var(--border);padding-bottom:0.5rem;margin-bottom:1.5rem;}
.request-tabs::-webkit-scrollbar{display:none;}
.request-tab{background:#f3f4f6;border:none;border-radius:999px;color:var(--muted);font-weight:600;padding:0.55rem 1.2rem;cursor:pointer;transition:.2s;}
.request-tab.active{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;}
.request-tab:hover{background:#e5e7eb;}
.requests-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.25rem;justify-content:center;margin:auto;max-width:85%;transition:opacity .28s ease,transform .28s ease;}
.requests-grid.fading{opacity:0;transform:translateY(8px);}
.request-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 2px 8px rgba(0,0,0,.05);padding:1.25rem;display:flex;flex-direction:column;justify-content:space-between;transition:.2s;}
.request-card:hover{transform:translateY(-2px);box-shadow:0 6px 12px rgba(0,0,0,.08);}
.request-name{font-weight:600;font-size:1rem;margin-bottom:0.25rem;display:flex;align-items:center;gap:6px;}
.request-type{color:var(--accent);font-weight:500;font-size:0.9rem;}
.request-actions{display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;}
.status-badge{padding:6px 12px;border-radius:8px;font-size:0.8rem;font-weight:600;color:#fff;text-transform:capitalize;}
.status-pending{background:#f59e0b;}
.status-ready{background:#0ea5e9;}
.status-declined{background:#ef4444;}
.status-cancelled{background:#9ca3af;}
.status-completed{background:#374151;}
.btn-cancel{background:#ef4444;color:#fff;border:none;border-radius:8px;font-size:0.8rem;font-weight:600;padding:6px 12px;cursor:pointer;transition:.2s;}
.btn-cancel:hover{opacity:.9;}
.request-date{color:var(--muted);font-size:0.85rem;margin-top:0.5rem;}
.empty{text-align:center;color:var(--muted);padding:1rem;border:1px dashed var(--border);border-radius:var(--radius);}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);z-index:2000;align-items:center;justify-content:center;padding:1rem;}
.modal.show{display:flex;}
.modal-box{background:#fff;border-radius:14px;padding:1.5rem;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,0.15);max-width:400px;width:100%;}
.modal h3{color:var(--brand);margin-bottom:1rem;}
.modal-buttons{display:flex;justify-content:center;gap:10px;margin-top:1.5rem;}
.btn{border:none;border-radius:10px;padding:10px 16px;font-weight:600;cursor:pointer;}
.btn-danger{background:#ef4444;color:#fff;}
.btn-cancel-modal{background:#f3f4f6;color:var(--text);}
.btn-danger:hover,.btn-cancel-modal:hover{opacity:0.9;}
footer{background:#fff;color:var(--muted);font-size:0.9rem;padding:20px;text-align:center;}
</style>
</head>

<body>
<div class="container-custom">
  <section class="hero">
    <h1>My Barangay Requests</h1>
    <p>Track and manage your submitted barangay requests.</p>
  </section>

  <div class="request-tabs" id="requestTabs">
    <button class="request-tab active" data-filter="all">All</button>
    <button class="request-tab" data-filter="pending">Pending</button>
    <button class="request-tab" data-filter="ready">Ready</button>
    <button class="request-tab" data-filter="declined">Declined</button>
    <button class="request-tab" data-filter="completed">Completed</button>
    <button class="request-tab" data-filter="cancelled">Cancelled</button>
  </div>

  <div class="requests-grid" id="requestsGrid">
    <?php if (empty($requests)): ?>
      <div class="empty" id="emptyState"><i class='bx bx-folder-open' style="font-size:2rem;"></i><br>No requests yet.</div>
    <?php else: ?>
      <?php foreach ($requests as $r): ?>
        <article class="request-card" data-status="<?= strtolower($r['status']) ?>">
          <div class="request-name"><i class='bx bx-user'></i> <?= htmlspecialchars($r['fullname']) ?></div>
          <div class="request-type"><?= htmlspecialchars($r['permit_type']) ?></div>
          <div class="request-actions">
            <span class="status-badge status-<?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
            <?php if (strtolower($r['status']) === 'pending'): ?>
              <button class="btn-cancel" onclick="openCancelModal(<?= $r['id'] ?>,'<?= $r['source_table'] ?>')">Cancel</button>
            <?php endif; ?>
          </div>
          <div class="request-date"><i class='bx bx-calendar'></i> <?= date('F j, Y', strtotime($r['created_at'])) ?></div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Cancel Modal -->
<div class="modal" id="cancelModal">
  <div class="modal-box">
    <h3><i class='bx bx-x-circle' style="color:#ef4444;font-size:1.5rem;"></i><br>Cancel Request?</h3>
    <p>Are you sure you want to cancel this request? This action cannot be undone.</p>
    <div class="modal-buttons">
      <button class="btn btn-danger" id="confirmCancel">Yes, Cancel</button>
      <button class="btn btn-cancel-modal" onclick="closeCancelModal()">No</button>
    </div>
  </div>
</div>

<footer>© 2025 Servigo. All rights reserved.</footer>

<script>
let cancelId=null;let cancelTable=null;
function openCancelModal(id,table){cancelId=id;cancelTable=table;document.getElementById('cancelModal').classList.add('show');}
function closeCancelModal(){document.getElementById('cancelModal').classList.remove('show');cancelId=null;cancelTable=null;}
document.getElementById('confirmCancel').addEventListener('click',()=>{
  if(!cancelId||!cancelTable)return;
  fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`cancel_id=${cancelId}&table=${cancelTable}`})
  .then(r=>r.text()).then(res=>{
    if(res.trim()==='success'){alert('✅ Request cancelled. The barangay admin has been notified.');location.reload();}
    else{alert('❌ Failed to cancel request.');}
  });
});
const tabs=document.querySelectorAll('.request-tab');
const grid=document.getElementById('requestsGrid');
const cards=Array.from(document.querySelectorAll('.request-card'));
const empty=document.getElementById('emptyState');
tabs.forEach(tab=>{
  tab.addEventListener('click',()=>{
    if(tab.classList.contains('active'))return;
    tabs.forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    applyFilter(tab.dataset.filter,true);
  });
});
function applyFilter(filter,animated){
  if(animated)grid.classList.add('fading');
  setTimeout(()=>{
    let visible=0;
    cards.forEach(card=>{
      const match=(filter==='all'||card.dataset.status===filter);
      card.style.display=match?'flex':'none';
      if(match)visible++;
    });
    if(empty)empty.hidden=visible>0;
    grid.classList.remove('fading');
  },animated?160:0);
}
</script>
</body>
</html>
