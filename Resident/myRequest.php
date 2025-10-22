<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

// 📄 Fetch resident requests
$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? "Unknown Barangay";

// Handle AJAX cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
  $cancel_id = intval($_POST['cancel_id']);

  // Fetch resident info & permit type for message
  $stmt = $conn->prepare("SELECT fullname, permit_type FROM barangay_clearance_requests WHERE id=? AND resident_id=?");
  $stmt->bind_param("ii", $cancel_id, $resident_id);
  $stmt->execute();
  $req = $stmt->get_result()->fetch_assoc();

  if ($req) {
    $fullname = $req['fullname'];
    $permit_type = $req['permit_type'];

    // Update status to cancelled
    $update = $conn->prepare("UPDATE barangay_clearance_requests SET status='Cancelled' WHERE id=? AND resident_id=?");
    $update->bind_param("ii", $cancel_id, $resident_id);
    $update->execute();

    // Notify admin
    $notif = $conn->prepare("INSERT INTO barangay_notifications (barangay_name, message, type, created_at) VALUES (?,?,?,NOW())");
    $msg = "$fullname cancelled their $permit_type request.";
    $type = "cancellation";
    $notif->bind_param("sss", $barangay, $msg, $type);
    $notif->execute();

    echo "success";
  } else {
    echo "not_found";
  }
  exit;
}


// Fetch requests for display
$stmt = $conn->prepare("SELECT * FROM barangay_clearance_requests WHERE resident_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
  --accent:#16a34a;
  --bg:#fff;
  --border:#e5e7eb;
  --brand:#1e40af;
  --muted:#6b7280;
  --radius:14px;
  --text:#1e1e1e;
}

/* Base */
body {
  background:var(--bg);
  color:var(--text);
  font-family:"Parkinsans","Outfit",sans-serif;
  margin:0;
  padding:0;
}

/* Container */
.container-custom {
  max-width:1400px;
  margin:auto;
  padding:40px 6vw 80px;
}

/* Hero */
.hero {
  text-align:left;
  margin-bottom:1.5rem;
}
.hero h1 {
  color:var(--brand);
  font-family:"Outfit";
  font-size:2.2rem;
  font-weight:700;
  margin-bottom:6px;
}
.hero p {
  color:var(--muted);
  font-size:1rem;
  max-width:600px;
  line-height:1.5;
}

/* Tabs */
.request-tabs {
  display:flex;
  gap:0.75rem;
  overflow-x:auto;
  scrollbar-width:none;
  border-bottom:1px solid var(--border);
  padding-bottom:0.5rem;
  margin-bottom:1.5rem;
}
.request-tabs::-webkit-scrollbar {display:none;}
.request-tab {
  background:#f3f4f6;
  border:none;
  border-radius:999px;
  color:var(--muted);
  font-weight:600;
  padding:0.55rem 1.2rem;
  cursor:pointer;
  transition:.2s;
}
.request-tab.active {
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;
}
.request-tab:hover {background:#e5e7eb;}

/* Requests Grid */
.requests-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
  gap:1.25rem;
  justify-content:center;
  margin:auto;
  max-width:85%;
  transition:opacity .28s ease, transform .28s ease;
}
.requests-grid.fading {
  opacity:0;
  transform:translateY(8px);
}

/* Cards */
.request-card {
  background:#fff;
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  padding:1.25rem;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  transition:.2s;
}
.request-card:hover {
  transform:translateY(-2px);
  box-shadow:0 6px 12px rgba(0,0,0,.08);
}
.request-name {
  font-weight:600;
  font-size:1rem;
  margin-bottom:0.25rem;
  display:flex;
  align-items:center;
  gap:6px;
}
.request-type {
  color:var(--accent);
  font-weight:500;
  font-size:0.9rem;
}
.request-actions {
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-top:0.75rem;
}
.status-badge {
  padding:6px 12px;
  border-radius:8px;
  font-size:0.8rem;
  font-weight:600;
  color:#fff;
  text-transform:capitalize;
}
.status-pending {background:#f59e0b;}
.status-ready {background:#0ea5e9;}
.status-declined {background:#ef4444;}
.status-completed {background:#374151;}
.btn-cancel {
  background:#ef4444;
  color:#fff;
  border:none;
  border-radius:8px;
  font-size:0.8rem;
  font-weight:600;
  padding:6px 12px;
  cursor:pointer;
  transition:.2s;
}
.btn-cancel:hover {opacity:.9;}
.request-date {
  color:var(--muted);
  font-size:0.85rem;
  margin-top:0.5rem;
}
.empty {
  text-align:center;
  color:var(--muted);
  padding:1rem;
  border:1px dashed var(--border);
  border-radius:var(--radius);
}

/* Modal */
.modal {
  display:none;
  position:fixed;
  top:0;left:0;
  width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  backdrop-filter:blur(4px);
  z-index:2000;
  align-items:center;
  justify-content:center;
  padding:1rem;
}
.modal.show {display:flex;}
.modal-box {
  background:#fff;
  border-radius:14px;
  padding:1.5rem;
  text-align:center;
  box-shadow:0 8px 24px rgba(0,0,0,0.15);
  max-width:400px;
  width:100%;
}
.modal h3 {color:var(--brand);margin-bottom:1rem;}
.modal-buttons {
  display:flex;
  justify-content:center;
  gap:10px;
  margin-top:1.5rem;
}
.btn {
  border:none;
  border-radius:10px;
  padding:10px 16px;
  font-weight:600;
  cursor:pointer;
}
.btn-danger {
  background:#ef4444;
  color:#fff;
}
.btn-cancel-modal {
  background:#f3f4f6;
  color:var(--text);
}
.btn-danger:hover,.btn-cancel-modal:hover {opacity:0.9;}

/* Footer */
footer {
  background:#fff;
  color:var(--muted);
  font-size:0.9rem;
  padding:20px;
  text-align:center;
}
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
            <?php if ($r['status'] === 'Pending'): ?>
              <button class="btn-cancel" onclick="openCancelModal(<?= $r['id'] ?>)">Cancel</button>
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
let cancelId = null;

function openCancelModal(id){
  cancelId = id;
  document.getElementById('cancelModal').classList.add('show');
}
function closeCancelModal(){
  document.getElementById('cancelModal').classList.remove('show');
  cancelId = null;
}

document.getElementById('confirmCancel').addEventListener('click',()=>{
  if(!cancelId) return;
  fetch('',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`cancel_id=${cancelId}`
  }).then(r=>r.text()).then(res=>{
    if(res.trim()==='success'){
      alert('✅ Request cancelled. The barangay admin has been notified.');
      location.reload();
    } else {
      alert('❌ Failed to cancel request.');
    }
  });
});


const tabs=document.querySelectorAll('.request-tab');
const grid=document.getElementById('requestsGrid');
const cards=Array.from(document.querySelectorAll('.request-card'));
const empty=document.getElementById('emptyState');

tabs.forEach(tab=>{
  tab.addEventListener('click',()=>{
    if(tab.classList.contains('active')) return;
    tabs.forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    applyFilter(tab.dataset.filter,true);
  });
});

function applyFilter(filter,animated){
  if(animated) grid.classList.add('fading');
  setTimeout(()=>{
    let visible=0;
    cards.forEach(card=>{
      const match=(filter==='all'||card.dataset.status===filter);
      card.style.display=match?'flex':'none';
      if(match) visible++;
    });
    if(empty) empty.hidden=visible>0;
    grid.classList.remove('fading');
  },animated?160:0);
}
</script>
</body>
</html>
