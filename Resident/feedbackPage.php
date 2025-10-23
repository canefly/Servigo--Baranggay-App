<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
require_once(__DIR__ . "/../Database/verification-checker.php");
include 'Components/topbar.php';

requireVerifiedResident($conn);

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';
$message     = '';

/* ================================
   FORM SUBMISSION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'])) {
  $subject  = trim($_POST['subject']);
  $category = trim($_POST['category']);
  $body     = trim($_POST['message']);
  if ($subject && $category && $body) {
    $stmt = $conn->prepare("INSERT INTO barangay_feedback (resident_id, barangay_name, subject, message, category, status, created_at)
                            VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
    $stmt->bind_param("issss", $resident_id, $barangay, $subject, $body, $category);
    $stmt->execute();
    $message = "<div class='toast success'>✅ Feedback submitted successfully!</div>";
    $stmt->close();
  } else {
    $message = "<div class='toast error'>❌ Please complete all fields.</div>";
  }
}

/* ================================
   FETCH HISTORY
================================ */
$stmt = $conn->prepare("SELECT * FROM barangay_feedback WHERE resident_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Concerns & Suggestions · Servigo</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root {
  --brand:#1e40af;--accent:#16a34a;--muted:#6b7280;
  --border:#e5e7eb;--radius:14px;--bg:#f9fafb;
}
body{background:var(--bg);color:#1e1e1e;font-family:"Parkinsans","Outfit",sans-serif;margin:0;padding:0;}
.container-custom{max-width:1400px;margin:auto;padding:40px 6vw 80px;}

/* Hero */
.hero h1{color:var(--brand);font-family:"Outfit";font-size:2.2rem;font-weight:700;margin-bottom:6px;}
.hero p{color:var(--muted);font-size:1rem;max-width:600px;line-height:1.5;}
/* Form Card */
.feedback-card{
  background:#fff;border:1px solid var(--border);border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);padding:2rem 1.8rem;max-width:800px;margin:auto;margin-top:2rem;
}
.feedback-card label{display:block;font-weight:600;margin-top:16px;color:#111;}
.feedback-card input,.feedback-card select,.feedback-card textarea{
  width:100%;border:1px solid var(--border);border-radius:10px;padding:10px 12px;margin-top:6px;
  font-size:1rem;font-family:inherit;transition:border-color .2s;
}
.feedback-card input:focus,.feedback-card select:focus,.feedback-card textarea:focus{
  border-color:var(--brand);outline:none;
}
.feedback-card textarea{resize:vertical;min-height:120px;}
.feedback-card button{
  background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;border:none;padding:12px 18px;
  border-radius:10px;font-weight:600;cursor:pointer;margin-top:24px;width:100%;transition:opacity .2s;
}
.feedback-card button:hover{opacity:.9;}

/* Feedback History */
.section-title{
  margin-top:3rem;font-size:1.2rem;color:var(--brand);font-weight:700;display:flex;align-items:center;gap:8px;
}
.history-grid{display:grid;gap:18px;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));margin-top:1.2rem;}
.card{
  background:#fff;border:1px solid var(--border);border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);padding:18px;transition:.2s;
}
.card:hover{transform:translateY(-2px);}
.card .subject{font-weight:700;color:var(--brand);font-size:1.05rem;margin-bottom:4px;}
.card .info{color:var(--muted);font-size:.9rem;margin-bottom:8px;}
.card .message{color:#1e1e1e;font-size:.95rem;line-height:1.45;margin-bottom:10px;}
.status-badge{display:inline-block;padding:4px 10px;border-radius:999px;color:#fff;font-size:.8rem;font-weight:600;}
.status-Pending{background:#f59e0b;}
.status-Resolved{background:#16a34a;}
.reply-box{background:#f9fafb;border:1px dashed var(--border);border-radius:10px;padding:10px 12px;margin-top:10px;}
.reply-box p{margin:0;color:#374151;font-size:.9rem;}
.reply-title{color:var(--accent);font-weight:600;margin-bottom:4px;}
.no-feedback{text-align:center;color:var(--muted);margin-top:2rem;font-size:1rem;}

/* Toast */
.toast{
  position:fixed;bottom:25px;right:25px;padding:14px 18px;border-radius:10px;color:#fff;font-weight:600;
  box-shadow:0 4px 12px rgba(0,0,0,.15);animation:fadeIn .4s ease;z-index:9999;
}
.toast.success{background:#16a34a;}
.toast.error{background:#dc2626;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
@media(max-width:768px){
  .container-custom{padding:30px 5vw 60px;}
  .feedback-card{padding:1.5rem;}
  .hero h1{font-size:1.8rem;}
}
</style>
</head>
<body>
<div class="container-custom">
  <section class="hero">
    <h1>Concerns & Suggestions</h1>
    <p>Submit feedback or suggestions to your barangay officials and track their responses below.</p>
    <?php if($message) echo $message; ?>
  </section>

  <!-- Submit Feedback Form -->
  <form method="POST" class="feedback-card">
    <label>Subject</label>
    <input type="text" name="subject" placeholder="e.g. Broken streetlight near Zone 2" required>

    <label>Category</label>
    <select name="category" required>
      <option value="">Select Type</option>
      <option value="Concern">Concern</option>
      <option value="Suggestion">Suggestion</option>
    </select>

    <label>Message</label>
    <textarea name="message" placeholder="Describe your concern or suggestion..." required></textarea>

    <button type="submit"><i class='bx bx-send'></i> Submit Feedback</button>
  </form>

  <!-- History Section -->
  <div class="section-title"><i class='bx bx-history'></i> My Submitted Feedback</div>
  <?php if(empty($history)): ?>
    <div class="no-feedback"><i class='bx bx-comment-x' style="font-size:2rem;"></i><br>No feedback submitted yet.</div>
  <?php else: ?>
    <div class="history-grid">
      <?php foreach($history as $f): ?>
        <div class="card">
          <div class="subject"><?=htmlspecialchars($f['subject'])?></div>
          <div class="info"><?=htmlspecialchars($f['category'])?> • <?=date("M d, Y", strtotime($f['created_at']))?></div>
          <div class="message"><?=htmlspecialchars($f['message'])?></div>
          <span class="status-badge status-<?=$f['status']?>"><?=$f['status']?></span>

          <?php if(!empty($f['admin_reply'])): ?>
          <div class="reply-box">
            <div class="reply-title">Barangay Reply:</div>
            <p><?=nl2br(htmlspecialchars($f['admin_reply']))?></p>
          </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<footer style="background:#fff;color:var(--muted);font-size:0.9rem;padding:20px;text-align:center;border-top:1px solid var(--border);">
  © 2025 Servigo. All rights reserved.
</footer>

<script>
document.addEventListener("DOMContentLoaded",()=>{
  const toast=document.querySelector(".toast");
  if(toast){setTimeout(()=>toast.remove(),4000);}
});
</script>
</body>
</html>
