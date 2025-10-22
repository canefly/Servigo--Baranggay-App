<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

/* ================================
   SESSION DATA
================================ */
$resident_id       = $_SESSION['sg_id']   ?? null;
$resident_email    = $_SESSION['sg_email']?? '';
$resident_barangay = $_SESSION['sg_brgy'] ?? '';
$resident_name     = $_SESSION['sg_name'] ?? '';

if (!$resident_id) {
  header("Location: loginPage.php");
  exit;
}

/* ================================
   FETCH CURRENT STATUS + LATEST PROOF
================================ */
$status = "Unverified";
$latest_proof_url = "";  // from resident_verifications.latest
$latest_id_type   = "";

$stmt = $conn->prepare("SELECT verification_status FROM residents WHERE id=?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
  $status = $row['verification_status'] ?: "Unverified";
}
$stmt->close();

// pull latest submitted proof (kung meron)
$lp = $conn->prepare("
  SELECT id_type, valid_id_url
  FROM resident_verifications
  WHERE resident_id=?
  ORDER BY submitted_at DESC, id DESC
  LIMIT 1
");
$lp->bind_param("i", $resident_id);
$lp->execute();
$lr = $lp->get_result()->fetch_assoc();
$lp->close();

if ($lr) {
  $latest_proof_url = $lr['valid_id_url'] ?? "";
  $latest_id_type   = $lr['id_type']      ?? "";
}

/* ================================
   HANDLE SUBMIT (UPLOAD + INSERT)
================================ */
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $valid_id_type = trim($_POST['validID'] ?? '');
  $full_name     = trim($_POST['fullName'] ?? '');
  $barangay      = trim($_POST['barangay'] ?? '');
  $email         = trim($_POST['email'] ?? '');

  // basic validations
  if ($valid_id_type === '' || $full_name === '' || $barangay === '' || $email === '') {
    $msg = "❌ Please complete all required fields.";
  } elseif (!isset($_FILES['uploadID']) || $_FILES['uploadID']['error'] !== UPLOAD_ERR_OK) {
    $msg = "❌ File upload failed. Please try again.";
  } else {
    $file = $_FILES['uploadID'];

    // Optional: limit file types (images + pdf)
    $allowed_mimes = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
    if (!in_array($file['type'], $allowed_mimes, true)) {
      $msg = "❌ Invalid file type. Please upload an image or PDF.";
    } else {
      // Save file
      $upload_dir = __DIR__ . "/../uploads/verification/";
      if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }

      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
      $safeBase = preg_replace("/[^a-zA-Z0-9._-]/", "_", pathinfo($file['name'], PATHINFO_FILENAME));
      $fname = time() . "_" . $resident_id . "_" . $safeBase . "." . $ext;

      $target_abs = $upload_dir . $fname;
      $target_rel = "uploads/verification/" . $fname;

      if (!move_uploaded_file($file['tmp_name'], $target_abs)) {
        $msg = "❌ Failed to save file to server.";
      } else {
        // Insert into resident_verifications
        $ins = $conn->prepare("
          INSERT INTO resident_verifications (resident_id, id_type, valid_id_url, status)
          VALUES (?, ?, ?, 'Pending')
        ");
        $ins->bind_param("iss", $resident_id, $valid_id_type, $target_rel);
        $ok1 = $ins->execute();
        $ins->close();

        // Update residents.verification_status
        $up = $conn->prepare("UPDATE residents SET verification_status='Pending' WHERE id=?");
        $up->bind_param("i", $resident_id);
        $ok2 = $up->execute();
        $up->close();

        // Insert admin notification (barangay scope)
        $title = "New Resident Verification";
        $message = $resident_name . " submitted an ID for verification.";
        $link = "/BARANGAY/verification.php"; // adjust path if needed

        $notif = $conn->prepare("
          INSERT INTO notifications 
            (barangay_name, recipient_type, recipient_id, source_table, source_id, type, title, message, link)
          VALUES 
            (?, 'admin', NULL, 'resident_verifications', ?, 'verification_submitted', ?, ?, ?)
        ");
        $source_id = $resident_id; // pwede ring last insert id ng verification kung gusto mo
        $notif->bind_param("sisss", $resident_barangay, $source_id, $title, $message, $link);
        $notif->execute();
        $notif->close();

        if ($ok1 && $ok2) {
          $msg = "✅ Your document has been uploaded. Verification pending.";
          $status = "Pending";
          $latest_proof_url = $target_rel;
          $latest_id_type   = $valid_id_type;
        } else {
          $msg = "❌ Database error while saving verification.";
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Account · Servigo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
      --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
      --shadow:0 4px 12px rgba(0,0,0,.08); --radius:14px;
    }
    *{box-sizing:border-box; margin:0; padding:0;}
    body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);display:flex;justify-content:center;padding:20px;}
    .container{width:100%; max-width:750px; display:flex; flex-direction:column; gap:20px;}
    .profile-card{background:var(--card); padding:20px; border-radius:var(--radius); box-shadow:var(--shadow); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;}
    .profile-info{display:flex; gap:14px; align-items:center; flex:1;}
    .user-icon{font-size:40px; color:var(--brand); padding:8px; border-radius:50%; background:#f3f4f6;}
    .profile-info h2{margin:0; font-size:18px; color:var(--brand);}
    .profile-info p{font-size:14px; color:var(--muted); margin:2px 0;}
    .status-pill{padding:6px 14px; border-radius:999px; font-size:13px; font-weight:600; white-space:nowrap;}
    .status-pill.unverified{background:#fef2f2; color:#b91c1c;}
    .status-pill.pending{background:#e0f2fe; color:#0369a1;}
    .status-pill.verified{background:#dcfce7; color:#15803d;}

    .form-card{background:var(--card); padding:24px 20px; border-radius:var(--radius); box-shadow:var(--shadow);}
    .form-card h1{font-size:20px; margin-bottom:6px; color:var(--brand);}
    .form-card p{font-size:14px; color:var(--muted); margin-bottom:20px;}

    .form-group{margin-bottom:18px;}
    label{font-weight:600; font-size:14px; display:block; margin-bottom:6px;}
    input, select{width:100%; padding:12px; font-size:15px; border:1px solid var(--border); border-radius:8px; background:#fff;}
    input:focus, select:focus{border-color:var(--brand); outline:none; box-shadow:0 0 0 3px rgba(30,64,175,.15);}

    input[type="file"]{border:2px dashed var(--border); padding:10px; background:#fafafa; cursor:pointer;}
    input[type="file"]::-webkit-file-upload-button{margin-right:10px; background:var(--brand); color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;}

    #preview{margin-top:12px; max-width:100%; border-radius:8px; border:1px solid var(--border); display:none;}
    img.preview{max-width:100%; border-radius:8px; margin-top:10px; border:1px solid var(--border);}

    .btn{width:100%; padding:14px; border:none; background:linear-gradient(135deg,var(--brand),var(--accent)); color:#fff; border-radius:8px; font-weight:600; font-size:15px; cursor:pointer; transition:.2s;}
    .btn:hover{opacity:.9; transform:translateY(-1px);}

    .form-actions{display:flex; justify-content:space-between; gap:10px; margin-top:10px;}
    .ghost-btn{all:unset; cursor:pointer; padding:12px 20px; border-radius:8px; font-weight:600; font-size:14px; border:1px solid var(--border); color:var(--brand); background:#f9fafb; text-align:center;}
    .ghost-btn:hover{background:#f3f4f6;}
    footer{text-align:center; color:var(--muted); font-size:13px; margin-top:20px;}
  </style>
</head>
<body>
  <div class="container">

    <!-- Profile Card -->
    <section class="card profile-card">
      <div class="profile-info">
        <i class='bx bx-user user-icon'></i>
        <div>
          <h2><?= htmlspecialchars($resident_name) ?></h2>
          <p>Barangay: <?= htmlspecialchars($resident_barangay) ?></p>
          <p>Email: <?= htmlspecialchars($resident_email) ?></p>
        </div>
      </div>
      <div class="profile-actions">
        <?php
          $cls = strtolower($status);
          if ($cls === 'pending')   $cls = 'pending';
          elseif ($cls === 'verified') $cls = 'verified';
          else $cls = 'unverified';
        ?>
        <span class="status-pill <?= $cls ?>"><?= htmlspecialchars($status) ?></span>
      </div>
    </section>

    <!-- Verification Form / Status -->
    <section class="form-card">
      <h1><i class='bx bx-id-card'></i> Verify Your Account</h1>
      <p>Upload your valid ID. Your Barangay Admin will review it within 1–3 days.</p>

      <?php if($msg): ?>
        <div style="text-align:center;margin-bottom:10px;font-weight:500;"><?= $msg ?></div>
      <?php endif; ?>

      <?php if ($status !== 'Verified' && $status !== 'Pending'): ?>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="validID">Valid ID Type</label>
            <select name="validID" id="validID" required>
              <option value="">-- Select ID --</option>
              <option value="National ID"      <?= ($latest_id_type==='National ID'?'selected':'') ?>>National ID</option>
              <option value="Passport"         <?= ($latest_id_type==='Passport'?'selected':'') ?>>Passport</option>
              <option value="Driver’s License" <?= ($latest_id_type==="Driver’s License"?'selected':'') ?>>Driver’s License</option>
              <option value="Voter’s ID"       <?= ($latest_id_type==="Voter’s ID"?'selected':'') ?>>Voter’s ID</option>
            </select>
          </div>

          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input name="fullName" id="fullName" type="text" value="<?= htmlspecialchars($resident_name) ?>" required>
          </div>

          <div class="form-group">
            <label for="barangay">Barangay</label>
            <input name="barangay" id="barangay" type="text" value="<?= htmlspecialchars($resident_barangay) ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input name="email" id="email" type="email" value="<?= htmlspecialchars($resident_email) ?>" required>
          </div>

          <div class="form-group">
            <label for="uploadID">Upload Valid ID (image or PDF)</label>
            <input name="uploadID" id="uploadID" type="file" accept="image/*,application/pdf" onchange="previewImage(event)" required>
            <img id="preview" alt="Preview" class="preview">
          </div>

          <div class="form-actions">
            <button type="button" class="ghost-btn" onclick="history.back()">← Back</button>
            <button type="submit" class="btn">Verify Account</button>
          </div>
        </form>

      <?php elseif ($status === 'Pending'): ?>
        <p style="color:#0369a1;">⏳ Your verification is under review.</p>
        <?php if ($latest_proof_url): ?>
          <p>Uploaded file:</p>
          <?php if (preg_match('/\.pdf$/i', $latest_proof_url)): ?>
            <a href="../<?= htmlspecialchars($latest_proof_url) ?>" target="_blank">Open PDF</a>
          <?php else: ?>
            <img src="../<?= htmlspecialchars($latest_proof_url) ?>" class="preview" alt="Uploaded ID">
          <?php endif; ?>
        <?php endif; ?>

      <?php else: ?>
        <p style="color:#15803d;">✅ You are already verified!</p>
      <?php endif; ?>
    </section>

    <footer>© 2025 Servigo | Barangay Verification Portal</footer>
  </div>

<script>
function previewImage(event){
  const [file] = event.target.files;
  if(!file) return;
  const preview = document.getElementById('preview');
  const isImg = file.type.startsWith('image/');
  if (isImg) {
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
}
</script>
</body>
</html>
