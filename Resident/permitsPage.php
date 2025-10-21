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
:root {
  --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
  --shadow:0 4px 12px rgba(0,0,0,.08); --radius:16px;
  --pending:#f59e42; --declined:#ef4444; --ready:#0ea5e9; --completed:#374151;
}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);}
.container{max-width:1100px;margin:0 auto;padding:16px}
.navtabs{display:flex;gap:8px;justify-content:center;background:#f9fafb;
  padding:10px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
.tabbtn{all:unset;cursor:pointer;font-weight:600;padding:8px 14px;
  border-radius:10px;color:var(--text);border:1px solid var(--border);background:#f3f4f6}
.tabbtn.active{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;font-weight:700}
.hero{text-align:center;margin:20px 0}
.hero h1{margin:0;font-size:2rem;color:var(--brand)}
.hero p{color:var(--muted)}
.grid{display:grid;gap:14px}
.cols-3{grid-template-columns:repeat(3,1fr)}
@media(max-width:1024px){.cols-3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:680px){.cols-3{grid-template-columns:1fr}}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:16px;box-shadow:var(--shadow);transition:.15s;display:flex;flex-direction:column;justify-content:space-between}
.card:hover{transform:translateY(-2px)}
.card h3{margin:0;color:var(--brand)}
.card p{flex-grow:1;color:var(--muted);margin:0 0 12px}
.btn{all:unset;cursor:pointer;padding:10px 14px;border-radius:10px;
  font-weight:600;text-align:center;background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff}
.btn:hover{opacity:.9}
footer{color:var(--muted);text-align:center;padding:20px;font-size:14px}
.request-card {background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,.05);
  padding:16px 18px;margin-bottom:14px;transition:.2s ease;}
.request-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); transform:translateY(-2px); }
.request-name{font-weight:600;font-size:1.05rem;margin-bottom:6px;color:var(--text);display:flex;align-items:center;gap:6px;}
.request-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:6px;}
.request-type{font-size:.95rem;color:var(--brand);font-weight:500;}
.request-actions{display:flex;align-items:center;gap:8px;}
.status-badge{all:unset;padding:6px 12px;border-radius:8px;font-size:.8rem;font-weight:600;color:#fff;transition:.2s;}
.status-pending{background:var(--pending);}
.status-ready{background:var(--ready);}
.status-declined{background:var(--declined);}
.status-completed{background:var(--completed);}
.request-date{font-size:.85rem;color:var(--muted);}
.btn-cancel{all:unset;cursor:pointer;padding:6px 12px;border-radius:8px;font-size:.8rem;font-weight:600;background:#ef4444;color:#fff;transition:.2s;}
.btn-cancel:hover{opacity:.9;transform:scale(.97);}
.empty{padding:16px;text-align:center;color:var(--muted);border:1px dashed var(--border);border-radius:12px;margin-top:10px}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:200;background:rgba(30,40,60,.2);backdrop-filter:blur(2px);
  align-items:center;justify-content:center;padding:10px;}
.modal.show{display:flex;}
.modal form{max-width:480px;width:100%;background:#fff;border-radius:18px;box-shadow:0 6px 24px rgba(30,40,60,.15);display:flex;flex-direction:column;}
.modal .body{padding:20px;overflow-y:auto;max-height:80vh;}
.modal label{font-weight:600;margin-top:10px;display:block}
.modal input,.modal textarea{width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;font-size:15px;margin-top:4px}
</style>
</head>
<body>

<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn">News</a>
  <a href="permitsPage.php" class="tabbtn active">Permits</a>
  <a href="storesPage.php" class="tabbtn">Stores</a>
  <a href="events.php" class="tabbtn">Events</a>
</nav>

<main class="container">
  <section class="hero">
    <h1>Barangay Permits & Documents</h1>
    <p>Apply online for clearances, residency certificates, and permits. Track status without visiting the hall.</p>
  </section>

  <!-- Apply cards -->
  <div class="grid cols-3">
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
    foreach($permits as $p){
      echo "
      <div class='card'>
        <h3>{$p[0]}</h3>
        <p>{$p[1]}<br><strong>Requirements:</strong> {$p[2]}</p>
        <button class='btn' onclick=\"openForm('{$p[0]}')\">Apply Now</button>
      </div>";
    }
    ?>
  </div>

  <!-- My Requests -->
  <section style="margin-top:40px;">
    <h2 style="color:#1e40af;">My Requests</h2>
    <?php if (empty($requests)): ?>
      <div class="empty"><i class='bx bx-folder-open' style="font-size:2rem;"></i><br>You have not submitted any requests yet.</div>
    <?php else: ?>
      <?php foreach ($requests as $r): ?>
        <div class="request-card">
          <div class="request-name"><i class="bx bx-user"></i> <?= htmlspecialchars($r['fullname']) ?></div>
          <div class="request-row">
            <span class="request-type"><?= htmlspecialchars($r['permit_type']) ?></span>
            <div class="request-actions">
              <span class="status-badge status-<?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
              <?php if ($r['status'] === 'Pending'): ?>
                <a href="?cancel=<?= $r['id'] ?>" class="btn-cancel"><i class='bx bx-x'></i> Cancel</a>
              <?php endif; ?>
            </div>
          </div>
          <div class="request-date"><i class='bx bx-calendar'></i> <?= date('F j, Y', strtotime($r['created_at'])) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<!-- Modal -->
<div id="applyModal" class="modal">
  <form method="POST" enctype="multipart/form-data">
    <div class="body">
      <h2 style="margin-top:0;color:#1e40af" id="modalTitle">Apply for Permit</h2>
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
    <div style="padding:15px;text-align:center;">
      <button class="btn" type="submit">Submit Request</button>
      <button type="button" class="btn cancelBtn" style="background:#ef4444;color:#fff" onclick="closeForm()">Cancel</button>
      <div style="margin-top:8px;"><?= $message ?></div>
    </div>
  </form>
</div>

<footer>© 2025 Servigo</footer>

<script>
function openForm(type){
  document.getElementById("applyModal").classList.add("show");
  document.getElementById("permitTypeInput").value = type;
  document.getElementById("modalTitle").textContent = "Barangay " + type + " Request";
  document.body.style.overflow='hidden';
}
function closeForm(){
  document.getElementById("applyModal").classList.remove("show");
  document.body.style.overflow='';
}
</script>
</body>
</html>
