<?php
require_once(__DIR__ . "/../Database/session-checker.php");
require_once(__DIR__ . "/../Database/connection.php");
requireRole("admin");

// ------------ CONFIG ------------
$barangay = $_SESSION['sg_brgy'] ?? '';
$reviewed_by = $_SESSION['sg_email'] ?? 'admin';

// ------------ AJAX: VERIFY / REJECT ------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: text/plain');

  $action      = $_POST['action'];                   // verify | reject
  $resident_id = isset($_POST['resident_id']) ? intval($_POST['resident_id']) : 0;
  $reason      = trim($_POST['reason'] ?? '');

  if (!$resident_id || !in_array($action, ['verify','reject'], true)) {
    http_response_code(400);
    echo "Invalid params";
    exit;
  }

  // Kunin latest verification row ng resident (optional pero helpful)
  $ver = $conn->prepare("
    SELECT v.id, v.id_type, v.valid_id_url, r.barangay
    FROM residents r
    LEFT JOIN resident_verifications v
      ON v.resident_id = r.id
     AND v.submitted_at = (
        SELECT MAX(v2.submitted_at)
        FROM resident_verifications v2
        WHERE v2.resident_id = r.id
      )
    WHERE r.id = ?
    LIMIT 1
  ");
  $ver->bind_param("i", $resident_id);
  $ver->execute();
  $row = $ver->get_result()->fetch_assoc();
  $ver->close();

  $verif_id   = $row['id'] ?? null; // pwedeng null kung wala pang submission
  $id_type    = $row['id_type'] ?? null;
  $valid_id   = $row['valid_id_url'] ?? null;
  $brgy_name  = $row['barangay'] ?? $barangay;

  // start transaction for safety
  $conn->begin_transaction();
  try {
    if ($action === 'verify') {
      // 1) mark resident verified
      $u1 = $conn->prepare("UPDATE residents SET verification_status='Verified' WHERE id=?");
      $u1->bind_param("i", $resident_id);
      $u1->execute();
      $u1->close();

      // 2) mark latest verification approved (if meron)
      if ($verif_id) {
        $u2 = $conn->prepare("
          UPDATE resident_verifications 
             SET status='Approved', reviewed_by=?, reviewed_at=NOW(), remarks=NULL
           WHERE id=?
        ");
        $u2->bind_param("si", $reviewed_by, $verif_id);
        $u2->execute();
        $u2->close();
      }

      // 3) notification
      $title = "Your account verification is approved";
      $msg   = "Your Servigo account has been verified.";
      $link  = "/Resident/verification.php";
      $n = $conn->prepare("
        INSERT INTO notifications 
          (barangay_name, recipient_type, recipient_id, source_table, source_id, type, title, message, link)
        VALUES (?, 'resident', ?, 'resident_verifications', ?, 'verification_approved', ?, ?, ?)
      ");
      // source_id can be null; use 0 if walang verif row
      $sid = $verif_id ? $verif_id : 0;
      $n->bind_param("siisss", $brgy_name, $resident_id, $sid, $title, $msg, $link);
      $n->execute();
      $n->close();
    }

    if ($action === 'reject') {
      if ($reason === '') {
        throw new Exception("Reason is required");
      }

      // 1) mark resident as Unverified (or keep Pending if may flow ka; using Unverified here)
      $u1 = $conn->prepare("UPDATE residents SET verification_status='Unverified' WHERE id=?");
      $u1->bind_param("i", $resident_id);
      $u1->execute();
      $u1->close();

      // 2) mark latest verification Rejected
      if ($verif_id) {
        $u2 = $conn->prepare("
          UPDATE resident_verifications 
             SET status='Rejected', reviewed_by=?, reviewed_at=NOW(), remarks=?
           WHERE id=?
        ");
        $u2->bind_param("ssi", $reviewed_by, $reason, $verif_id);
        $u2->execute();
        $u2->close();
      }

      // 3) insert to verification_rejects (archive/audit)
      if ($verif_id) {
        $insr = $conn->prepare("
          INSERT INTO verification_rejects (resident_id, id_type, valid_id_url, reason, reviewed_by, rejected_at)
          VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insr->bind_param("issss", $resident_id, $id_type, $valid_id, $reason, $reviewed_by);
        $insr->execute();
        $insr->close();
      }

      // 4) notification
      $title = "Your account verification was rejected";
      $msg   = "Reason: ".$reason;
      $link  = "/Resident/verification.php";
      $n = $conn->prepare("
        INSERT INTO notifications 
          (barangay_name, recipient_type, recipient_id, source_table, source_id, type, title, message, link)
        VALUES (?, 'resident', ?, 'resident_verifications', ?, 'verification_rejected', ?, ?, ?)
      ");
      $sid = $verif_id ? $verif_id : 0;
      $n->bind_param("siisss", $brgy_name, $resident_id, $sid, $title, $msg, $link);
      $n->execute();
      $n->close();
    }

    $conn->commit();
    echo "OK";
  } catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo "ERR: ".$e->getMessage();
  }
  exit;
}

// ------------ NORMAL PAGE LOAD: DATA ------------
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

// Pull residents scoped by barangay + latest verification (if any)
$rows = [];
if ($barangay) {
  $q = $conn->prepare("
    SELECT 
      r.id AS resident_id, r.last_name, r.first_name, r.middle_name, r.suffix,
      r.birthdate, r.house_no, r.street, r.purok, r.subdivision, r.barangay,
      r.city, r.province, r.region, r.postal, r.nationality, r.verification_status,
      v.id AS verif_id, v.id_type, v.valid_id_url, v.status AS v_status, v.submitted_at
    FROM residents r
    LEFT JOIN resident_verifications v
      ON v.resident_id = r.id
     AND v.submitted_at = (
        SELECT MAX(v2.submitted_at)
        FROM resident_verifications v2
        WHERE v2.resident_id = r.id
      )
    WHERE r.barangay = ?
    ORDER BY r.created_at DESC
  ");
  $q->bind_param("s", $barangay);
  $q->execute();
  $rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
  $q->close();
}

// split to unverified vs verified (based sa residents.verification_status)
$unverified = [];
$verified   = [];
foreach ($rows as $r) {
  $card = [
    'resident_id' => (int)$r['resident_id'],
    'last_name'   => $r['last_name'],
    'first_name'  => $r['first_name'],
    'birthdate'   => $r['birthdate'],
    'house_no'    => $r['house_no'],
    'street'      => $r['street'],
    'purok'       => $r['purok'],
    'subdivision' => $r['subdivision'],
    'barangay'    => $r['barangay'],
    'city'        => $r['city'],
    'province'    => $r['province'],
    'region'      => $r['region'],
    'postal'      => $r['postal'],
    'nationality' => $r['nationality'],
    'id_type'     => $r['id_type'],
    'valid_id_url'=> $r['valid_id_url'],
    'verif_id'    => $r['verif_id'],
  ];

  if (strtolower($r['verification_status']) === 'verified') {
    $verified[] = $card;
  } else {
    // treat anything else as Unverified (includes 'Pending', 'Unverified')
    $unverified[] = $card;
  }
}

// pass to JS
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Resident Verification · Servigo</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
<style>
:root {
  --bg:#f5f7fa; --card:#ffffff;
  --text:#222; --muted:#6b7280; --border:#e5e7eb;
  --brand:#047857; --accent:#10b981;
  --declined:#ef4444;
  --radius:14px; --shadow:0 2px 8px rgba(0,0,0,.08);
  --sidebar-width:240px;
}
*{box-sizing:border-box;}
body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);}
.layout{display:flex;min-height:100vh;}
.main-content{flex:1;padding:16px;transition:margin-left .3s ease;width:100%;}
@media(min-width:1024px){.main-content{margin-left:var(--sidebar-width);}}

/* Header */
.dashboard-header{
  display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px;
  background:var(--card);border:1px solid var(--border);padding:14px 20px;
  border-radius:var(--radius);box-shadow:var(--shadow);flex-wrap:wrap;
}
.dashboard-header-left{display:flex;align-items:center;gap:14px;}
.dashboard-header img{height:48px;width:48px;border-radius:10px;object-fit:cover;}
.dashboard-title{font-size:1.4rem;font-weight:700;color:var(--brand);}

/* Tabs */
.filter-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
.filter-tab{
  all:unset;cursor:pointer;padding:8px 18px;border-radius:999px;font-weight:600;font-size:.95rem;
  border:1px solid var(--border);background:#f3f4f6;color:var(--brand);transition:.2s;
}
.filter-tab.active{
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;border:none;box-shadow:0 2px 6px rgba(0,0,0,.1);
}

/* Grid + Cards */
.residents-list{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;
}
.resident-card{
  position:relative;
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  box-shadow:var(--shadow);padding:16px;display:flex;flex-direction:column;gap:10px;
}
.resident-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);}
.resident-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;}
.resident-name{font-weight:700;font-size:1.05rem;color:var(--text);}
.toggle-btn{
  all:unset;cursor:pointer;padding:6px 14px;border-radius:8px;
  background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;
  font-weight:600;font-size:.85rem;display:flex;align-items:center;gap:6px;
}

