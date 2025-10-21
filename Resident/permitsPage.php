<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';


// 📦 Load Resident Context
$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? "Unknown Barangay";
$email       = $_SESSION['sg_email'] ?? "";
$message     = "";

// 🧾 Handle Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['permit_type'])) {
    $permit_type     = $_POST['permit_type'];
    $fullname        = trim($_POST['fullname']);
    $civil_status    = trim($_POST['civil_status']);
    $date_of_birth   = $_POST['date_of_birth'];
    $house_street    = trim($_POST['house_street']);
    $city            = trim($_POST['city']);
    $province        = trim($_POST['province']);
    $date_of_residency = $_POST['date_of_residency'] ?? null;
    $years_residency = $_POST['years_residency'] ?? null;
    $purpose         = trim($_POST['purpose']);
    $phone           = trim($_POST['phone']);
    $file_path       = null;

    // 📎 File Upload
    if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . "/../uploads/valid_ids/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES['valid_id']['name'], PATHINFO_EXTENSION);
        $fname = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES['valid_id']['name']);
        $dest = $dir . $fname;
        if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $dest)) {
            $file_path = "uploads/valid_ids/" . $fname;
        }
    }

    // 🧮 Insert Request
    $stmt = $conn->prepare("
        INSERT INTO barangay_clearance_requests
        (resident_id, fullname, email, phone, civil_status, date_of_birth, house_street, city, province,
         date_of_residency, years_residency, purpose, valid_id_url, barangay_name, permit_type, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $status = "Pending";
    $stmt->bind_param(
        "isssssssssssssss",
        $resident_id, $fullname, $email, $phone, $civil_status, $date_of_birth,
        $house_street, $city, $province, $date_of_residency, $years_residency,
        $purpose, $file_path, $barangay, $permit_type, $status
    );
    if ($stmt->execute()) {
        $message = "<span style='color:#16a34a'>✔️ Request submitted successfully!</span>";
    } else {
        $message = "<span style='color:#dc2626'>❌ Failed to submit request.</span>";
    }
}

// 🗂️ Handle Cancellation
if (isset($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);
    $stmt = $conn->prepare("UPDATE barangay_clearance_requests SET status='Cancelled' WHERE id=? AND resident_id=?");
    $stmt->bind_param("ii", $cancel_id, $resident_id);
    $stmt->execute();
    header("Location: permitsPage.php");
    exit();
}

// 📄 Fetch User Requests
$stmt = $conn->prepare("SELECT * FROM barangay_clearance_requests WHERE resident_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?> 

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Barangay Requests</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
/* ===========================================================
   Servigo Civic-Modern UI (Frontend Only)
   — Reworked "My Requests" with Tabs (Events Standard)
=========================================================== */
:root {
  --brand-green: #047857;
  --accent-blue: #3b82f6;
  --text: #1e293b;
  --muted: #6b7280;
  --white: #ffffff;
  --bg: #f9fafb;
  --border: #e5e7eb;
  --radius: 14px;
  --shadow: 0 4px 12px rgba(0,0,0,0.08);
  --transition: 0.3s ease;
}

/* Base */
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:"Parkinsans","Outfit","Roboto",system-ui,sans-serif;
  background:var(--bg);
  color:var(--text);
}

/* Tabs (main nav) */
.navtabs{
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:8px;
  padding:10px 0.5rem;
  background:var(--white);
  box-shadow:var(--shadow);
  position:sticky;
  top:0;
  z-index:100;
}
.tabbtn{
  all:unset;
  cursor:pointer;
  font-weight:600;
  padding:8px 16px;
  border-radius:999px;
  color:var(--text);
  background:#f3f4f6;
  transition:var(--transition);
}
.tabbtn:hover{background:#e5e7eb;}
.tabbtn.active{
  background:linear-gradient(135deg,var(--brand-green),var(--accent-blue));
  color:#fff;
}

/* Hero */
.hero{text-align:center;margin:2rem 0 1.5rem;}
.hero h1{color:var(--brand-green);font-size:1.9rem;margin-bottom:0.4rem;}
.hero p{color:var(--muted);font-size:1rem;}

/* Permit Cards */
.grid{
  display:grid;
  gap:1.2rem;
  grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
  padding:0 1rem;
}
.card{
  background:var(--white);
  border-radius:var(--radius);
  border:1px solid var(--border);
  box-shadow:var(--shadow);
  padding:1.5rem 1.25rem;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  transition:transform 0.25s var(--transition), box-shadow 0.25s var(--transition);
}
.card:hover{transform:translateY(-4px);box-shadow:0 6px 16px rgba(0,0,0,0.1);}
.card h3{font-size:1.2rem;font-weight:700;color:var(--brand-green);margin-bottom:0.6rem;}
.card p{color:var(--muted);font-size:0.95rem;flex-grow:1;margin-bottom:1rem;line-height:1.45;}
.btn{
  all:unset;
  cursor:pointer;
  padding:10px 14px;
  text-align:center;
  border-radius:10px;
  font-weight:600;
  background:linear-gradient(135deg,var(--brand-green),var(--accent-blue));
  color:#fff;
  transition:var(--transition);
}
.btn:hover{opacity:0.9;}

/* My Requests (Tabs + Grid) */
.requests-section{
  margin-top:3rem;
  padding:0 1rem 3rem;
}

/* Filter Tabs (like events) */
.request-tabs{
  display:flex;
  gap:0.75rem;
  border-bottom:2px solid #e5e7eb;
  margin-bottom:1.25rem;
  overflow-x:auto;
  scrollbar-width:none;
  padding:0 0.5rem;
  scroll-padding:0.75rem;
}
.request-tabs::-webkit-scrollbar{display:none;}
.request-tab{
  background:none;
  border:none;
  font-size:1rem;
  font-weight:600;
  color:var(--muted);
  padding:0.55rem 1.1rem;
  cursor:pointer;
  border-radius:999px;
  flex-shrink:0;
  transition:var(--transition);
}
.request-tab:hover{background:#e9eefb;}
.request-tab.active{
  color:#fff;
  background:var(--brand-green);
}

/* Requests Grid */
.requests-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
  gap:1.25rem;
  transition:opacity .28s ease, transform .28s ease;
}
.requests-grid.fading{
  opacity:0;
  transform:translateY(8px);
}

/* Individual Request Card */
.request-card{
  background:var(--white);
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,0.05);
  padding:1.25rem 1.25rem;
  transition:var(--transition);
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}
.request-card:hover{
  transform:translateY(-2px);
  box-shadow:0 6px 18px rgba(0,0,0,0.08);
}
.request-name{
  font-weight:600;
  font-size:1.05rem;
  display:flex;
  align-items:center;
  gap:6px;
  margin-bottom:0.4rem;
}
.request-type{font-size:0.95rem;color:var(--accent-blue);font-weight:500;}
.request-date{font-size:0.85rem;color:var(--muted);margin-top:0.4rem;}
.request-actions{
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-top:0.75rem;
}
.status-badge{
  padding:6px 12px;
  border-radius:8px;
  font-size:0.8rem;
  font-weight:600;
  color:#fff;
  text-transform:capitalize;
}
.status-pending{background:#f59e42;}
.status-ready{background:#0ea5e9;}
.status-declined{background:#ef4444;}
.status-completed{background:#374151;}
.btn-cancel{
  all:unset;
  background:#ef4444;
  color:#fff;
  padding:6px 12px;
  border-radius:8px;
  font-size:0.8rem;
  font-weight:600;
  cursor:pointer;
  transition:var(--transition);
}
.btn-cancel:hover{transform:scale(0.97);opacity:0.9;}
.empty{
  text-align:center;
  color:var(--muted);
  font-size:0.95rem;
  margin-top:1.4rem;
  padding:1rem;
  border:1px dashed var(--border);
  border-radius:var(--radius);
}

/* Modal */
.modal{
  display:none;
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.35);
  backdrop-filter:blur(4px);
  z-index:2000;
  align-items:center;
  justify-content:center;
  padding:1rem;
}
.modal.show{display:flex;}
.modal form{
  background:var(--white);
  border-radius:var(--radius);
  box-shadow:0 8px 24px rgba(0,0,0,0.12);
  width:100%;
  max-width:480px;
  display:flex;
  flex-direction:column;
  animation:fadeIn .35s ease;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
.modal .body{
  padding:1.5rem;
  overflow-y:auto;
  max-height:75vh;
}
.modal label{
  display:block;
  font-weight:600;
  margin-top:0.6rem;
  font-size:0.9rem;
}
.modal input,
.modal textarea{
  width:100%;
  padding:10px;
  border:1px solid var(--border);
  border-radius:10px;
  font-size:0.95rem;
  margin-top:4px;
  transition:border-color var(--transition);
}
.modal input:focus,
.modal textarea:focus{
  border-color:var(--accent-blue);
  outline:none;
}
.modal h2{font-size:1.3rem;color:var(--brand-green);margin-top:0;}
.modal .footer{
  padding:1rem;
  text-align:center;
  border-top:1px solid var(--border);
}
.modal .footer .btn{
  padding:10px 18px;
  border-radius:10px;
  font-size:0.95rem;
}
.modal .footer .cancelBtn{background:#ef4444;}
footer{color:var(--muted);text-align:center;padding:20px;font-size:14px;}
</style>

<main class="container">
  <section class="hero">
    <h1>Barangay Permits & Documents</h1>
    <p>Apply online for clearances, certificates, and permits — track status anytime.</p>
  </section>

<div class="grid">
  <?php 
  // Define permits safely inside UI (frontend only)
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
  foreach ($permits as $p):
  ?>
    <div class="card">
      <h3><?= htmlspecialchars($p[0]) ?></h3>
      <p><?= htmlspecialchars($p[1]) ?><br><strong>Requirements:</strong> <?= htmlspecialchars($p[2]) ?></p>
      <button class="btn" onclick="openForm('<?= htmlspecialchars($p[0]) ?>')">Apply Now</button>
    </div>
  <?php endforeach; ?>
</div>


  <section class="requests-section">
    <h2 style="margin-bottom:0.5rem;">My Requests</h2>

    <!-- Filter Tabs -->
    <div class="request-tabs" id="requestTabs">
      <button class="request-tab active" data-filter="all">All</button>
      <button class="request-tab" data-filter="pending">Pending</button>
      <button class="request-tab" data-filter="ready">Ready</button>
      <button class="request-tab" data-filter="declined">Declined</button>
      <button class="request-tab" data-filter="completed">Completed</button>
    </div>

    <!-- Requests Grid -->
    <div class="requests-grid" id="requestsGrid">
      <?php if (empty($requests)): ?>
        <div class="empty" id="emptyState"><i class='bx bx-folder-open' style="font-size:2rem;"></i><br>No requests yet.</div>
      <?php else: ?>
        <?php foreach ($requests as $r): ?>
          <article class="request-card" data-status="<?= strtolower($r['status']) ?>">
            <div class="request-name"><i class="bx bx-user"></i> <?= htmlspecialchars($r['fullname']) ?></div>
            <div class="request-type"><?= htmlspecialchars($r['permit_type']) ?></div>
            <div class="request-actions">
              <span class="status-badge status-<?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
              <?php if ($r['status'] === 'Pending'): ?>
                <a href="?cancel=<?= $r['id'] ?>" class="btn-cancel"><i class='bx bx-x'></i> Cancel</a>
              <?php endif; ?>
            </div>
            <div class="request-date"><i class='bx bx-calendar'></i> <?= date('F j, Y', strtotime($r['created_at'])) ?></div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

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
      <button class="btn" type="submit">Submit Request</button>
      <button type="button" class="btn cancelBtn" onclick="closeForm()">Cancel</button>
      <div style="margin-top:8px;"><?= $message ?></div>
    </div>
  </form>
</div>

<footer>© 2025 Servigo</footer>

<script>
/* ===========================================================
   Interactive Filtering (Matches Events.php Behavior)
=========================================================== */
const tabs = document.querySelectorAll('.request-tab');
const grid = document.getElementById('requestsGrid');
const cards = Array.from(document.querySelectorAll('.request-card'));
const empty = document.getElementById('emptyState');

tabs.forEach(tab=>{
  tab.addEventListener('click',()=>{
    if(tab.classList.contains('active')) return;
    tabs.forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    applyFilter(tab.dataset.filter,true);
  });
});

function applyFilter(filter, animated){
  if(animated) grid.classList.add('fading');
  setTimeout(()=>{
    let visible=0;
    cards.forEach(card=>{
      const match=(filter==='all'||card.dataset.status===filter);
      card.style.display=match?'flex':'none';
      if(match) visible++;
    });
    if(empty) empty.hidden=visible>0;
    if(animated) requestAnimationFrame(()=>grid.classList.remove('fading'));
  }, animated?160:0);
}

/* Smooth tab scroll center (optional aesthetic) */
window.addEventListener('load',()=>{
  const active=document.querySelector('.request-tab.active');
  if(active){
    const container=document.getElementById('requestTabs');
    const rect=active.getBoundingClientRect();
    const cRect=container.getBoundingClientRect();
    container.scrollBy({left:(rect.left+rect.width/2)-(cRect.left+cRect.width/2),behavior:'smooth'});
  }
});

/* Modal Controls */
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
</script>

</body>
</html>
