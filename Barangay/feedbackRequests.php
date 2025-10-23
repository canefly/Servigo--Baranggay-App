<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("admin");
require_once(__DIR__ . "/../Database/connection.php");

$barangay = $_SESSION['sg_brgy'] ?? '';

/* --- Handle Reply Submission + Notification (must be BEFORE any HTML output) --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'], $_POST['reply_text'])) {
  header('Content-Type: application/json; charset=utf-8');

  $id   = intval($_POST['reply_id']);
  $text = trim($_POST['reply_text']);

  // Update feedback
  $stmt = $conn->prepare("UPDATE barangay_feedback SET admin_reply=?, status='Resolved' WHERE id=?");
  if (!$stmt) { echo json_encode(['ok'=>false,'error'=>$conn->error]); exit; }
  $stmt->bind_param("si", $text, $id);
  $ok1 = $stmt->execute();
  $stmt->close();

  // Fetch resident info for notification
  $resident_id = null; $subject = ''; $barangay_name = $barangay;
  $getRes = $conn->prepare("SELECT resident_id, subject, barangay_name FROM barangay_feedback WHERE id=? LIMIT 1");
  if ($getRes) {
    $getRes->bind_param("i", $id);
    $getRes->execute();
    $resData = $getRes->get_result()->fetch_assoc();
    $getRes->close();
    if ($resData) {
      $resident_id   = intval($resData['resident_id']);
      $subject       = $resData['subject'] ?? 'Feedback';
      $barangay_name = $resData['barangay_name'] ?: $barangay;
    }
  }

  // Insert notification (best-effort)
  $ok2 = true;
  if ($resident_id) {
    $notif = $conn->prepare("INSERT INTO notifications 
      (barangay_name, recipient_type, recipient_id, type, title, message, link, created_at)
      VALUES (?, 'resident', ?, 'feedback_reply', ?, ?, ?, NOW())");
    if ($notif) {
      $title   = "Barangay replied to your feedback";
      $message = "Your feedback titled '{$subject}' has been replied to by the barangay.";
      $link    = "/Resident/feedbackPage.php";
      $notif->bind_param("sisss", $barangay_name, $resident_id, $title, $message, $link);
      $ok2 = $notif->execute();
      $notif->close();
    } else {
      $ok2 = false;
    }
  }

  echo json_encode(['ok' => ($ok1 && $ok2)]);
  exit;
}

/* --- From here on, it's safe to output HTML --- */
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