/* floating details panel */
.resident-body{
  position:absolute; top:0; left:0; right:0; background:var(--card);
  border:1px solid var(--border); border-radius:var(--radius);
  box-shadow:0 4px 12px rgba(0,0,0,.2); padding:16px; display:none; z-index:10;
}
.resident-card.open .resident-body{display:block;}

.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;margin-top:10px;}
.field{background:#f9fafb;border:1px solid var(--border);padding:8px;border-radius:8px;font-size:.9rem;}
.id-section{margin-top:14px;}
.id-preview img{max-width:100%;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);}

.actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;}
.btn{all:unset;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;
  padding:8px 16px;border-radius:12px;font-weight:700;font-size:.9rem;box-shadow:0 2px 8px rgba(0,0,0,.07);}
.btn.verify{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;}
.btn.reject{background:var(--declined);color:#fff;}

.no-residents{text-align:center;color:var(--muted);font-size:1rem;margin-top:30px;}
@media(max-width:768px){.dashboard-title{font-size:1.2rem;}.btn{flex:1;}}

/* Reject Modal */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:22px;width:95%;max-width:520px;}
.modal h3{margin-top:0;color:var(--brand);}
.modal textarea{width:100%;height:110px;border-radius:10px;border:1px solid var(--border);padding:10px;}
.modal-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px;}
</style>
</head>
<body>
<div class="layout">
  <main class="main-content">
    <div class="dashboard-container">
      <div class="dashboard-header">
        <div class="dashboard-header-left">
          <img src="B.png" alt="Barangay Logo">
          <div class="dashboard-title">Resident Verification</div>
        </div>
      </div>

      <nav class="filter-tabs">
        <button class="filter-tab active" id="tab-unverified">Unverified</button>
        <button class="filter-tab" id="tab-verified">Verified</button>
      </nav>

      <section id="residentsList" class="residents-list"></section>
      <div id="noResidents" class="no-residents" style="display:none;">
        <i class='bx bx-user-x' style="font-size:2rem;"></i><br>
        No residents in this list.
      </div>
    </div>
  </main>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-bg">
  <form class="modal" onsubmit="submitReject(event)">
    <h3>Reject Verification</h3>
    <input type="hidden" id="rej_resident">
    <label>Reason:</label>
    <textarea id="rej_reason" required placeholder="Enter reason for rejection..."></textarea>
    <div class="modal-actions">
      <button type="button" class="btn" onclick="closeReject()">Cancel</button>
      <button type="submit" class="btn reject">Confirm Reject</button>
    </div>
  </form>
