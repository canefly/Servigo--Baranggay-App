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

if (!$resident_id) { header("Location: loginPage.php"); exit; }

/* ================================
   FETCH STATUS + LATEST VERIFICATION
================================ */
$status="Unverified"; $latest_proof_url=""; $latest_id_type=""; $remarks_text="";
$stmt=$conn->prepare("SELECT verification_status FROM residents WHERE id=?");
$stmt->bind_param("i",$resident_id);
$stmt->execute();
$res=$stmt->get_result();
if($r=$res->fetch_assoc()) $status=$r['verification_status'] ?: 'Unverified';
$stmt->close();

$lp=$conn->prepare("SELECT id_type, valid_id_url, remarks FROM resident_verifications WHERE resident_id=? ORDER BY submitted_at DESC, id DESC LIMIT 1");
$lp->bind_param("i",$resident_id);
$lp->execute();
$lr=$lp->get_result()->fetch_assoc();
$lp->close();
if($lr){
  $latest_proof_url=$lr['valid_id_url'] ?? '';
  $latest_id_type=$lr['id_type'] ?? '';
  $remarks_text=$lr['remarks'] ?? '';
}

/* ================================
   HANDLE VERIFICATION / UPDATE
================================ */
$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mode = $_POST['mode'] ?? 'new';
  $valid_id_type = trim($_POST['validID'] ?? '');
  $full_name = trim($_POST['fullName'] ?? '');
  $barangay = trim($_POST['barangay'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if ($valid_id_type===''||$barangay===''||$email==='') {
    $flash = "<div class='alert alert-danger mb-3'>❌ Please complete all required fields.</div>";
  } elseif (!isset($_FILES['uploadID']) || $_FILES['uploadID']['error']!==UPLOAD_ERR_OK) {
    $flash = "<div class='alert alert-danger mb-3'>❌ File upload failed. Please try again.</div>";
  } else {
    $file=$_FILES['uploadID'];
    $allowed=['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
    if(!in_array($file['type'],$allowed,true)){
      $flash = "<div class='alert alert-danger mb-3'>❌ Invalid file type. Please upload an image or PDF.</div>";
    } else {
      $dir=__DIR__."/../uploads/verification/";
      if(!is_dir($dir)) mkdir($dir,0777,true);

      $ext=pathinfo($file['name'], PATHINFO_EXTENSION);
      $safe=preg_replace("/[^a-zA-Z0-9._-]/","_", pathinfo($file['name'], PATHINFO_FILENAME));
      $fname=time()."_".$resident_id."_".$safe.".".$ext;
      $abs=$dir.$fname; $rel="uploads/verification/".$fname;

      if(!move_uploaded_file($file['tmp_name'],$abs)){
        $flash = "<div class='alert alert-danger mb-3'>❌ Failed to save file to server.</div>";
      } else {
        // Insert verification record
        $ins=$conn->prepare("INSERT INTO resident_verifications (resident_id,id_type,valid_id_url,status) VALUES (?,?,?,'Pending')");
        $ins->bind_param("iss",$resident_id,$valid_id_type,$rel);
        $ok1=$ins->execute(); $ins->close();

        // Update resident status
        $up=$conn->prepare("UPDATE residents SET verification_status='Pending' WHERE id=?");
        $up->bind_param("i",$resident_id);
        $ok2=$up->execute(); $up->close();

        if($ok1 && $ok2){
          $status="Pending";
          $latest_proof_url=$rel;
          $latest_id_type=$valid_id_type;
          $flash = ($mode==='update')
            ? "<div class='alert alert-info mb-3'>🔄 Re-Verification submitted successfully. Status set to <b>Pending</b>.</div>"
            : "<div class='alert alert-success mb-3'>✅ Uploaded successfully. Verification pending.</div>";
        } else {
          $flash = "<div class='alert alert-danger mb-3'>❌ Database error while saving verification.</div>";
        }
      }
    }
  }
}

/* ================================
   RECENT HISTORY
================================ */
$hist=$conn->prepare("SELECT id_type,status,submitted_at FROM resident_verifications WHERE resident_id=? ORDER BY submitted_at DESC LIMIT 3");
$hist->bind_param("i",$resident_id);
$hist->execute();
$history=$hist->get_result()->fetch_all(MYSQLI_ASSOC);
$hist->close();

/* ================================
   TRACKER PERCENTAGE
================================ */
$tracker_stage = ($status==='Verified') ? 3 : (($status==='Pending') ? 2 : 1);
$fillPercent = ($tracker_stage === 2) ? 50 : (($tracker_stage === 3) ? 100 : 0);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify Account · Servigo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --brand:#1e40af;
  --accent:#16a34a;
  --muted:#6b7280;
  --border:#e5e7eb;
}
body{ background:#f5f7fa; font-family:'Outfit',sans-serif; }
.card{ border:none; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.05); }
.hero-icon{
  width:60px;height:60px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:1.8rem;
  background:linear-gradient(135deg,var(--brand),var(--accent));
}
.status-pill{ padding:.4rem .9rem; border-radius:999px; font-weight:600; font-size:.9rem; }
.status-pill.unverified{ background:#fee2e2; color:#b91c1c; }
.status-pill.pending{ background:#dbeafe; color:#1e3a8a; }
.status-pill.verified{ background:#dcfce7; color:#15803d; }
.btn-brand{ background:linear-gradient(135deg,var(--brand),var(--accent)); color:#fff; border:none; }
.btn-brand:hover{ opacity:.95; }
footer{ color:#6b7280; font-size:.85rem; text-align:center; margin-top:40px; }
.object-fit-cover{object-fit:cover;}
</style>
</head>
<body>

<div class="container py-4">
  <?= $flash ?>

  <!-- Resident Info -->
  <div class="card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="hero-icon"><i class='bx bx-user'></i></div>
        <div>
          <h5 class="mb-1 text-primary fw-semibold"><?= htmlspecialchars($resident_name) ?></h5>
          <div class="text-muted small"><?= htmlspecialchars($resident_barangay) ?></div>
          <div class="text-muted small"><?= htmlspecialchars($resident_email) ?></div>
        </div>
      </div>
      <span class="status-pill <?= strtolower($status) ?>"><?= htmlspecialchars($status) ?></span>
    </div>
  </div>

  <div class="row g-4">
    <!-- LEFT COLUMN -->
    <div class="col-lg-8">

      <!-- Progress -->
      <div class="card p-4 mb-4">
        <h6 class="text-primary fw-semibold mb-3"><i class='bx bx-trending-up'></i> Verification Progress</h6>
        <div class="progress" style="height: 10px; border-radius: 6px;">
          <div class="progress-bar"
               role="progressbar"
               style="width: <?= $fillPercent ?>%;
                      background: linear-gradient(90deg, var(--brand), var(--accent));
                      transition: width 0.8s ease;"
               aria-valuenow="<?= $fillPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mt-2 text-muted small">
          <span>Unverified</span><span>Pending</span><span>Verified</span>
        </div>
      </div>

      <!-- Main Section -->
      <div class="card p-4">
        <?php if ($status === 'Unverified'): ?>
          <h5 class="text-primary mb-1"><i class='bx bx-id-card'></i> Verify Your Account</h5>
          <p class="text-muted mb-3">Upload a valid ID for verification by your Barangay Admin within 1–3 days.</p>
          <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Valid ID Type</label>
              <select name="validID" class="form-select" required>
                <option value="">-- Select ID --</option>
                <?php $ids=["National ID","Passport","Driver’s License","Voter’s ID"];
                  foreach($ids as $idOpt){
                    $sel = ($latest_id_type===$idOpt) ? "selected" : "";
                    echo "<option value='$idOpt' $sel>$idOpt</option>";
                  } ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($resident_email) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Full Name</label>
              <input name="fullName" class="form-control" value="<?= htmlspecialchars($resident_name) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Barangay</label>
              <input name="barangay" class="form-control" value="<?= htmlspecialchars($resident_barangay) ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Upload Valid ID (Image/PDF)</label>
              <input type="file" name="uploadID" class="form-control" accept="image/*,application/pdf" required>
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-brand px-4">Submit Verification</button>
            </div>
          </form>

        <?php elseif ($status === 'Pending'): ?>
          <h5 class="text-primary mb-1"><i class='bx bx-time'></i> Verification in Progress</h5>
          <p class="text-muted">Your submitted ID is under review by the Barangay Admin.</p>
          <?php if ($latest_proof_url): ?>
            <div class="ratio ratio-16x9 border rounded overflow-hidden shadow-sm mt-3">
              <?php if (preg_match('/\.pdf$/i',$latest_proof_url)): ?>
                <a href="../<?= htmlspecialchars($latest_proof_url) ?>" target="_blank" class="d-flex align-items-center justify-content-center text-primary fw-semibold">View PDF</a>
              <?php else: ?>
                <img src="../<?= htmlspecialchars($latest_proof_url) ?>" class="w-100 h-100 object-fit-cover">
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if ($remarks_text): ?>
            <div class="alert alert-warning mt-3 py-2"><strong>Admin Remark:</strong> <?= htmlspecialchars($remarks_text) ?></div>
          <?php endif; ?>

        <?php else: ?>
          <h5 class="text-primary mb-1"><i class='bx bx-badge-check'></i> Verified Resident</h5>
          <div class="alert alert-success py-2 mb-3"><i class='bx bx-check-circle'></i> Your account is fully verified.</div>

          <div class="p-3 bg-light border rounded mb-3">
            <div><strong>ID Type:</strong> <?= htmlspecialchars($latest_id_type) ?></div>
            <div><strong>Barangay:</strong> <?= htmlspecialchars($resident_barangay) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($resident_email) ?></div>
          </div>

          <button class="btn btn-brand w-100 mt-2" data-bs-toggle="modal" data-bs-target="#reverifyModal">
            <i class='bx bx-refresh'></i> Update or Re-Verify ID
          </button>

          <!-- Modal -->
          <div class="modal fade" id="reverifyModal" tabindex="-1" aria-labelledby="reverifyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header border-0 pb-0">
                  <h5 class="modal-title text-primary fw-semibold" id="reverifyModalLabel">
                    <i class='bx bx-refresh'></i> Re-Verify ID
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                  <p class="text-muted mb-3">Upload a new valid ID to reverify your account. This will reset your status to <strong>Pending</strong>.</p>
                  <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="mode" value="update">
                    <div class="col-12">
                      <label class="form-label fw-semibold">Valid ID Type</label>
                      <select name="validID" class="form-select" required>
                        <option value="">-- Select ID --</option>
                        <?php $ids=["National ID","Passport","Driver’s License","Voter’s ID"];
                          foreach($ids as $idOpt){ $sel = ($latest_id_type===$idOpt) ? "selected" : ""; echo "<option value='$idOpt' $sel>$idOpt</option>"; } ?>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-semibold">Upload New ID (Image/PDF)</label>
                      <input type="file" name="uploadID" class="form-control" accept="image/*,application/pdf" required>
                    </div>
                    <div class="modal-footer border-0 mt-2">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button class="btn btn-brand">Submit Updated ID</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if($history): ?>
          <div class="mt-4">
            <h6 class="fw-semibold text-primary mb-2">Recent Submissions</h6>
            <ul class="list-group list-group-flush">
              <?php foreach($history as $h): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span><i class='bx bx-id-card'></i> <?= htmlspecialchars($h['id_type']) ?></span>
                  <span class="text-muted small"><?= htmlspecialchars($h['status']) ?> • <?= date("M j, Y",strtotime($h['submitted_at'])) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">
      <div class="card p-4">
        <h6 class="text-primary fw-semibold mb-3"><i class='bx bx-info-circle'></i> Verification Summary</h6>
        <div class="mb-2"><strong>Status:</strong> <?= htmlspecialchars($status) ?></div>
        <div class="mb-2"><strong>Resident:</strong> <?= htmlspecialchars($resident_name) ?></div>
        <div class="mb-2"><strong>Barangay:</strong> <?= htmlspecialchars($resident_barangay) ?></div>
        <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($resident_email) ?></div>
        <div class="mb-2"><strong>ID Type:</strong> <?= htmlspecialchars($latest_id_type ?: '—') ?></div>
        <?php if ($latest_proof_url): ?>
          <div class="ratio ratio-16x9 border rounded overflow-hidden shadow-sm mt-3">
            <?php if (preg_match('/\.pdf$/i',$latest_proof_url)): ?>
              <a href="../<?= htmlspecialchars($latest_proof_url) ?>" target="_blank" class="d-flex align-items-center justify-content-center text-primary fw-semibold">View PDF</a>
            <?php else: ?>
              <img src="../<?= htmlspecialchars($latest_proof_url) ?>" class="w-100 h-100 object-fit-cover" alt="ID Preview">
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="text-muted text-center small py-4">No ID file uploaded yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <footer>© 2025 Servigo | Barangay Verification Portal</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body> 
</html>