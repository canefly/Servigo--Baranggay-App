<?php
require_once(__DIR__ . "/../Database/session-checker.php");
require_once(__DIR__ . "/../Database/connection.php");
requireRole("admin");
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

$barangay = $_SESSION['sg_brgy'] ?? '';

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

$union = [];
foreach ($tables as $tbl => $label) {
  $union[] = "
  SELECT '$tbl' AS table_name, r.id, r.resident_id,
         CONCAT(res.first_name,' ',res.last_name) AS fullname,
         r.email, r.barangay_name, r.status, r.created_at, '$label' AS document_type
  FROM $tbl r
  JOIN residents res ON r.resident_id=res.id
  WHERE r.barangay_name=?
  ";
}
$sql = implode(" UNION ALL ", $union) . " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($tables)), ...array_fill(0, count($tables), $barangay));
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD']==='POST'){
  $id=intval($_POST['id']);
  $table=$_POST['table'];
  $action=$_POST['action'];
  $reason=$_POST['reason']??null;
  $stmt=$conn->prepare("UPDATE $table SET status=?, remarks=? WHERE id=?");
  $stmt->bind_param("ssi",$action,$reason,$id);
  $stmt->execute();
  echo "OK";
  exit;
}
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
.status-Ready{background:#16a34a;}
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
.done-btn{background:#3b82f6;color:#fff;}
.view-btn{background:var(--gray);color:#111;}
.view-btn:hover{background:#d1d5db;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);
  z-index:999;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:22px;width:95%;max-width:480px;}
.modal h3{margin-top:0;color:var(--green);}
.modal textarea{width:100%;height:90px;border-radius:10px;border:1px solid var(--border);padding:10px;}
.modal-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px;}
.no-requests{text-align:center;color:var(--muted);margin-top:40px;}
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
        <button class="filter-tab" data-filter="Barangay Clearance">Barangay Clearance</button>
        <button class="filter-tab" data-filter="Indigency">Indigency</button>
        <button class="filter-tab" data-filter="Residency">Residency</button>
        <button class="filter-tab" data-filter="Good Moral">Good Moral</button>
        <button class="filter-tab" data-filter="Solo Parent">Solo Parent</button>
        <button class="filter-tab" data-filter="Late Birth Registration">Late Birth Registration</button>
        <button class="filter-tab" data-filter="No Record">No Record</button>
        <button class="filter-tab" data-filter="OJT">OJT</button>
        <button class="filter-tab" data-filter="Business Permit">Business Permit</button>
      </nav>

      <section id="requestsList" class="requests-list">
        <?php if(empty($requests)): ?>
          <div class="no-requests"><i class='bx bx-user-x' style="font-size:2rem;"></i><br>No requests found.</div>
        <?php else: foreach($requests as $r): ?>
          <div class="request-card" data-status="<?=$r['status']?>" data-type="<?=$r['document_type']?>" data-id="<?=$r['id']?>" data-table="<?=$r['table_name']?>">
            <div class="request-header">
              <span class="resident-name"><i class='bx bx-user'></i> <?=htmlspecialchars($r['fullname'])?></span>
              <span class="request-type"><?=htmlspecialchars($r['document_type'])?></span>
            </div>
            <div class="date"><i class='bx bx-calendar'></i><?=date("M d, Y",strtotime($r['created_at']))?></div>
            <span class="status-badge status-<?=$r['status']?>"><?=$r['status']?></span>
            <div class="request-actions" style="margin-top:14px;">
              <?php if($r['status']==='Pending'): ?>
                <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
                <button class="btn decline-btn" onclick="openReject(this)"><i class='bx bx-x'></i> Decline</button>
                <button class="btn view-btn"><i class='bx bx-show'></i> View</button>
              <?php elseif($r['status']==='Ready'): ?>
                <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
                <button class="btn ready-btn" onclick="markDone(this)"><i class='bx bx-check'></i> Mark as Done</button>
                <button class="btn view-btn"><i class='bx bx-show'></i> View</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </section>
    </div>
  </main>
</div>

<div id="rejectModal" class="modal-bg">
  <form class="modal" onsubmit="submitReject(event)">
    <h3>Decline Request</h3>
    <input type="hidden" id="rej_id"><input type="hidden" id="rej_table">
    <label>Reason:</label>
    <textarea id="rej_reason" required placeholder="Enter reason for decline..."></textarea>
    <div class="modal-actions">
      <button type="button" class="btn view-btn" onclick="closeReject()">Cancel</button>
      <button type="submit" class="btn decline-btn">Confirm Decline</button>
    </div>
  </form>
</div>

<script>
/* Filter Tabs */
document.querySelectorAll('.filter-tab').forEach(tab=>{
  tab.onclick=()=>{
    document.querySelectorAll('.filter-tab').forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    const f=tab.dataset.filter;
    document.querySelectorAll('.request-card').forEach(c=>{
      const match=(f==='all')
        ? (c.dataset.status!=='Completed') // hide completed in All
        : (c.dataset.status===f || c.dataset.type===f);
      c.style.display=match?'block':'none';
    });
  };
});

/* Search Filter */
document.getElementById('searchInput').oninput=(e)=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll('.request-card').forEach(c=>{
    const inAll = document.querySelector('.filter-tab.active').dataset.filter==='all';
    const isCompleted = c.dataset.status==='Completed';
    const match = c.textContent.toLowerCase().includes(q);
    c.style.display = match && (!inAll || !isCompleted) ? 'block':'none';
  });
};

/* Decline Modal */
function openReject(btn){
  const card=btn.closest('.request-card');
  document.getElementById('rej_id').value=card.dataset.id;
  document.getElementById('rej_table').value=card.dataset.table;
  document.getElementById('rejectModal').classList.add('active');
}
function closeReject(){document.getElementById('rejectModal').classList.remove('active');}
function submitReject(e){
  e.preventDefault();
  const id=document.getElementById('rej_id').value;
  const table=document.getElementById('rej_table').value;
  const reason=document.getElementById('rej_reason').value;
  let f=new FormData();
  f.append('id',id);f.append('table',table);f.append('action','Rejected');f.append('reason',reason);
  fetch('',{method:'POST',body:f}).then(()=>location.reload());
}

/* PRINT BUTTON → Set to READY */
function printRequest(btn){
  const card=btn.closest('.request-card');
  const id=card.dataset.id;
  const table=card.dataset.table;
  let f=new FormData();
  f.append('id',id);
  f.append('table',table);
  f.append('action','Ready'); // ✅ update DB status
  fetch('',{method:'POST',body:f}).then(()=>{
    // Update UI
    card.dataset.status='Ready';
    card.querySelector('.status-badge').textContent='Ready';
    card.querySelector('.status-badge').className='status-badge status-Ready';
    card.querySelector('.request-actions').innerHTML=`
      <button class="btn print-btn" onclick="printRequest(this)"><i class='bx bx-printer'></i> Print</button>
      <button class="btn ready-btn" onclick="markDone(this)"><i class='bx bx-check'></i> Mark as Done</button>
      <button class="btn view-btn"><i class='bx bx-show'></i> View</button>`;
  });
}

/* MARK AS DONE → Set to COMPLETED + Hide from All */
function markDone(btn){
  const card=btn.closest('.request-card');
  const id=card.dataset.id;
  const table=card.dataset.table;
  let f=new FormData();
  f.append('id',id);
  f.append('table',table);
  f.append('action','Completed'); // ✅ update DB to completed
  fetch('',{method:'POST',body:f}).then(()=>{
    card.dataset.status='Completed';
    card.querySelector('.status-badge').textContent='Completed';
    card.querySelector('.status-badge').className='status-badge status-Completed';
    
    // If user is viewing "All", remove it visually
    const active=document.querySelector('.filter-tab.active').dataset.filter;
    if(active==='all' || active==='Ready' || active==='Pending'){
      card.remove();
    }

    // Optional toast
    const toast=document.createElement('div');
    toast.textContent='✅ Request marked as Completed!';
    Object.assign(toast.style,{
      position:'fixed',bottom:'20px',right:'20px',
      background:'#16a34a',color:'#fff',padding:'10px 16px',
      borderRadius:'10px',fontWeight:'600',boxShadow:'0 2px 8px rgba(0,0,0,.2)'
    });
    document.body.appendChild(toast);
    setTimeout(()=>toast.remove(),2500);
  });
}


</script>
</body>
</html>
