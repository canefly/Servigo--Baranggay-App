<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php'; // topbar stays intact

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';
$email       = $_SESSION['sg_email'] ?? '';
$message     = '';

/* ==========================
   Fetch resident info
========================== */
$resident_name = '';
$stmt = $conn->prepare("SELECT first_name, last_name, verification_status FROM residents WHERE id=?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
  $resident_name = $row['first_name'] . ' ' . $row['last_name'];
  $status = $row['verification_status'];
} else {
  die("Resident not found.");
}
$stmt->close();

/* ==========================
   Handle form submit
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_type = trim($_POST['id_type'] ?? '');
  $full_name = trim($_POST['full_name'] ?? '');
  $barangay_input = trim($_POST['barangay'] ?? '');
  $email_input = trim($_POST['email'] ?? '');
  $file_path = null;

  // 📎 Upload file
  if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
    $dir = __DIR__ . "/../uploads/verifications/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $fname = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES['valid_id']['name']);
    $dest = $dir . $fname;

    if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $dest)) {
      $file_path = "uploads/verifications/" . $fname;
    } else {
      $message = "<span style='color:#b91c1c;'>❌ File upload failed.</span>";
    }
  }

  if ($file_path) {
    // Insert into resident_verifications
    $stmt = $conn->prepare("
      INSERT INTO resident_verifications (resident_id, id_type, valid_id_url, status)
      VALUES (?, ?, ?, 'Pending')
    ");
    $stmt->bind_param("iss", $resident_id, $id_type, $file_path);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
      // Send notification to admin
      $title = "New Resident Verification";
      $notif_msg = "$resident_name submitted an ID for verification.";
      $stmt2 = $conn->prepare("
        INSERT INTO notifications 
        (barangay_name, recipient_type, type, title, message, source_table, source_id)
        VALUES (?, 'admin', 'verification', ?, ?, 'resident_verifications', ?)
      ");
      $stmt2->bind_param("sssi", $barangay, $title, $notif_msg, $resident_id);
      $stmt2->execute();
      $stmt2->close();

      $message = "<span style='color:#16a34a;'>✅ Verification submitted successfully!</span>";
    } else {
      $message = "<span style='color:#b91c1c;'>❌ Failed to save verification.</span>";
    }
  }
}
?>

<!-- ✅ HTML starts BELOW the included topbar -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Account · Servigo</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Parkinsans:wght@400;700&display=swap" rel="stylesheet">

<style>
:root {
  --bg:#f5f7fa; --card:#fff; --text:#1e1e1e; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
  --shadow:0 4px 10px rgba(0,0,0,.08); --radius:14px;
}
body {
  font-family:"Parkinsans","Outfit",sans-serif;
  background:var(--bg);
  color:var(--text);
  margin:0;
}
.container {
  width:100%;
  max-width:740px;
  margin:0 auto;
  padding:  20px;
  display:flex;
  flex-direction:column;
  gap:24px;
  margin-top: 0px;
}
.profile-card {
  background:var(--card);
  padding:20px;
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
  gap:16px;
}
.profile-info {
  display:flex;
  gap:14px;
  align-items:center;
  flex:1;
}
.user-icon {
  font-size:40px;
  color:var(--brand);
  background:#eef2ff;
  padding:10px;
  border-radius:50%;
}
.profile-info h2 {
  margin:0;
  font-size:1.05rem;
  font-weight:600;
  color:var(--brand);
}
.profile-info p {
  font-size:.9rem;
  color:var(--muted);
  margin:2px 0;
}
.status-pill {
  padding:6px 14px;
  border-radius:999px;
  font-size:.8rem;
  font-weight:600;
  white-space:nowrap;
}
.status-pill.unverified {background:#fef2f2;color:#b91c1c;}
.status-pill.verified {background:#dcfce7;color:#15803d;}
.form-card {
  background:var(--card);
  padding:24px 20px;
  border-radius:var(--radius);
  box-shadow:var(--shadow);
}
.form-card h1 {
  font-family:"Outfit";
  font-size:1.2rem;
  color:var(--brand);
  margin-bottom:4px;
  font-weight:700;
  display:flex;
  align-items:center;
  gap:6px;
}
.form-card p {font-size:.9rem;color:var(--muted);margin-bottom:20px;}
label {font-weight:600;font-size:.9rem;display:block;margin-bottom:6px;}
input,select {
  width:100%;
  padding:11px 12px;
  font-size:.95rem;
  border:1px solid var(--border);
  border-radius:8px;
  background:#fff;
}
input:focus,select:focus {
  border-color:var(--brand);
  outline:none;
  box-shadow:0 0 0 3px rgba(30,64,175,.15);
}
input[type="file"] {
  border:2px dashed var(--border);
  background:#fafafa;
  padding:10px;
  cursor:pointer;
}
input[type="file"]::-webkit-file-upload-button {
  margin-right:10px;
  background:var(--brand);
  color:#fff;
  border:none;
  padding:8px 12px;
  border-radius:6px;
  cursor:pointer;
}
.btn {
  width:100%;
  padding:13px;
  border:none;
  cursor:pointer;
  border-radius:8px;
  font-weight:600;
  font-size:.95rem;
  color:#fff;
  background:linear-gradient(135deg,var(--brand),var(--accent));
  transition:.2s;
}
.btn:hover {opacity:.9;transform:translateY(-1px);}
.form-actions {
  display:flex;
  justify-content:space-between;
  gap:10px;
  margin-top:14px;
}
.ghost-btn {
  all:unset;
  cursor:pointer;
  padding:11px 20px;
  border:1px solid var(--border);
  border-radius:8px;
  color:var(--brand);
  background:#f9fafb;
  font-weight:600;
  font-size:.9rem;
  text-align:center;
  transition:.2s;
}
.ghost-btn:hover {background:#f3f4f6;}
footer {
  text-align:center;
  color:var(--muted);
  font-size:.8rem;
  margin-top:20px;
  padding-bottom:20px;
}
</style>
</head>

<body>
<div class="container">

  <!-- Resident Profile -->
  <section class="profile-card">
    <div class="profile-info">
      <i class='bx bx-user user-icon'></i>
      <div>
        <h2><?= htmlspecialchars($resident_name) ?></h2>
        <p>Barangay: <?= htmlspecialchars($barangay) ?></p>
        <p>Email: <?= htmlspecialchars($email) ?></p>
      </div>
    </div>
    <span class="status-pill <?= strtolower($status) ?>"><?= htmlspecialchars($status) ?></span>
  </section>

  <!-- Verification Form -->
  <section class="form-card">
    <h1><i class='bx bx-shield-quarter'></i> Verify Your Account</h1>
    <p>Upload a valid ID. Your Barangay Admin will review within 1–3 days.</p>

    <form method="POST" enctype="multipart/form-data">
      <label>Valid ID Type</label>
      <select name="id_type" required>
        <option value="">-- Select ID --</option>
        <option>National ID</option>
        <option>Passport</option>
        <option>Driver’s License</option>
        <option>Voter’s ID</option>
      </select>

      <label>Full Name</label>
      <input name="full_name" type="text" value="<?= htmlspecialchars($resident_name) ?>" required>

      <label>Barangay</label>
      <input name="barangay" type="text" value="<?= htmlspecialchars($barangay) ?>" required>

      <label>Email</label>
      <input name="email" type="email" value="<?= htmlspecialchars($email) ?>" required>

      <label>Upload Valid ID</label>
      <input type="file" name="valid_id" accept=".jpg,.jpeg,.png,.pdf" required>

      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="history.back()">← Back</button>
        <button type="submit" class="btn">Verify Account</button>
      </div>
      <div style="margin-top:10px;"><?= $message ?></div>
    </form>
  </section>

  <footer>© 2025 Servigo | Barangay Verification Portal</footer>
</div>
</body>
</html>
