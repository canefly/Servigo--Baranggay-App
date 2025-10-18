<?php include 'INCLUDES/barangaySidebar.php'; ?> 
<?php include 'INCLUDES/barangayTopbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Admin Concerns & Suggestions · Servigo</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
<style>
:root {
  --bg:#f5f7fa; --card:#ffffff;
  --text:#222; --muted:#6b7280; --border:#e5e7eb;
  --brand:#047857; --accent:#16a34a;
  --radius:14px; --shadow:0 2px 8px rgba(0,0,0,.08);
  --sidebar-width:240px;
}

/* Global */
*{box-sizing:border-box;}
body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);}
.layout{display:flex;min-height:100vh;}
.main-content{flex:1;padding:16px;transition:margin-left .3s ease;width:100%;}
@media(min-width:1024px){.main-content{margin-left:var(--sidebar-width);}}

/* Header */
.dashboard-header{
  display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px;
  background:var(--card);border:1px solid var(--border);padding:14px 20px;
  border-radius:var(--radius);box-shadow:var(--shadow);flex-wrap:wrap;
}
.dashboard-header-left{display:flex;align-items:center;gap:14px;}
.dashboard-header img{height:48px;width:48px;border-radius:10px;object-fit:cover;}
.dashboard-title{font-size:1.4rem;font-weight:700;color:var(--brand);}

/* Tabs */
.filter-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
.filter-tab{
  all:unset;cursor:pointer;padding:8px 18px;border-radius:999px;font-weight:600;font-size:.95rem;
  border:1px solid var(--border);background:#f3f4f6;color:var(--brand);transition:.2s;
}
.filter-tab.active{
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;border:none;box-shadow:0 2px 6px rgba(0,0,0,.1);
}

