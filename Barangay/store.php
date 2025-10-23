<?php 
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("admin");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

$barangay = $_SESSION['sg_brgy'] ?? '';
$message = '';

/* =========================
   HANDLE ADMIN ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $action = $_POST['action'];
  $remarks = trim($_POST['remarks'] ?? '');

  $stmt = $conn->prepare("SELECT s.*, r.first_name, r.last_name 
                          FROM barangay_services s
                          JOIN residents r ON s.resident_id = r.id
                          WHERE s.id=? AND s.barangay_name=?");
  $stmt->bind_param("is", $id, $barangay);
  $stmt->execute();
  $svc = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$svc) {
    $message = "<div style='background:#fee2e2;border:1px solid #dc2626;padding:10px 14px;border-radius:10px;color:#991b1b;margin-bottom:14px;'>❌ Record not found.</div>";
  } else {
    $store = $svc['store_name'];
    $resident_id = $svc['resident_id'];

    if ($action === 'approve') {
      $conn->query("UPDATE barangay_services SET status='Approved', remarks=NULL WHERE id=$id");
      $notif_title = "✅ Store Approved";
      $notif_msg = "Your store <b>$store</b> has been approved by the barangay.";

    } elseif ($action === 'reject') {
      if ($remarks === '') $remarks = 'No reason provided.';
      $stmt2 = $conn->prepare("UPDATE barangay_services SET status='Rejected', remarks=? WHERE id=?");
      $stmt2->bind_param("si", $remarks, $id);
      $stmt2->execute();
      $stmt2->close();
      $notif_title = "❌ Store Rejected";
      $notif_msg = "Your store <b>$store</b> was rejected. Reason: <i>$remarks</i>";

    } elseif ($action === 'close') {
      if ($remarks === '') $remarks = 'No reason specified.';
      $stmt2 = $conn->prepare("UPDATE barangay_services SET closed_by_admin=1, closed_reason=?, status='Closed' WHERE id=?");
      $stmt2->bind_param("si", $remarks, $id);
      $stmt2->execute();
      $stmt2->close();
      $notif_title = "⚠️ Store Closed by Barangay";
      $notif_msg = "Your store <b>$store</b> has been closed by the barangay. Reason: <i>$remarks</i>";

    } elseif ($action === 'reopen') {
      $conn->query("UPDATE barangay_services SET closed_by_admin=0, closed_reason=NULL, status='Approved' WHERE id=$id");
      $notif_title = "✅ Store Reopened";
      $notif_msg = "Your store <b>$store</b> has been reopened by the barangay.";
    }

    // 🔔 Send Notification
    $stmt3 = $conn->prepare("
      INSERT INTO notifications (barangay_name, recipient_type, recipient_id, type, title, message, source_table, source_id)
      VALUES (?, 'resident', ?, 'service_review', ?, ?, 'barangay_services', ?)
    ");
    $stmt3->bind_param("sissi", $barangay, $resident_id, $notif_title, $notif_msg, $id);
    $stmt3->execute();
    $stmt3->close();

    $message = "<div style='background:#dcfce7;border:1px solid #16a34a;padding:10px 14px;border-radius:10px;color:#166534;margin-bottom:14px;'>✔️ Action completed and resident notified.</div>";
  }
}

/* =========================
   FETCH ALL SERVICES
========================= */
$stmt = $conn->prepare("
  SELECT s.*, r.first_name, r.last_name 
  FROM barangay_services s
  JOIN residents r ON s.resident_id = r.id
  WHERE s.barangay_name=?
  ORDER BY s.submitted_at DESC
");
$stmt->bind_param("s", $barangay);
$stmt->execute();
$services = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Business & Services · Admin</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
<style>
:root {
  --bg:#f5f7fa;--card:#ffffff;--text:#222;--muted:#6b7280;--border:#e5e7eb;
  --brand:#047857;--accent:#10b981;--declined:#ef4444;
  --radius:14px;--shadow:0 2px 8px rgba(0,0,0,.08);
  --sidebar-width:240px;
}
*{box-sizing:border-box;}
body{margin:0;font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);}
.layout{display:flex;min-height:100vh;}
.main-content{flex:1;padding:16px;transition:margin-left .3s ease;width:100%;}
@media(min-width:1024px){.main-content{margin-left:var(--sidebar-width);}}

