<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Resident Feedback</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --bg:#f5f7fa; --card:#fff; --text:#222; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
  --shadow:0 2px 8px rgba(0,0,0,.08); --radius:16px; --pad:14px;
}

body {
  margin:0;
  font-family:system-ui, sans-serif;
  background:var(--bg);
  color:var(--text);
}

.container {
  max-width:900px;
  margin:0 auto;
  padding:16px;
}

/* Nav Tabs */
.navtabs {
  display:flex;
  gap:8px;
  justify-content:center;
  background:#f9fafb;
  padding:10px;
  border-bottom:1px solid var(--border);
}
.tabbtn {
  all:unset;
  cursor:pointer;
  font-weight:600;
  padding:8px 14px;
  border-radius:10px;
  border:1px solid var(--border);
  background:#f3f4f6;
  color:var(--text);
}
.tabbtn.active {
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;
  font-weight:700;
}

/* Card Layout */
.card {
  background:var(--card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:var(--pad) calc(var(--pad) * 1.3);
  box-shadow:var(--shadow);
  margin-bottom:20px;
}

h2 { margin:0 0 8px 0; color:var(--brand); }
p.muted { margin:4px 0 16px 0; color:var(--muted); }

/* Input Fields */
label {
  font-size:14px;
  font-weight:600;
  display:block;
  margin-bottom:6px;
}

input.input,
select.input,
textarea {
  width:100%;
  padding:12px 14px;
  border-radius:10px;
  border:1px solid var(--border);
  font-size:15px;
  margin-bottom:14px;
  box-sizing:border-box;
  background:#fff;
  line-height:1.5;
  transition:border .2s, box-shadow .2s;
}

input.input:focus,
select.input:focus,
textarea:focus {
  border-color:var(--brand);
  box-shadow:0 0 0 2px rgba(30,64,175,.15);
  outline:none;
}

textarea {
  resize:none;
  min-height:100px;
}

/* Buttons */
button.primary {
  all:unset;
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;
  padding:10px 18px;
  border-radius:12px;
  cursor:pointer;
  font-weight:700;
  text-align:center;
  display:inline-block;
}
button.primary:hover {
  opacity:.9;
}

/* Feedback List */
.feedback {
  background:#fff;
  border:1px solid var(--border);
  border-radius:12px;
  padding:14px 16px;
  box-shadow:var(--shadow);
  margin-bottom:14px;
}
.feedback .meta {
  font-size:13px;
  color:var(--muted);
  margin-bottom:4px;
}
.feedback h3 {
  margin:0 0 6px 0;
  font-size:16px;
  color:#111;
}
.feedback p {
  margin:0;
  color:var(--text);
  font-size:14px;
  line-height:1.4;
}

/* Status Tags */
.status {
  display:inline-block;
  padding:2px 8px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
}
.status.Unread { background:#e0f2fe; color:#0369a1; }
.status.Reviewed { background:#dcfce7; color:#166534; }
.status.Resolved { background:#fef3c7; color:#92400e; }

/* Empty State + Footer */
.empty {
  padding:16px;
  text-align:center;
  color:var(--muted);
  border:1px dashed var(--border);
  border-radius:12px;
}
footer {
  color:var(--muted);
  text-align:center;
  padding:20px 12px;
  font-size:14px;
}

</style>
</head>
<body>

<?php include 'INCLUDES/topbar.php'; ?>

<!-- ✅ Navtabs -->
<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn">News</a>
  <a href="permitsPage.php" class="tabbtn">Permits</a>
  <a href="suggestion.php" class="tabbtn active">Feedback</a>
  <a href="storesPage.php" class="tabbtn">Stores</a>
</nav>

<main class="container" id="app">
  <section class="card">
    <h2>Barangay Suggestion Box</h2>
    <p class="muted">Submit your feedback, concern, or suggestion to help improve your barangay.</p>
    <form id="feedbackForm">
      <label>Category</label>
      <select id="category" class="input" required>
        <option>Suggestion</option>
        <option>Complaint</option>
        <option>Inquiry</option>
        <option>Praise</option>
      </select>

      <label>Subject</label>
      <input id="subject" class="input" placeholder="e.g., Flooding on Main Street" required>

      <label>Message</label>
      <textarea id="message" rows="4" placeholder="Describe your concern or idea..." required></textarea>

      <button class="primary" type="submit">Submit Feedback</button>
    </form>
  </section>

  <section>
    <h2>My Submitted Feedback</h2>
    <div id="feedbackList"></div>
    <div id="emptyState" class="empty" hidden>No feedback submitted yet.</div>
  </section>
</main>

<footer><small>© 2025 Servigo (Prototype)</small></footer>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const residentId = localStorage.getItem("sg_resident_id");
const barangay = localStorage.getItem("sg_brgy") || "BARANGAY";

const list = document.getElementById('feedbackList');
const emptyState = document.getElementById('emptyState');
const form = document.getElementById('feedbackForm');

form.addEventListener('submit', async e=>{
  e.preventDefault();
  const category=document.getElementById('category').value;
  const subject=document.getElementById('subject').value.trim();
  const message=document.getElementById('message').value.trim();
  if(!subject||!message) return alert("Please complete all fields.");

  const res=await fetch(`${SUPABASE_URL}rest/v1/resident_feedback`,{
    method:'POST',
    headers:{
      apikey:SUPABASE_KEY,
      Authorization:`Bearer ${SUPABASE_KEY}`,
      'Content-Type':'application/json'
    },
    body:JSON.stringify({
      resident_id:residentId,
      barangay_name:barangay,
      subject,
      message,
      category
    })
  });

  if(res.ok){
    alert("✅ Feedback submitted successfully!");
    form.reset();
    loadFeedback();
  }else{
    alert("❌ Error sending feedback.");
  }
});

async function loadFeedback(){
  const res=await fetch(`${SUPABASE_URL}rest/v1/resident_feedback?resident_id=eq.${residentId}&order=created_at.desc`,{
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const data=await res.json();
  render(data);
}

function render(items){
  list.innerHTML='';
  if(!items.length){emptyState.hidden=false;return;} emptyState.hidden=true;
  items.forEach(f=>{
    const el=document.createElement('div');
    el.className='feedback';
    el.innerHTML=`
      <div class="meta">${new Date(f.created_at).toLocaleDateString()} • <span class="status ${f.status}">${f.status}</span></div>
      <h3>${f.subject}</h3>
      <p>${f.message}</p>
      ${f.admin_response?`<p style="margin-top:10px;color:var(--brand);font-weight:600">Response: ${f.admin_response}</p>`:''}
    `;
    list.appendChild(el);
  });
}

loadFeedback();
</script>
</body>
</html>