</div>

<script>
// ====== Data from PHP ======
const UNVERIFIED = <?php echo json_encode($unverified, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
const VERIFIED   = <?php echo json_encode($verified,   JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;

let unverified = [...UNVERIFIED];
let verified   = [...VERIFIED];
let activeTab  = "unverified";

// ====== Rendering ======
function render(){
  const list=document.getElementById("residentsList"); list.innerHTML="";
  const data = activeTab==="unverified" ? unverified : verified;
  document.getElementById("noResidents").style.display = data.length ? "none" : "block";

  data.forEach(r=>{
    const card=document.createElement("div");
    card.className="resident-card";
    card.dataset.resident=r.resident_id;

    card.innerHTML=`
      <div class="resident-header">
        <span class="resident-name"><i class='bx bx-user'></i> ${safe(r.last_name)}, ${safe(r.first_name)}</span>
        <button class="toggle-btn"><i class='bx bx-show'></i> Show Details</button>
      </div>
      <div class="resident-body">
        <div class="resident-header" style="justify-content:space-between;margin-bottom:10px;">
          <span class="resident-name"><i class='bx bx-user'></i> ${safe(r.last_name)}, ${safe(r.first_name)}</span>
          <button class="toggle-btn close"><i class='bx bx-x'></i> Close</button>
        </div>

        <div class="info-grid">
          <div class="field">DOB: ${safe(r.birthdate)}</div>
          <div class="field">House No: ${safe(r.house_no)}</div>
          <div class="field">Street: ${safe(r.street)}</div>
          <div class="field">Purok: ${safe(r.purok)}</div>
          <div class="field">Subdivision: ${safe(r.subdivision) || "-"}</div>
          <div class="field">Barangay: ${safe(r.barangay)}</div>
          <div class="field">Municipality/City: ${safe(r.city)}</div>
          <div class="field">Province: ${safe(r.province)}</div>
          <div class="field">Postal: ${safe(r.postal)}</div>
          <div class="field">Region: ${safe(r.region)}</div>
          <div class="field">Nationality: ${safe(r.nationality)}</div>
          <div class="field">ID Type: ${safe(r.id_type) || "—"}</div>
        </div>

        <div class="id-section">
          <div class="id-preview">
            ${r.valid_id_url ? `<img src="${r.valid_id_url}" alt="Valid ID">` : `<div style="color:#6b7280">No ID submitted yet.</div>`}
          </div>
        </div>

        ${activeTab==="unverified" ? `
        <div class="actions">
          <button class="btn verify"><i class='bx bx-check'></i> Verify</button>
          <button class="btn reject"><i class='bx bx-x'></i> Reject</button>
        </div>` : ``}
      </div>
    `;

    const toggle = card.querySelector(".toggle-btn:not(.close)");
    const closeBtn = card.querySelector(".toggle-btn.close");
    const body = card.querySelector(".resident-body");

    toggle.onclick = ()=>{
      document.querySelectorAll(".resident-card.open").forEach(c=>{
        c.classList.remove("open");
        c.querySelector(".resident-body").style.display="none";
      });
      card.classList.add("open");
      body.style.display="block";
    };
    closeBtn.onclick = ()=>{card.classList.remove("open");body.style.display="none";};

    body.addEventListener("click", (e)=>{
      if (e.target.closest(".btn.verify")) verifyResident(r.resident_id, card);
      if (e.target.closest(".btn.reject")) openReject(r.resident_id);
    });

    list.appendChild(card);
  });
}

function safe(v){ return (v===null || v===undefined || v==='') ? '—' : String(v); }

// ====== Tabs ======
document.getElementById("tab-unverified").onclick=()=>{activeTab="unverified";setActiveTab();};
document.getElementById("tab-verified").onclick=()=>{activeTab="verified";setActiveTab();};
function setActiveTab(){
  document.querySelectorAll(".filter-tab").forEach(t=>t.classList.remove("active"));
  document.getElementById("tab-"+activeTab).classList.add("active");
  render();
}

// ====== Verify / Reject ======
function verifyResident(residentId, card){
  const f = new FormData();
  f.append('action','verify');
  f.append('resident_id', residentId);

  fetch('', { method:'POST', body:f })
    .then(r=>r.text())
    .then(txt=>{
      // optimistic UI
      unverified = unverified.filter(x=>x.resident_id !== residentId);
      const found = [...UNVERIFIED, ...VERIFIED].find(x=>x.resident_id===residentId) || null;
      if (found) verified.push(found); // push a copy
      render();
    });
}

function openReject(residentId){
  document.getElementById('rej_resident').value = residentId;
  document.getElementById('rejectModal').classList.add('active');
}
function closeReject(){
  document.getElementById('rejectModal').classList.remove('active');
}
function submitReject(e){
  e.preventDefault();
  const residentId = document.getElementById('rej_resident').value;
  const reason = document.getElementById('rej_reason').value.trim();
  if (!reason) return;

  const f = new FormData();
  f.append('action','reject');
  f.append('resident_id', residentId);
  f.append('reason', reason);

  fetch('', { method:'POST', body:f })
    .then(r=>r.text())
    .then(txt=>{
      // optimistic UI
      unverified = unverified.filter(x=>x.resident_id !== +residentId);
      closeReject();
      setActiveTab(); // re-render
    });
}

setActiveTab(); // initial render
</script>
</body>
</html>