/* Header */
.dashboard-header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px;background:var(--card);border:1px solid var(--border);padding:14px 20px;border-radius:var(--radius);box-shadow:var(--shadow);flex-wrap:wrap;}
.dashboard-header-left{display:flex;align-items:center;gap:14px;}
.dashboard-header img{height:48px;width:48px;border-radius:10px;object-fit:cover;}
.dashboard-title{font-size:1.4rem;font-weight:700;color:var(--brand);}
.subtle{color:var(--muted);font-size:.9rem;}

/* Grid + Cards */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;}
.card{position:relative;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:16px;display:flex;flex-direction:column;gap:10px;}
.card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);}
.thumb{width:100%;height:160px;object-fit:cover;border-radius:10px;}
.title{font-weight:700;font-size:1.05rem;color:var(--text);}
.meta{font-size:.9rem;color:var(--muted);}
.badge{padding:4px 8px;border-radius:8px;font-size:.8rem;font-weight:700;color:#fff;display:inline-flex;align-items:center;gap:4px;}
.s-pending{background:#f59e42;}
.s-approved{background:#16a34a;}
.s-rejected{background:#dc2626;}
.s-closed{background:#991b1b;}
.b-licensed{background:#0ea5e9;}
.b-approved{background:#10b981;}
.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.btn{all:unset;cursor:pointer;padding:8px 14px;border-radius:10px;font-weight:700;font-size:.9rem;}
.btn.view{background:#e5e7eb;color:#111;}
.btn.approve{background:linear-gradient(135deg,#16a34a,#10b981);color:#fff;}
.btn.close{background:#fee2e2;color:#991b1b;}
.btn.reopen{background:#dcfce7;color:#166534;}
.btn.reject{background:#fee2e2;color:#991b1b;}
.btn:hover{opacity:.9;}
.status-banner{position:absolute;top:10px;left:10px;background:rgba(239,68,68,.9);color:#fff;padding:4px 10px;border-radius:8px;font-weight:600;}

/* Modal */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:22px;width:95%;max-width:720px;}
.modal h3{margin-top:0;color:var(--brand);}
.modal textarea{width:100%;height:110px;border-radius:10px;border:1px solid var(--border);padding:10px;}
.modal-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px;}
.modal-body{max-height:70vh;overflow:auto;}
.modal-body img{max-width:100%;border-radius:10px;border:1px solid var(--border);}
.docs{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}
.docs a{background:var(--brand);color:#fff;text-decoration:none;padding:6px 10px;border-radius:8px;font-size:.9rem;}
</style>
</head>
<body>
<div class="layout">
<main class="main-content">
  <div class="dashboard-header">
    <div class="dashboard-header-left">
      <img src="B.png" alt="Barangay Logo">
      <div>
        <div class="dashboard-title">Barangay Business & Services</div>
        <div class="subtle">Review, verify, and manage submitted stores.</div>
      </div>
    </div>
  </div>

  <?= $message ?>

  <div class="grid" id="servicesGrid">
  <?php if($services->num_rows): while($s=$services->fetch_assoc()): ?>
    <div class="card" data-status="<?= htmlspecialchars($s['status']) ?>">
      <?php if($s['status']==='Closed'): ?><div class="status-banner">Closed</div><?php endif; ?>
      <?php if($s['photo_url']): ?>
        <img src="../<?= htmlspecialchars($s['photo_url']) ?>" class="thumb">
      <?php endif; ?>
      <div class="title"><?= htmlspecialchars($s['store_name']) ?></div>
      <div class="meta"><?= htmlspecialchars($s['address']) ?></div>
      <div class="meta"><?= htmlspecialchars($s['open_hours']) ?></div>
      <div class="meta"><b>Owner:</b> <?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></div>
      <div class="docs">
        <?php if($s['dti_cert_url']): ?><a href="../<?= htmlspecialchars($s['dti_cert_url']) ?>" target="_blank">DTI Cert</a><?php endif; ?>
        <?php if($s['lease_contract_url']): ?><a href="../<?= htmlspecialchars($s['lease_contract_url']) ?>" target="_blank">Lease</a><?php endif; ?>
        <?php if($s['valid_id_url']): ?><a href="../<?= htmlspecialchars($s['valid_id_url']) ?>" target="_blank">Valid ID</a><?php endif; ?>
        <?php if($s['business_permit_url']): ?><a href="../<?= htmlspecialchars($s['business_permit_url']) ?>" target="_blank">Business Permit</a><?php endif; ?>
      </div>
      <div class="actions">
        <button class="btn view" onclick="openDetails(<?= $s['id'] ?>,'<?= addslashes($s['store_name']) ?>','<?= addslashes($s['address']) ?>','<?= addslashes($s['open_hours']) ?>','<?= addslashes($s['contact']) ?>','<?= $s['classification'] ?>','<?= $s['status'] ?>','<?= addslashes($s['photo_url']) ?>','<?= addslashes($s['closed_reason']) ?>')"><i class='bx bx-show'></i> View</button>
      </div>
    </div>
  <?php endwhile; else: ?>
    <p style="text-align:center;color:var(--muted);margin-top:30px;">No services found.</p>
  <?php endif; ?>
  </div>
</main>
</div>

<!-- Modal -->
<div class="modal-bg" id="detailsModal">
  <div class="modal">
    <h3 id="modalTitle"></h3>
    <div class="modal-body">
      <img id="modalPhoto" src="" alt="">
      <p id="modalInfo"></p>
      <p id="modalReason" style="color:#991b1b;font-weight:600;"></p>
    </div>
    <form method="POST" id="modalForm" style="display:none;margin-top:12px;">
      <input type="hidden" name="id" id="modalId">
      <input type="hidden" name="action" id="modalAction">
      <label>Remarks / Reason:</label>
      <textarea name="remarks" id="modalRemarks" required></textarea>
      <div class="modal-actions">
        <button type="button" class="btn view" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn approve" id="modalConfirmBtn">Confirm</button>
      </div>
    </form>
    <div class="modal-actions" id="modalButtons">
      <form method="POST" style="display:inline;">
        <input type="hidden" name="id" id="approveId"><input type="hidden" name="action" value="approve">
        <button type="submit" class="btn approve">Approve</button>
      </form>
      <button class="btn reject" onclick="showReject()">Reject</button>
      <button class="btn close" onclick="showClose()">Close Store</button>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="id" id="reopenId"><input type="hidden" name="action" value="reopen">
        <button type="submit" class="btn reopen">Reopen</button>
      </form>
      <button type="button" class="btn view" onclick="closeModal()">Exit</button>
    </div>
  </div>
</div>

<script>
function openDetails(id,name,addr,hours,contact,classif,status,photo,reason){
  const modal=document.getElementById('detailsModal');
  modal.classList.add('active');
  document.getElementById('modalTitle').textContent=name;
  document.getElementById('modalPhoto').src='../'+photo;
  document.getElementById('modalInfo').innerHTML=`📍 ${addr}<br>🕒 ${hours}<br>📞 ${contact}<br><b>${classif}</b>`;
  document.getElementById('modalReason').textContent=reason?('Reason: '+reason):'';
  document.getElementById('approveId').value=id;
  document.getElementById('reopenId').value=id;
  document.getElementById('modalButtons').style.display='flex';
  document.getElementById('modalForm').style.display='none';
}
function showReject(){
  const f=document.getElementById('modalForm');
  f.style.display='block';
  f.querySelector('#modalAction').value='reject';
  f.querySelector('#modalConfirmBtn').className='btn reject';
  f.querySelector('#modalConfirmBtn').textContent='Reject';
  document.getElementById('modalButtons').style.display='none';
  f.querySelector('#modalId').value=document.getElementById('approveId').value;
}
function showClose(){
  const f=document.getElementById('modalForm');
  f.style.display='block';
  f.querySelector('#modalAction').value='close';
  f.querySelector('#modalConfirmBtn').className='btn close';
  f.querySelector('#modalConfirmBtn').textContent='Close Store';
  document.getElementById('modalButtons').style.display='none';
  f.querySelector('#modalId').value=document.getElementById('approveId').value;
}
function closeModal(){document.getElementById('detailsModal').classList.remove('active');}
</script>
</body>
</html>
