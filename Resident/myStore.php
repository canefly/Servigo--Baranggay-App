<?php 
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';
$message = '';

/* =========================
   Handle Add / Edit / Close
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $upload_dir = __DIR__ . "/../uploads/services/";
  if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

  function handleUpload($key, $dir) {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) return null;
    $fname = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES[$key]['name']);
    $dest = $dir . $fname;
    if (move_uploaded_file($_FILES[$key]['tmp_name'], $dest))
      return "uploads/services/" . $fname;
    return null;
  }

  /* ========== ADD STORE ========== */
  if ($action === 'add') {
    $store_name     = trim($_POST['store_name']);
    $address        = trim($_POST['address']);
    $open_hours     = trim($_POST['open_hours']);
    $contact        = trim($_POST['contact']);
    $classification = $_POST['classification'];
    $service_type   = $_POST['service_type'];
    $photo_url      = handleUpload('photo', $upload_dir);
    $dti_cert_url   = handleUpload('dti_cert', $upload_dir);
    $lease_contract_url = handleUpload('lease_contract', $upload_dir);
    $valid_id_url   = handleUpload('valid_id', $upload_dir);
    $business_permit_url = handleUpload('business_permit', $upload_dir);

    $stmt = $conn->prepare("
      INSERT INTO barangay_services 
      (resident_id, barangay_name, store_name, address, open_hours, contact, classification, category, photo_url, 
       dti_cert_url, lease_contract_url, valid_id_url, business_permit_url, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt->bind_param(
      "issssssssssss", 
      $resident_id, $barangay, $store_name, $address, $open_hours, $contact, 
      $classification, $service_type, $photo_url, 
      $dti_cert_url, $lease_contract_url, $valid_id_url, $business_permit_url
    );
    $stmt->execute();
    $stmt->close();

    $message = "<div style='background:#dcfce7;border:1px solid #16a34a;padding:10px 14px;border-radius:10px;color:#166534;margin-bottom:14px;'>✅ Store submitted for barangay review.</div>";
  }

  /* ========== EDIT STORE INFO ONLY ========== */
  elseif ($action === 'edit') {
    $id = intval($_POST['id']);
    $store_name = trim($_POST['store_name']);
    $address = trim($_POST['address']);
    $open_hours = trim($_POST['open_hours']);
    $contact = trim($_POST['contact']);
    $classification = $_POST['classification'];
    $service_type = $_POST['service_type'];
    $new_photo = handleUpload('photo', $upload_dir);

    if ($new_photo) {
      $stmt = $conn->prepare("UPDATE barangay_services 
                              SET store_name=?, address=?, open_hours=?, contact=?, classification=?, category=?, photo_url=? 
                              WHERE id=? AND resident_id=?");
      $stmt->bind_param("sssssssii", $store_name, $address, $open_hours, $contact, $classification, $service_type, $new_photo, $id, $resident_id);
    } else {
      $stmt = $conn->prepare("UPDATE barangay_services 
                              SET store_name=?, address=?, open_hours=?, contact=?, classification=?, category=? 
                              WHERE id=? AND resident_id=?");
      $stmt->bind_param("ssssssii", $store_name, $address, $open_hours, $contact, $classification, $service_type, $id, $resident_id);
    }
    $stmt->execute();
    $stmt->close();

    $message = "<div style='background:#e0f2fe;border:1px solid #0284c7;padding:10px 14px;border-radius:10px;color:#075985;margin-bottom:14px;'>✏️ Store information updated successfully.</div>";
  }

  /* ========== CLOSE / REOPEN ========== */
  elseif (in_array($action, ['close_today','reopen','close_forever'])) {
    $id = intval($_POST['id']);
    if ($action === 'close_today') 
      $conn->query("UPDATE barangay_services SET is_closed_today=1 WHERE id=$id AND resident_id=$resident_id");
    if ($action === 'reopen') 
      $conn->query("UPDATE barangay_services SET is_closed_today=0,is_closed_forever=0 WHERE id=$id AND resident_id=$resident_id");
    if ($action === 'close_forever') 
      $conn->query("UPDATE barangay_services SET is_closed_forever=1 WHERE id=$id AND resident_id=$resident_id");
    $message = "<div style='background:#fef3c7;border:1px solid #f59e0b;padding:10px 14px;border-radius:10px;color:#92400e;margin-bottom:14px;'>⚠️ Store status updated.</div>";
  }
}

/* =========================
   Fetch Resident's Stores
========================= */
$stmt = $conn->prepare("SELECT * FROM barangay_services WHERE resident_id=? ORDER BY submitted_at DESC");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$apps = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Stores & Services · Servigo</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root{
  --brand:#1e40af;--accent:#16a34a;--border:#e5e7eb;
  --muted:#6b7280;--bg:#f5f7fa;--card:#fff;--radius:14px;--text:#1e1e1e;
}
body{font-family:"Parkinsans","Outfit",sans-serif;background:var(--bg);color:var(--text);margin:0;}
.container{max-width:1200px;margin:auto;padding:24px;}
h2{color:var(--brand);font-weight:800;margin-bottom:10px;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-bottom:20px;}
.card{position:relative;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 3px 10px rgba(0,0,0,.05);overflow:hidden;}
.card img{width:100%;height:180px;object-fit:cover;background:#f9fafb;}
.closed-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(220,38,38,.45);color:#fff;font-weight:700;font-size:1.1rem;display:flex;align-items:center;justify-content:center;text-shadow:0 1px 3px rgba(0,0,0,.5);}
.card .body{padding:14px 16px;}
.title{font-size:1rem;font-weight:700;color:var(--brand);}
.badge{display:inline-block;margin-top:6px;padding:4px 8px;border-radius:8px;font-size:.75rem;font-weight:600;color:#fff;}
.s-pending{background:#f59e0b;}
.s-approved{background:#16a34a;}
.s-rejected{background:#dc2626;}
.actions{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
.btn{border:none;border-radius:8px;padding:7px 10px;font-weight:700;font-size:.8rem;cursor:pointer;}
.btn-edit{background:#dbeafe;color:#1e3a8a;}
.btn-close{background:#fef3c7;color:#92400e;}
.btn-forever{background:#fee2e2;color:#991b1b;}
.btn-reopen{background:#dcfce7;color:#166534;}
.btn:hover{opacity:.9;}
.add-btn{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;padding:10px 18px;border-radius:10px;font-weight:700;border:none;cursor:pointer;}
.add-btn:hover{opacity:.9;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);justify-content:center;align-items:center;z-index:3000;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:14px;padding:20px;max-width:600px;width:100%;max-height:90vh;overflow:auto;box-shadow:0 12px 28px rgba(0,0,0,.25);}
.modal h3{color:var(--brand);margin-top:0;}
input,select{width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;margin-bottom:10px;font-size:.95rem;}
label{font-weight:600;font-size:.9rem;}
.close-btn{background:#f3f4f6;border:none;padding:8px 14px;border-radius:8px;margin-left:8px;cursor:pointer;}
.submit-btn{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:700;}
.doc-section{background:#f9fafb;border:1px solid var(--border);border-radius:10px;padding:10px;margin-top:10px;}
.doc-section h4{margin:0 0 8px 0;font-size:.95rem;color:var(--brand);}
.doc-tips{font-size:.85rem;color:var(--muted);}
</style>
</head>
<body>
<div class="container">
  <h2><i class='bx bx-store-alt'></i> My Stores & Services</h2>
  <?= $message ?>

  <div class="grid">
    <?php if($apps->num_rows): while($a=$apps->fetch_assoc()): ?>
      <div class="card">
        <?php if($a['photo_url']): ?><img src="../<?= htmlspecialchars($a['photo_url']) ?>"><?php endif; ?>
        <?php if($a['is_closed_today'] || $a['is_closed_forever']): ?>
          <div class="closed-overlay"><?= $a['is_closed_forever'] ? "Permanently Closed" : "Temporarily Closed" ?></div>
        <?php endif; ?>
        <div class="body">
          <div class="title"><?= htmlspecialchars($a['store_name']) ?></div>
          <p style="color:var(--muted);font-size:.9rem;margin:4px 0;">
            <?= htmlspecialchars($a['address']) ?><br><small><?= htmlspecialchars($a['open_hours']) ?></small>
          </p>
          <span class="badge <?= strtolower($a['status'])=='pending'?'s-pending':(strtolower($a['status'])=='approved'?'s-approved':'s-rejected') ?>">
            <?= htmlspecialchars($a['status']) ?>
          </span>
          <div class="actions">
            <button class="btn btn-edit" onclick='openModal("edit", <?= json_encode($a) ?>)'>✏️ Edit</button>
            <?php if(!$a['is_closed_today'] && !$a['is_closed_forever']): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="close_today"><input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button class="btn btn-close">🕒 Temporarily Close</button>
              </form>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="close_forever"><input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button class="btn btn-forever">🚫 Permanently Retire</button>
              </form>
            <?php elseif($a['is_closed_today']): ?>
              <form method="POST"><input type="hidden" name="action" value="reopen"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-reopen">🔁 Reopen</button></form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <p style="color:var(--muted);text-align:center;">No store applications yet.</p>
    <?php endif; ?>
  </div>

  <div style="text-align:center;margin-top:20px;">
    <button class="add-btn" onclick="openModal('add')"><i class='bx bx-plus-circle'></i> Add New Store</button>
  </div>
</div>

<!-- MODAL FORM -->
<div class="modal-bg" id="modalBg">
  <div class="modal">
    <h3 id="modalTitle">Add New Store / Service</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" id="action" value="add">
      <input type="hidden" name="id" id="storeId">

      <label>Store / Service Name</label><input name="store_name" id="store_name" required>
      <label>Address / Coverage Area</label><input name="address" id="address" required>
      <label>Open Hours</label><input name="open_hours" id="open_hours" placeholder="Mon–Sat 8:00 AM – 6:00 PM" required>
      <label>Contact Number</label><input name="contact" id="contact" required>

      <label>Classification</label>
      <select name="classification" id="classification" required>
        <option value="">Select</option>
        <option value="licensed">Licensed Business</option>
        <option value="informal">Barangay-Approved</option>
      </select>

      <label>Service Type</label>
      <select name="service_type" id="service_type" required>
        <option value="">Select</option>
        <option value="fixed">Fixed Location</option>
        <option value="home">Home-to-Home</option>
      </select>

      <div class="doc-section" id="uploadSection">
        <h4><i class='bx bx-folder-open'></i> Upload Required Documents</h4>
        <p class="doc-tips">Attach clear photos of your valid business documents (DTI, Lease, Valid ID, Business Permit).</p>
        <label>DTI Certificate</label><input type="file" name="dti_cert" accept=".jpg,.jpeg,.png,.pdf">
        <label>Lease Contract</label><input type="file" name="lease_contract" accept=".jpg,.jpeg,.png,.pdf">
        <label>Valid ID</label><input type="file" name="valid_id" accept=".jpg,.jpeg,.png,.pdf">
        <label>Business Permit</label><input type="file" name="business_permit" accept=".jpg,.jpeg,.png,.pdf">
        <label>Store Photo</label><input type="file" name="photo" accept=".jpg,.jpeg,.png">
      </div>

      <div style="margin-top:14px;text-align:right;">
        <button type="submit" class="submit-btn">💾 Save</button>
        <button type="button" class="close-btn" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(mode,data=null){
  const modal=document.getElementById('modalBg');
  modal.classList.add('active');
  document.getElementById('modalTitle').textContent=(mode==='add')?'Add New Store / Service':'Edit Store / Service';
  document.getElementById('action').value=mode;

  const uploadSec=document.getElementById('uploadSection');
  if(mode==='add'){uploadSec.style.display='block';}
  else{uploadSec.style.display='none';}

  if(mode==='edit' && data){
    document.getElementById('storeId').value=data.id;
    document.getElementById('store_name').value=data.store_name;
    document.getElementById('address').value=data.address;
    document.getElementById('open_hours').value=data.open_hours;
    document.getElementById('contact').value=data.contact;
    document.getElementById('classification').value=data.classification;
    document.getElementById('service_type').value=data.category;
  }
}
function closeModal(){
  document.getElementById('modalBg').classList.remove('active');
}
</script>
</body>
</html>
