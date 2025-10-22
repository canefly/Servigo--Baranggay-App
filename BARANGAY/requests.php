<?php
require_once(__DIR__ . "/../Database/session-checker.php");
require_once(__DIR__ . "/../Database/connection.php");
requireRole("admin");
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

/* =========================
   SESSION / CONFIG
========================= */
$barangay = $_SESSION['sg_brgy'] ?? '';
if ($barangay === '') { die("Barangay not found in session."); }

/* If print_template.php sits one directory up from this file (Barangay/…): */
$print_template_href = "../print_template.php"; // change to "print_template.php" if same folder

/* =========================
   TABLE MAP + WHITELIST
========================= */
$tables = [
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
$allowed_tables = array_keys($tables);

function is_allowed_table(string $t, array $allowed): bool {
  return in_array($t, $allowed, true);
}

/* =========================================================
   1) DETAILS ENDPOINT (GET)  /?details=1&table=...&id=...
   - Returns JSON with full row + joined resident info
========================================================= */
if (isset($_GET['details']) && $_GET['details'] === '1') {
  header('Content-Type: application/json');

  $table = $_GET['table'] ?? '';
  $id    = intval($_GET['id'] ?? 0);

  if (!is_allowed_table($table, $allowed_tables) || $id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid parameters']);
    exit;
  }

  // Only fetch if the request belongs to this barangay AND resident lives in this barangay
  $sql = "
    SELECT 
      r.*,
      res.id AS resident_pk,
      res.first_name, res.last_name,
      res.email   AS resident_email,
      res.phone   AS resident_phone,
      res.barangay AS resident_barangay,
      res.city     AS resident_city,
      res.province AS resident_province
    FROM {$table} r
    JOIN residents res ON r.resident_id = res.id
    WHERE r.id = ? AND r.barangay_name = ? AND res.barangay = ?
    LIMIT 1
  ";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB prepare error']);
    exit;
  }
  $stmt->bind_param("iss", $id, $barangay, $barangay);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();

  if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Request not found']);
    exit;
  }

  // Prefer request.fullname if present (per your column list for some tables)
  $fullname = '';
  if (!empty($row['fullname'])) {
    $fullname = $row['fullname'];
  } else {
    $fullname = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
  }

  echo json_encode([
    'ok'    => true,
    'label' => $tables[$table],
    'table' => $table,
    'data'  => $row,
    'resident' => [
      'name'     => $fullname ?: 'Unknown',
      'email'    => $row['resident_email'] ?? ($row['email'] ?? null),
      'phone'    => $row['resident_phone'] ?? ($row['phone'] ?? null),
      'barangay' => $row['resident_barangay'] ?? null,
      'city'     => $row['resident_city'] ?? null,
      'province' => $row['resident_province'] ?? null
    ]
  ]);
  exit;
}

/* =========================================================
   2) MUTATIONS (POST)  
   action = Rejected | Printed | Completed
   - Updates status only (your request tables use `status`)
   - No remarks column required
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $id     = intval($_POST['id'] ?? 0);
  $table  = $_POST['table'] ?? '';
  $action = $_POST['action'] ?? '';
  $reason = $_POST['reason'] ?? null;

  $valid_actions = ['Rejected', 'Printed', 'Completed'];
  if ($id <= 0 || !is_allowed_table($table, $allowed_tables) || !in_array($action, $valid_actions, true)) {
    http_response_code(400); echo "ERR"; exit;
  }
  if ($action === 'Rejected' && (!isset($reason) || trim($reason) === '')) {
    http_response_code(400); echo "ERR_REASON_REQUIRED"; exit;
  }

  // Ensure the row belongs to the same barangay and resident is in same barangay
  $chk = $conn->prepare("SELECT r.id 
                         FROM {$table} r 
                         JOIN residents res ON r.resident_id = res.id
                         WHERE r.id=? AND r.barangay_name=? AND res.barangay=? LIMIT 1");
  if (!$chk) { http_response_code(500); echo "ERR_DB"; exit; }
  $chk->bind_param("iss", $id, $barangay, $barangay);
  $chk->execute();
  $exists = $chk->get_result()->fetch_assoc();
  $chk->close();
  if (!$exists) { http_response_code(404); echo "ERR_NOT_FOUND"; exit; }

  $stmt = $conn->prepare("UPDATE {$table} SET status=? WHERE id=? AND barangay_name=?");
  if (!$stmt) { http_response_code(500); echo "ERR_DB"; exit; }
  $stmt->bind_param("sis", $action, $id, $barangay);
  $ok = $stmt->execute();
  $stmt->close();

  if (!$ok) { http_response_code(500); echo "ERR_DB"; exit; }

  echo "OK";
  exit;
}

/* =========================================================
   3) DASHBOARD QUERY (UNION ALL TABLES)
   - Only same-barangay requests where resident lives in same barangay
========================================================= */
$union = [];
foreach ($tables as $tbl => $label) {
  $union[] = "
    SELECT 
      '$tbl' AS table_name,
      r.id,
      r.resident_id,
      CONCAT(res.first_name, ' ', res.last_name) AS fullname,
      COALESCE(r.email, res.email) AS email,
      r.barangay_name,
      r.status,
      r.created_at,
      '$label' AS document_type
    FROM $tbl r
    JOIN residents res ON r.resident_id = res.id
    WHERE r.barangay_name = ? AND res.barangay = ?
  ";
}
$sql = implode(' UNION ALL ', $union) . ' ORDER BY created_at DESC';