/* --- Fetch Feedback --- */
$stmt = $conn->prepare("
  SELECT f.*, r.first_name, r.last_name 
  FROM barangay_feedback f
  LEFT JOIN residents r ON f.resident_id = r.id
  WHERE f.barangay_name = ?
  ORDER BY f.created_at DESC
");
$stmt->bind_param("s", $barangay);
$stmt->execute();
$feedbacks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Feedback Management · Servigo</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root { --bg:#f9fafb; --card:#fff; --text:#1e293b; --muted:#64748b; --border:#e2e8f0; --brand:#065f46; --accent:#10b981; --radius:14px; --shadow:0 3px 10px rgba(0,0,0,.06); }
body{font-family:"Inter",sans-serif;background:var(--bg);color:var(--text);margin:0;}
.main-content{padding:28px;}
.feedback-header{display:flex;align-items:center;justify-content:space-between;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 24px;box-shadow:var(--shadow);margin-bottom:24px;}
.feedback-header h2{margin:0;font-weight:700;color:var(--brand);display:flex;align-items:center;gap:10px;}
.feedback-header h2 i{font-size:1.7rem;color:var(--accent);}
.filter-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:22px;}
.filter-tab{all:unset;cursor:pointer;padding:9px 18px;border-radius:999px;font-weight:600;font-size:.9rem;color:var(--brand);background:#f1f5f9;border:1px solid var(--border);transition:.2s;}
.filter-tab.active{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;border:none;box-shadow:0 2px 8px rgba(16,185,129,.3);}
.feedback-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:20px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;display:flex;flex-direction:column;justify-content:space-between;transition:.25s ease;position:relative;}
.card:hover{transform:translateY(-3px);box-shadow:0 6px 14px rgba(0,0,0,.07);}
.category{position:absolute;top:18px;right:18px;background:#ecfdf5;color:var(--brand);font-size:.75rem;padding:4px 10px;border-radius:999px;font-weight:600;}
.card h4{margin:0;color:var(--brand);font-weight:600;font-size:1.05rem;margin-bottom:8px;}
.meta{font-size:.9rem;color:var(--muted);margin-bottom:10px;}
.message{color:var(--text);font-size:.95rem;line-height:1.5;margin-bottom:12px;max-height:80px;overflow:hidden;text-overflow:ellipsis;}
.badge{display:inline-flex;align-items:center;justify-content:center;width:auto;padding:4px 12px;border-radius:20px;color:#fff;font-weight:600;font-size:.82rem;}
.badge.Pending{background:#f59e0b;}
.badge.Resolved{background:#10b981;}
.reply-box{background:#f9fafb;border-left:4px solid var(--accent);padding:10px 12px;border-radius:8px;font-size:.9rem;margin-top:10px;}
.reply-box strong{color:var(--accent);}
.actions{display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-top:12px;}
.btn{all:unset;cursor:pointer;padding:9px 16px;border-radius:8px;font-weight:600;font-size:.9rem;text-align:center;transition:all .2s;}
.btn-view{background:#f1f5f9;border:1px solid var(--border);color:#111;}
.btn-primary{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;border:none;box-shadow:0 2px 6px rgba(0,0,0,.15);}
.btn:hover{opacity:.9;}
.no-feedback{text-align:center;color:var(--muted);margin-top:40px;font-size:.95rem;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:24px;width:95%;max-width:520px;box-shadow:0 8px 24px rgba(0,0,0,.15);animation:fadeIn .25s ease;}
.modal h3{margin-top:0;color:var(--brand);font-weight:700;text-align:center;}
.modal p{font-size:.95rem;line-height:1.5;}
.modal textarea{width:100%;height:120px;border-radius:10px;border:1px solid var(--border);padding:10px;font-size:.95rem;resize:none;margin-top:8px;}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:14px;}
.btn-cancel{background:#f1f5f9;color:#111;border:1px solid var(--border);}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
@media(max-width:768px){.feedback-list{grid-template-columns:1fr;}}
</style>
</head>
<body>
<main class="main-content">
  <div class="feedback-header">
    <h2><i class='bx bx-message-dots'></i>Resident Feedback Management</h2>
  </div>

  <nav class="filter-tabs">
    <button class="filter-tab active" data-filter="all">All</button>
    <button class="filter-tab" data-filter="Concern">Concerns</button>
    <button class="filter-tab" data-filter="Suggestion">Suggestions</button>
    <button class="filter-tab" data-filter="Pending">Pending</button>
    <button class="filter-tab" data-filter="Resolved">Resolved</button>
  </nav>

  <section class="feedback-list" id="feedbackList">
    <?php if(empty($feedbacks)): ?>
      <div class="no-feedback"><i class='bx bx-comment-x' style="font-size:2rem;"></i><br>No feedback found.</div>
    <?php else: foreach($feedbacks as $f): ?>
      <div class="card" data-type="<?=htmlspecialchars($f['category'])?>" data-status="<?=htmlspecialchars($f['status'])?>">
        <span class="category"><?=htmlspecialchars($f['category'])?></span>
        <h4><?=htmlspecialchars($f['subject'])?></h4>
        <div class="meta">From <?=htmlspecialchars($f['first_name'].' '.$f['last_name'])?> · <?=date("M d, Y",strtotime($f['created_at']))?></div>
        <div class="message"><?=htmlspecialchars($f['message'])?></div>
        <span class="badge <?=$f['status']?>"><?=$f['status']?></span>

        <?php if(!empty($f['admin_reply'])): ?>
          <div class="reply-box"><strong>Barangay Reply:</strong><br><?=nl2br(htmlspecialchars($f['admin_reply']))?></div>
        <?php endif; ?>

        <div class="actions">
          <button class="btn btn-view" 
            onclick="openView('<?=addslashes($f['subject'])?>','<?=addslashes($f['message'])?>','<?=addslashes($f['first_name'].' '.$f['last_name'])?>','<?=$f['category']?>','<?=$f['created_at']?>','<?=$f['status']?>','<?=addslashes($f['admin_reply']??'')?>')">
            <i class='bx bx-show'></i> View
          </button>
          <?php if($f['status']==='Pending'): ?>
            <button class="btn btn-primary"
              onclick="openReply(<?=$f['id']?>,'<?=addslashes($f['subject'])?>','<?=addslashes($f['category'])?>','<?=addslashes($f['message'])?>','<?=addslashes($f['first_name'].' '.$f['last_name'])?>')">
              <i class='bx bx-reply'></i> Reply
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </section>
</main>

<!-- VIEW MODAL -->
<div id="viewModal" class="modal-bg">
  <div class="modal">
    <h3>Feedback Details</h3>
    <div id="viewBody"></div>
    <div class="modal-actions"><button class="btn btn-cancel" onclick="closeView()">Close</button></div>
  </div>
</div>

<!-- REPLY MODAL -->
<div id="replyModal" class="modal-bg">
  <form class="modal" onsubmit="submitReply(event)">
    <h3>Reply to Resident</h3>
    <div id="replyContext" style="background:#f9fafb;border:1px solid var(--border);border-radius:10px;padding:10px;margin-bottom:12px;font-size:.9rem;color:var(--text);line-height:1.5;"></div>
    <input type="hidden" id="reply_id">
    <textarea id="reply_text" placeholder="Write your reply here..." required></textarea>
    <div class="modal-actions">
      <button type="button" class="btn btn-cancel" onclick="closeReply()">Cancel</button>
      <button type="submit" class="btn btn-primary">Send Reply</button>
    </div>
  </form>
</div>

<script>
document.querySelectorAll('.filter-tab').forEach(tab=>{
  tab.onclick=()=>{
    document.querySelectorAll('.filter-tab').forEach(t=>t.classList.remove('active'));
    tab.classList.add('active');
    const f=tab.dataset.filter;
    document.querySelectorAll('.card').forEach(c=>{
      const show=(f==='all'||c.dataset.type===f||c.dataset.status===f);
      c.style.display=show?'block':'none';
    });
  };
});

function openView(subject,message,resident,category,date,status,reply){
  const body=document.getElementById('viewBody');
  body.innerHTML=`
    <p><strong>Subject:</strong> ${subject}</p>
    <p><strong>Category:</strong> ${category}</p>
    <p><strong>From:</strong> ${resident}</p>
    <p><strong>Date:</strong> ${new Date(date).toLocaleDateString()}</p>
    <p><strong>Status:</strong> ${status}</p>
    <hr><p>${message}</p>
    ${reply?`<hr><p><strong>Barangay Reply:</strong><br>${reply}</p>`:''}
  `;
  document.getElementById('viewModal').classList.add('active');
}
function closeView(){document.getElementById('viewModal').classList.remove('active');}

function openReply(id,subject,category,message,resident){
  document.getElementById('reply_id').value=id;
  document.getElementById('replyContext').innerHTML=`
    <p><strong>Subject:</strong> ${subject}</p>
    <p><strong>Category:</strong> ${category}</p>
    <p><strong>Message:</strong> ${message}</p>
  `;
  document.getElementById('reply_text').value='';
  document.getElementById('replyModal').classList.add('active');
  setTimeout(()=>document.getElementById('reply_text').focus(),150);
}
function closeReply(){document.getElementById('replyModal').classList.remove('active');}

function submitReply(e){
  e.preventDefault();
  const id=document.getElementById('reply_id').value;
  const text=document.getElementById('reply_text').value.trim();
  if(!text){ alert('Please write a reply.'); return; }

  const f=new FormData();
  f.append('reply_id',id);
  f.append('reply_text',text);

  // Post to current URL (explicit) and expect JSON
  fetch(window.location.href, { method:'POST', body:f, credentials:'same-origin' })
    .then(r => r.json())
    .then(j => {
      if(j && j.ok){
        alert('Reply sent successfully!');
        location.reload();
      }else{
        alert('Failed to send reply. Please try again.');
      }
    })
    .catch(() => alert('Network error. Please try again.'));
}
</script>
</body>
</html>
