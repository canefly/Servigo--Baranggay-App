<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? "Unknown Barangay";
$email       = $_SESSION['sg_email'] ?? "";
$message     = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Servigo · Barangay Permits</title>
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

/* Header Row */
.header-flex {
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  flex-wrap:wrap;
  gap:24px;
}

/* Hero */
.hero {
  flex:1 1 600px;
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

/* My Requests Shortcut */
.requests-shortcut {
  background:#fff;
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 3px 10px rgba(0,0,0,.06);
  padding:24px 26px;
  text-align:center;
  flex:0 0 340px;
}
.requests-shortcut h2 {
  color:var(--brand);
  font-size:1.1rem;
  font-weight:700;
  margin-bottom:8px;
}
.requests-shortcut p {
  color:var(--muted);
  font-size:0.9rem;
  margin-bottom:12px;
}
.btn-gradient {
  background:linear-gradient(135deg,var(--brand),var(--accent));
  border:none;
  border-radius:10px;
  color:#fff;
  font-weight:600;
  padding:10px 14px;
  transition:.2s;
  cursor:pointer;
}
.btn-gradient:hover {opacity:.9;}

/* Permit Cards Grid */
.permit-grid {
  display:grid;
  gap:1.2rem;
  justify-content:center;
  grid-template-columns:repeat(auto-fit,minmax(260px,300px));
  margin:auto;
  max-width:85%;
  margin-top:2.5rem;
}
.card {
  background:#fff;
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  padding:1.5rem 1.25rem;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  transition:box-shadow .2s ease,transform .2s ease;
}
.card:hover {transform:translateY(-3px);box-shadow:0 4px 12px rgba(0,0,0,.1);}
.card h3 {
  color:var(--brand);
  font-weight:700;
  font-size:1.1rem;
  margin-bottom:0.5rem;
}
.card p {
  color:var(--muted);
  font-size:0.95rem;
  flex-grow:1;
  margin-bottom:1rem;
  line-height:1.45;
}

/* ===== Modal Overlay ===== */
.modal {
  display:none;
  position:fixed;
  top:0;left:0;
  width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  backdrop-filter:blur(5px);
  z-index:2000;
  align-items:center;
  justify-content:center;
  padding:1rem;
}
.modal.show {display:flex;}

/* ===== Modal Box ===== */
.modal form {
  background:#fff;
  border-radius:14px;
  box-shadow:0 8px 24px rgba(0,0,0,0.15);
  width:100%;
  max-width:480px;
  display:flex;
  flex-direction:column;
  overflow:hidden;
  animation:fadeIn .3s ease;
}
@keyframes fadeIn {
  from {opacity:0;transform:translateY(-10px);}
  to {opacity:1;transform:translateY(0);}
}

.modal .body {
  padding:1.5rem;
  overflow-y:auto;
  max-height:75vh;
}
.modal .footer {
  padding:1rem;
  text-align:center;
  border-top:1px solid #e5e7eb;
  background:#fafafa;
}
.modal label {
  display:block;
  font-weight:600;
  font-size:0.9rem;
  margin-top:0.7rem;
}
.modal input, .modal textarea {
  width:100%;
  border:1px solid #e5e7eb;
  border-radius:10px;
  padding:10px;
  font-size:0.95rem;
  margin-top:4px;
  transition:border-color .2s;
}
.modal input:focus, .modal textarea:focus {
  border-color:#1e40af;
  outline:none;
}
.modal h2 {
  color:#1e40af;
  font-size:1.3rem;
  margin:0 0 0.8rem;
  text-align:center;
}

/* Responsive */
@media(max-width:600px){
  .modal .body {padding:1rem;}
  .modal form {max-width:90%;}
}


/* Footer */
footer {
  background:#fff;
  color:var(--muted);
  font-size:0.9rem;
  padding:20px;
  text-align:center;
  border-top:1px solid var(--border);
}

/* Responsive */
@media(max-width:992px){
  .header-flex {flex-direction:column;text-align:center;}
  .requests-shortcut {flex:unset;max-width:100%;}
}
</style>
</head>

<body>
<div class="container-custom">
  <div class="header-flex">
    <section class="hero">
      <h1>Barangay Permits & Documents</h1>
      <p>Apply online for barangay clearances, certificates, and permits — available anytime.</p>
    </section>
  </div>

  <div class="permit-grid">
    <?php 
    $permits = [
      ["Barangay Clearance","Certification of good moral standing.","Valid ID, Cedula"],
      ["Residency","Proof of current residence.","Barangay ID or Proof of Address"],
      ["Indigency","Issued to financially challenged residents.","Valid ID, Proof of Income"],
      ["Good Moral","Certification of good conduct.","Valid ID, Barangay Clearance"],
      ["Solo Parent","Recognition for single parents under R.A. 8972.","Valid ID, Proof of Solo Parent Status"],
      ["Late Birth Registration","Support for delayed PSA birth registration.","Valid ID, Birth Record"],
      ["No Record","Proof that no blotter or complaint record exists.","Valid ID"],
      ["Employment","Proof of employment or self-employment.","Valid ID, Employment Proof or Business Permit"],
      ["OJT","Endorsement for internships.","Valid ID, School Endorsement Letter"],
      ["Business Permit","Authorization for business operations.","DTI/SEC Registration, Lease/Ownership Papers"],
    ];
    foreach ($permits as $p): ?>
      <div class="card">
        <h3><?= htmlspecialchars($p[0]) ?></h3>
        <p><?= htmlspecialchars($p[1]) ?><br><strong>Requirements:</strong> <?= htmlspecialchars($p[2]) ?></p>
        <button class="btn-gradient" onclick="openForm('<?= htmlspecialchars($p[0]) ?>')">Apply Now</button>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<footer>© 2025 Servigo. All rights reserved.</footer>

<!-- Modal -->
<div id="applyModal" class="modal">
  <form method="POST" enctype="multipart/form-data">
    <div class="body">
      <h2 id="modalTitle">Apply for Permit</h2>
      <input type="hidden" name="permit_type" id="permitTypeInput">
      <label>Full Name<input name="fullname" required></label>
      <label>Email<input name="email" type="email" value="<?= htmlspecialchars($email) ?>" required></label>
      <label>Phone<input name="phone"></label>
      <label>Civil Status<input name="civil_status" required></label>
      <label>Date of Birth<input name="date_of_birth" type="date" required></label>
      <label>House & Street<input name="house_street" required></label>
      <label>City<input name="city" required></label>
      <label>Province<input name="province" required></label>
      <label>Date of Residency<input name="date_of_residency" type="date"></label>
      <label>Years of Residency<input name="years_residency" type="number"></label>
      <label>Purpose<textarea name="purpose" rows="3" required></textarea></label>
      <label>Valid ID<input name="valid_id" type="file" accept=".jpg,.jpeg,.png,.pdf" required></label>
    </div>
    <div class="footer">
      <button class="btn-gradient" type="submit">Submit Request</button>
      <button type="button" class="btn-gradient" style="background:#ef4444" onclick="closeForm()">Cancel</button>
      <div style="margin-top:8px;"><?= $message ?></div>
    </div>
  </form>
</div>

<script>
function openForm(type){
  document.getElementById("applyModal").classList.add("show");
  document.getElementById("permitTypeInput").value=type;
  document.getElementById("modalTitle").textContent="Barangay "+type+" Request";
  document.body.style.overflow='hidden';
}
function closeForm(){
  document.getElementById("applyModal").classList.remove("show");
  document.body.style.overflow='';
}

function openForm(type){
  const modal=document.getElementById("applyModal");
  modal.classList.add("show");
  document.getElementById("permitTypeInput").value=type;
  document.getElementById("modalTitle").textContent="Barangay "+type+" Request";
  document.body.style.overflow='hidden';
}
function closeForm(){
  const modal=document.getElementById("applyModal");
  modal.classList.remove("show");
  document.body.style.overflow='';
}

</script>
</body>
</html>