/* Cards */
.feedback-list{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;
}
.feedback-card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:16px;
  display:flex;
  flex-direction:column;
  gap:8px;
  transition:.2s;
}
.feedback-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);}
.feedback-header{display:flex;justify-content:space-between;align-items:center;gap:8px;}
.feedback-title{font-weight:700;font-size:1.05rem;}
.status{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;font-weight:600;}
.status.Unread{background:#e0f2fe;color:#0369a1;}
.status.Reviewed{background:#dcfce7;color:#166534;}
.status.Resolved{background:#fef3c7;color:#92400e;}
.meta{font-size:.85rem;color:var(--muted);}
.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.btn{
  all:unset;cursor:pointer;padding:8px 14px;border-radius:10px;font-weight:600;
  font-size:.9rem;display:flex;align-items:center;gap:6px;
}
.btn.view{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;}
.btn.resolve{background:var(--accent);color:#fff;}
.empty{padding:16px;text-align:center;color:var(--muted);border:1px dashed var(--border);border-radius:12px;margin-top:20px;}

/* Modal */
.modal {
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
  display:flex;
  align-items:center;
  justify-content:center;
  z-index:100;
  opacity:0;
  pointer-events:none;
  transition:opacity .25s ease;
}
.modal.show {
  opacity:1;
  pointer-events:all;
}
.modal-content {
  background:#fff;
  border-radius:14px;
  padding:20px;
  max-width:500px;
  width:90%;
  box-shadow:var(--shadow);
  animation:pop .25s ease;
}
@keyframes pop { from{transform:scale(.95);opacity:0;} to{transform:scale(1);opacity:1;} }
.modal h3{margin-top:0;color:var(--brand);}
.modal textarea{width:100%;padding:10px;border-radius:10px;border:1px solid var(--border);resize:vertical;font-size:14px;}
.modal .actions{margin-top:10px;display:flex;justify-content:flex-end;gap:10px;}
.modal button{all:unset;cursor:pointer;padding:8px 14px;border-radius:10px;font-weight:600;}
.modal button.send{background:var(--accent);color:#fff;}
.modal button.close{background:#f3f4f6;color:var(--text);}
.no-feedback{text-align:center;color:var(--muted);margin-top:30px;}
</style>
</head>
<body>
<div class="layout">
  <main class="main-content">
    <div class="dashboard-container">
      <div class="dashboard-header">
        <div class="dashboard-header-left">
          <img src="B.png" alt="Barangay Logo">
          <div class="dashboard-title">Resident Concerns & Suggestions</div>
        </div>
      </div>

      <nav class="filter-tabs">
        <button class="filter-tab active" data-cat="All">All</button>
        <button class="filter-tab" data-cat="Suggestion">Suggestions</button>
        <button class="filter-tab" data-cat="Complaint">Complaints</button>
        <button class="filter-tab" data-cat="Inquiry">Inquiries</button>
        <button class="filter-tab" data-cat="Praise">Praises</button>
      </nav>

      <section id="feedbackList" class="feedback-list"></section>
      <div id="emptyState" class="no-feedback" hidden>
        <i class='bx bx-message-square-x' style="font-size:2rem;"></i><br>
        No concerns found.
      </div>
    </div>
  </main>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="modal">
  <div class="modal-content">
    <h3 id="modalSubject">Respond to Concern</h3>
    <p id="modalMessage" class="muted"></p>
    <textarea id="adminResponse" rows="4" placeholder="Type your response..."></textarea>
    <div class="actions">
      <button class="close" id="closeModal">Cancel</button>
      <button class="send" id="sendReply">Send Response</button>
    </div>
  </div>
</div>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const barangay = localStorage.getItem("sg_brgy") || "BARANGAY";
let allConcerns = [], activeCategory="All", currentId=null;

const list=document.getElementById("feedbackList");
const emptyState=document.getElementById("emptyState");
const modal=document.getElementById("replyModal");
const modalSubject=document.getElementById("modalSubject");
const modalMessage=document.getElementById("modalMessage");
const adminResponse=document.getElementById("adminResponse");
const closeModal=document.getElementById("closeModal");

async function loadConcerns(){
  const res = await fetch(`${SUPABASE_URL}rest/v1/resident_feedback?barangay_name=eq.${barangay}&order=created_at.desc`, {
    headers: { apikey: SUPABASE_KEY, Authorization: "Bearer " + SUPABASE_KEY }
  });
  allConcerns = await res.json();
  render();
}

function render(){
  list.innerHTML="";
  const filtered = activeCategory==="All" ? allConcerns : allConcerns.filter(f=>f.category===activeCategory);
  if(!filtered.length){emptyState.hidden=false;return;} emptyState.hidden=true;

  filtered.forEach(f=>{
    const card=document.createElement("div");
    card.className="feedback-card";
    card.innerHTML=`
      <div class="feedback-header">
        <span class="feedback-title">${f.subject}</span>
        <span class="status ${f.status}">${f.status}</span>
      </div>
      <div class="meta">${f.category} • ${new Date(f.created_at).toLocaleDateString()}</div>
      <p>${f.message}</p>
      <div class="actions">
        <button class="btn view" onclick="openModal(${f.id}, '${f.subject.replace(/'/g,"&#39;")}', '${f.message.replace(/'/g,"&#39;")}')"><i class='bx bx-message-dots'></i> Reply</button>
        <button class="btn resolve" onclick="markResolved(${f.id})"><i class='bx bx-check'></i> Mark Resolved</button>
      </div>
    `;
    list.appendChild(card);
  });
}

function openModal(id, subject, msg){
  currentId=id;
  modalSubject.textContent=subject;
  modalMessage.textContent=msg;
  adminResponse.value='';
  modal.classList.add("show");
}

closeModal.addEventListener("click", () => {
  modal.classList.remove("show");
});

window.addEventListener("click", (e) => {
  if (e.target === modal) modal.classList.remove("show");
});

document.getElementById("sendReply").onclick=async()=>{
  const response=adminResponse.value.trim();
  if(!response)return alert("Please type your response first.");
  const res=await fetch(`${SUPABASE_URL}rest/v1/resident_feedback?id=eq.${currentId}`,{
    method:"PATCH",
    headers:{
      apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY,
      "Content-Type":"application/json"
    },
    body:JSON.stringify({admin_response:response,status:"Resolved"})
  });
  if(res.ok){
    alert("✅ Response sent!");
    modal.classList.remove("show");
    loadConcerns();
  }else alert("❌ Failed to send response.");
};

async function markResolved(id){
  await fetch(`${SUPABASE_URL}rest/v1/resident_feedback?id=eq.${id}`,{
    method:"PATCH",
    headers:{
      apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY,
      "Content-Type":"application/json"
    },
    body:JSON.stringify({status:"Resolved"})
  });
  loadConcerns();
}

document.querySelectorAll(".filter-tab").forEach(btn=>{
  btn.onclick=()=>{
    document.querySelectorAll(".filter-tab").forEach(t=>t.classList.remove("active"));
    btn.classList.add("active");
    activeCategory=btn.dataset.cat;
    render();
  };
});

loadConcerns();
</script>
</body>
</html>