$stmt = $conn->prepare($sql);
if (!$stmt) { die('DB error preparing union.'); }

$bindCount = count($tables);
$params = [];
for ($i=0; $i<$bindCount; $i++) {
  $params[] = $barangay; // for r.barangay_name
  $params[] = $barangay; // for res.barangay
}
$stmt->bind_param(str_repeat('s', $bindCount * 2), ...$params);
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
.search-box i{position:absolute;left:10px;top:50%;transform:translateY(-50%);
  color:var(--muted);font-size:18px;}
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
.request-type{background:#f1f5f9;color:var(--green);font-weight:600;
  padding:4px 10px;border-radius:999px;font-size:.8rem;}
.status-badge{padding:4px 10px;border-radius:999px;color:#fff;font-size:.8rem;font-weight:600;}
.status-Pending{background:#f59e0b;}
.status-Printed{background:#15803d;} /* printed state */
.status-Completed{background:#3b82f6;}
.status-Rejected{background:#ef4444;}
.date{color:var(--muted);font-size:.9rem;margin-bottom:10px;display:flex;align-items:center;gap:4px;}
.request-actions{display:flex;gap:8px;flex-wrap:wrap;}
.btn{all:unset;cursor:pointer;display:flex;align-items:center;justify-content:center;
  gap:6px;padding:8px 16px;border-radius:8px;font-weight:600;font-size:.9rem;border:1px solid transparent;}
.print-btn{background:var(--green);color:#fff;}
.print-btn:hover{opacity:.9;}
.decline-btn{background:var(--red);color:#fff;}
.decline-btn:hover{opacity:.9;}
.ready-btn{background:#22c55e;color:#fff;}
.view-btn{background:var(--gray);color:#111;}
.view-btn:hover{background:#d1d5db;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);
  z-index:999;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:22px;width:95%;max-width:720px;max-height:85vh;overflow:auto;}
.modal h3{margin-top:0;color:var(--green);}
.modal textarea{width:100%;height:90px;border-radius:10px;border:1px solid var(--border);padding:10px;}
.modal-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px;}
.no-requests{text-align:center;color:var(--muted);margin-top:40px;}
@media print { body{background:#fff} .modal{box-shadow:none;border:none} .no-print{display:none !important} }
.toast{position:fixed;bottom:20px;right:20px;background:#111;color:#fff;padding:10px 16px;border-radius:10px;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,.2);z-index:2000}
.toast.ok{background:#16a34a}
.toast.err{background:#ef4444}
</style>
</head>
<body data-print-href="<?= htmlspecialchars($print_template_href) ?>">
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
          <input type="text" id="searchInput" placeholder="Search by name, email, or type...">
        </div>
      </div>

      <nav class="filter-tabs">
        <button class="filter-tab active" data-filter="all">All</button>
        <button class="filter-tab" data-filter="Completed">Completed</button>
        <button class="filter-tab" data-filter="Rejected">Declined</button>
        <?php foreach ($tables as $tbl => $label): ?>
          <button class="filter-tab" data-filter="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></button>
        <?php endforeach; ?>
      </nav>

      <section id="requestsList" class="requests-list">
        <?php if(empty($requests)): ?>
          <div class="no-requests"><i class='bx bx-user-x' style="font-size:2rem;"></i><br>No requests found.</div>
        <?php else: foreach($requests as $r): ?>
          <div class="request-card"
               data-status="<?=htmlspecialchars($r['status'])?>"
               data-type="<?=htmlspecialchars($r['document_type'])?>"
               data-id="<?=intval($r['id'])?>"
               data-table="<?=htmlspecialchars($r['table_name'])?>"
               data-name="<?=htmlspecialchars($r['fullname'] ?? '')?>"
               data-email="<?=htmlspecialchars($r['email'] ?? '')?>">
            <div class="request-header">
              <span class="resident-name"><i class='bx bx-user'></i> <?=htmlspecialchars($r['fullname'] ?: 'Unknown')?></span>
              <span class="request-type"><?=htmlspecialchars($r['document_type'])?></span>
            </div>
            <div class="date"><i class='bx bx-calendar'></i><?=htmlspecialchars(date("M d, Y",strtotime($r['created_at'])))?></div>
            <span class="status-badge status-<?=htmlspecialchars($r['status'])?>"><?=htmlspecialchars($r['status'])?></span>
            <div class="request-actions" style="margin-top:14px;">
              <?php if($r['status']==='Pending'): ?>
                <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
                <button class="btn decline-btn" onclick="openReject(this)"><i class='bx bx-x'></i> Reject</button>
                <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>
              <?php elseif($r['status']==='Printed'): ?>
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
    <h3>Reject Request</h3>
    <input type="hidden" id="rej_id"><input type="hidden" id="rej_table">
    <label>Reason:</label>
    <textarea id="rej_reason" required placeholder="Enter reason for rejection..." style="width:100%;height:100px;border:1px solid var(--border);border-radius:8px;padding:8px"></textarea>
    <div class="modal-actions">
      <button type="button" class="btn view-btn no-print" onclick="closeReject()">Cancel</button>
      <button type="submit" class="btn decline-btn no-print">Confirm Reject</button>
    </div>
  </form>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal-bg">
  <div class="modal" id="viewModalCard">
    <h3 id="vm_title">Request Details</h3>
    <div id="vm_body"><div style="color:var(--muted)">Loading details...</div></div>
    <div class="modal-actions no-print">
      <button class="btn view-btn" onclick="closeView()">Close</button>
      <button class="btn print-btn" onclick="quickPrintFromModal()"><i class='bx bx-printer'></i> Print</button>
    </div>
  </div>
</div>

<script>
/* ================= Helpers ================= */
const toast=(msg, ok=true)=>{const t=document.createElement('div');t.className='toast '+(ok?'ok':'err');t.textContent=msg;document.body.appendChild(t);setTimeout(()=>t.remove(),2500);};
const escapeHtml=s=>(s??'').toString().replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));

/* ================= Filters ================= */
document.querySelectorAll('.filter-tab').forEach(tab=>{
  tab.onclick=()=>{
    document.querySelectorAll('.filter-tab').forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    const f=tab.dataset.filter;
    document.querySelectorAll('.request-card').forEach(c=>{
      const show = (f==='all')
        ? (c.dataset.status!=='Completed') // hide completed in "All"
        : (c.dataset.status===f || c.dataset.type===f);
      c.style.display = show ? 'block' : 'none';
    });
  };
});

/* ================= Search ================= */
document.getElementById('searchInput').oninput=(e)=>{
  const q=e.target.value.toLowerCase();
  const inAll = document.querySelector('.filter-tab.active').dataset.filter==='all';
  document.querySelectorAll('.request-card').forEach(c=>{
    const isCompleted = c.dataset.status==='Completed';
    const hay = (c.dataset.name+' '+c.dataset.email+' '+c.dataset.type).toLowerCase();
    const match = hay.includes(q);
    c.style.display = match && (!inAll || !isCompleted) ? 'block':'none';
  });
};

/* =============== View Modal =============== */
let lastViewed = { id:null, table:null };

function openView(btn){
  const card=btn.closest('.request-card');
  const id=card.dataset.id;
  const table=card.dataset.table;
  lastViewed = { id, table };

  const vm=document.getElementById('viewModal');
  const body=document.getElementById('vm_body');
  const title=document.getElementById('vm_title');

  body.innerHTML = `<div style="color:var(--muted)">Loading details...</div>`;
  vm.classList.add('active');

  fetch(`?details=1&table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}`)
    .then(r=>r.json())
    .then(j=>{
      if(!j.ok){ body.innerHTML = `<div style="color:#b91c1c">Error loading details.</div>`; return; }
      title.textContent = `${j.label} • #${j.data.id}`;

      const d=j.data;
      const r=j.resident||{};
      const created = d.created_at ? new Date(d.created_at).toLocaleString() : '';

      const residentHTML = `
        <section style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <h4 style="margin:0 0 8px 0;color:#111">Resident</h4>
          <div><b>Name:</b> ${escapeHtml(r.name||'')}</div>
          <div><b>Email:</b> ${escapeHtml(r.email||'')}</div>
          <div><b>Contact:</b> ${escapeHtml(r.phone||'')}</div>
          <div><b>Barangay:</b> ${escapeHtml(r.barangay||'')}</div>
          <div><b>City/Province:</b> ${escapeHtml((r.city||'')+(r.city&&r.province?' / ':'')+(r.province||''))}</div>
        </section>`;

      const coreHTML = `
        <section style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <h4 style="margin:0 0 8px 0;color:#111">Request</h4>
          <div><b>Request ID:</b> ${escapeHtml(d.id)}</div>
          <div><b>Document:</b> ${escapeHtml(j.label)}</div>
          <div><b>Status:</b> ${escapeHtml(d.status||'')}</div>
          <div><b>Created:</b> ${escapeHtml(created)}</div>
        </section>`;

      // Show all user-submitted fields, render *_url as links
      const skip = new Set(['id','resident_id','barangay_name','status','created_at','permit_type','email','phone','first_name','last_name','fullname']);
      const rows = [];
      Object.keys(d||{}).forEach(k=>{
        if (skip.has(k)) return;
        const val = d[k];
        if (val==null || val==='') return;

        if (/_url$/i.test(k) || /^https?:/i.test(String(val))) {
          rows.push(`
            <div style="display:flex;justify-content:space-between;gap:12px">
              <div style="min-width:40%"><b>${labelize(k)}:</b></div>
              <div style="word-break:break-all">
                <a href="${escapeHtml(val)}" target="_blank" rel="noopener">${escapeHtml(val)}</a>
              </div>
            </div>`);
        } else {
          rows.push(`
            <div style="display:flex;justify-content:space-between;gap:12px">
              <div style="min-width:40%"><b>${labelize(k)}:</b></div>
              <div>${escapeHtml(String(val))}</div>
            </div>`);
        }
      });

      const extrasHTML = rows.length ? `
        <section style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <h4 style="margin:0 0 8px 0;color:#111">Details</h4>
          <div style="display:grid;gap:8px">${rows.join('')}</div>
        </section>` : '';

      body.innerHTML = `<div style="display:grid;gap:12px">${residentHTML}${coreHTML}${extrasHTML}</div>`;
    })
    .catch(()=>{ body.innerHTML=`<div style="color:#b91c1c">Network error.</div>`; });
}
function closeView(){ document.getElementById('viewModal').classList.remove('active'); }
function labelize(k){
  return k.replace(/_/g,' ')
          .replace(/\bid\b/ig,'ID')
          .replace(/\bdti\b/ig,'DTI')
          .replace(/\bojt\b/ig,'OJT')
          .replace(/\burl\b/ig,'URL')
          .replace(/\b\w/g, m=>m.toUpperCase());
}

/* =============== Reject Flow =============== */
function openReject(btn){
  const card=btn.closest('.request-card');
  document.getElementById('rej_id').value=card.dataset.id;
  document.getElementById('rej_table').value=card.dataset.table;
  document.getElementById('rejectModal').classList.add('active');
}
function closeReject(){ document.getElementById('rejectModal').classList.remove('active'); }
function submitReject(e){
  e.preventDefault();
  const id=document.getElementById('rej_id').value;
  const table=document.getElementById('rej_table').value;
  const reason=document.getElementById('rej_reason').value.trim();
  if(!reason){ toast('Reason required',false); return; }

  let f=new FormData();
  f.append('id',id); f.append('table',table);
  f.append('action','Rejected'); f.append('reason',reason);

  fetch('',{method:'POST',body:f})
    .then(r=>r.text())
    .then(t=>{
      if(t!=='OK'){ toast('Failed to reject',false); return; }
      closeReject();
      const card=document.querySelector(`.request-card[data-table="${CSS.escape(table)}"][data-id="${CSS.escape(id)}"]`);
      if(card){
        card.dataset.status='Rejected';
        card.querySelector('.status-badge').textContent='Rejected';
        card.querySelector('.status-badge').className='status-badge status-Rejected';
        card.querySelector('.request-actions').innerHTML=`
          <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>
        `;
      }
      toast('Request rejected');
    })
    .catch(()=>toast('Network error',false));
}

/* =============== Print (mark as Printed + open print_template.php) =============== */
function printRequest(btn){
  const card=btn.closest('.request-card');
  const id=card.dataset.id;
  const table=card.dataset.table;

  let f=new FormData();
  f.append('id',id); f.append('table',table); f.append('action','Printed');

  fetch('',{method:'POST',body:f})
    .then(r=>r.text())
    .then(t=>{
      if(t!=='OK'){ toast('Failed to update to Printed',false); return; }

      // Update UI to Printed: [Print] [Mark as Done] [View]
      card.dataset.status='Printed';
      card.querySelector('.status-badge').textContent='Printed';
      card.querySelector('.status-badge').className='status-badge status-Printed';
      card.querySelector('.request-actions').innerHTML=`
        <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
        <button class="btn ready-btn" onclick="markDone(this)"><i class='bx bx-check'></i> Mark as Done</button>
        <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>`;

      // Open printable official layout in new tab (auto-print handled by print_template.php if it checks ?print=1)
      const base = document.body.dataset.printHref || 'print_template.php';
      window.open(`${base}?table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}&print=1`,'_blank');
    })
    .catch(()=>toast('Network error',false));
}

/* Quick print from inside view modal: same behavior as button */
function quickPrintFromModal(){
  if(!lastViewed.id || !lastViewed.table){ return; }
  const base = document.body.dataset.printHref || 'print_template.php';
  window.open(`${base}?table=${encodeURIComponent(lastViewed.table)}&id=${encodeURIComponent(lastViewed.id)}&print=1`,'_blank');
}

/* =============== Mark as Done (Completed) =============== */
function markDone(btn){
  const card=btn.closest('.request-card');
  const id=card.dataset.id;
  const table=card.dataset.table;
  let f=new FormData();
  f.append('id',id); f.append('table',table); f.append('action','Completed');

  fetch('',{method:'POST',body:f})
    .then(r=>r.text())
    .then(t=>{
      if(t!=='OK'){ toast('Failed to mark as Completed',false); return; }
      card.dataset.status='Completed';
      card.querySelector('.status-badge').textContent='Completed';
      card.querySelector('.status-badge').className='status-badge status-Completed';
      // Remove from All (still visible in "Completed" filter)
      const active = document.querySelector('.filter-tab.active').dataset.filter;
      if (active === 'all') { card.remove(); }
      else {
        card.querySelector('.request-actions').innerHTML=`
          <button class="btn view-btn" onclick="openView(this)"><i class='bx bx-show'></i> View</button>
        `;
      }
      toast('✅ Request marked as Completed!');
    })
    .catch(()=>toast('Network error',false));
}

/* Close modals if clicking outside */
document.querySelectorAll('.modal-bg').forEach(bg=>{
  bg.addEventListener('click', (e)=>{ if(e.target===bg){ bg.classList.remove('active'); } });
});
</script>
</body>
</html>
