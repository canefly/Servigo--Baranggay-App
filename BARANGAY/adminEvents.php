<?php include 'INCLUDES/barangaySidebar.php'; ?>
<?php include 'INCLUDES/barangayTopbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Barangay · Events Manager</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root {
  --bg:#f5f7fa;
  --card:#ffffff;
  --text:#222;
  --muted:#6b7280;
  --brand:#047857;
  --accent:#10b981;
  --error:#b91c1c;
  --ok:#166534;
  --shadow:0 2px 8px rgba(0,0,0,.08);
  --radius:14px;
  --gap:16px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{
  background:var(--bg);
  color:var(--text);
  font-family:system-ui,sans-serif;
}
.layout{display:flex;min-height:100vh;}
.main-content{flex:1;padding:var(--gap);transition:margin-left .3s ease;max-width:100%;}
@media(min-width:1024px){.main-content{margin-left:275px;}}

/* Card */
.card{
  background:var(--card);
  padding:var(--gap);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  margin-bottom:var(--gap);
  width:100%;
}
h2{margin-bottom:12px;font-size:1.25rem;color:var(--brand);}
label{font-weight:600;display:block;margin-top:12px;}
input,textarea,select{
  width:100%;
  padding:12px;
  font-size:15px;
  margin-top:6px;
  border:1px solid #e5e7eb;
  border-radius:10px;
}
textarea{resize:vertical;min-height:100px;}
.btn{
  width:100%;
  margin-top:16px;
  padding:12px;
  border-radius:10px;
  border:none;
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;
  font-weight:600;
  cursor:pointer;
  transition:.2s;
}
.btn:hover{opacity:.9;}
.error,.ok{
  margin-top:10px;
  padding:10px;
  border-radius:8px;
  font-size:.9rem;
}
.error{background:#fee2e2;color:var(--error);border:1px solid #ef4444;}
.ok{background:#dcfce7;color:var(--ok);border:1px solid #22c55e;}

/* Event feed */
.post{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:12px;
  padding:14px;
  margin-bottom:16px;
  box-shadow:0 2px 6px rgba(0,0,0,.05);
  display:flex;
  flex-direction:column;
  gap:10px;
  word-wrap:break-word;
}
.post .meta{font-size:13px;color:var(--muted);}
.post h3{margin:0;font-size:16px;color:#111;}
.post small{color:var(--muted);}
.edit-btn,.delete-btn{
  all:unset;
  cursor:pointer;
  font-size:14px;
  font-weight:600;
}
.edit-btn{color:var(--brand);}
.delete-btn{color:var(--error);}
.counter{font-size:13px;color:var(--muted);}
@media(max-width:600px){
  .card,.post{padding:12px;border-radius:10px;}
  h2{font-size:1.1rem;}
  .btn{font-size:14px;padding:10px;}
}

/* Modal */
.modal{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,.5);
  display:none;
  align-items:center;justify-content:center;
  z-index:1000;
}
.modal.active{display:flex;}
.modal-content{
  background:var(--card);
  border-radius:14px;
  box-shadow:var(--shadow);
  width:95%;max-width:500px;
  padding:20px;
}
.modal h3{color:var(--brand);margin-bottom:10px;}
.modal-close{
  position:absolute;top:20px;right:20px;
  background:none;border:none;
  font-size:24px;cursor:pointer;color:var(--muted);
}
</style>
</head>
<body>
<div class="layout">
  <main class="main-content">

    <!-- Create -->
    <section class="card">
      <h2>Create Event</h2>
      <form id="eventForm">
        <label>Title</label>
        <input type="text" name="title" required>
        <label>Description</label>
        <textarea name="description"></textarea>
        <label>Venue</label>
        <input type="text" name="venue">
        <label>Category</label>
        <select name="category" required>
          <option>General</option>
          <option>Health</option>
          <option>Clean-up Drive</option>
          <option>Sports</option>
          <option>Emergency</option>
        </select>
        <label>Visibility</label>
        <select name="visibility" required>
          <option value="public">Public</option>
          <option value="verified_only">Verified Residents Only</option>
        </select>
        <label>Start Date & Time</label>
        <input type="datetime-local" name="start_date" required>
        <label>End Date & Time</label>
        <input type="datetime-local" name="end_date">
        <button type="submit" class="btn">Create Event</button>
        <p id="msg"></p>
      </form>
    </section>

    <!-- Feed -->
    <section class="card">
      <h2>My Events</h2>
      <div id="posts"></div>
    </section>
  </main>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal()">&times;</button>
    <h3>Edit Event</h3>
    <form id="editForm">
      <input type="hidden" name="id">
      <label>Title</label>
      <input type="text" name="title" required>
      <label>Description</label>
      <textarea name="description"></textarea>
      <label>Venue</label>
      <input type="text" name="venue">
      <label>Category</label>
      <select name="category">
        <option>General</option>
        <option>Health</option>
        <option>Clean-up Drive</option>
        <option>Sports</option>
        <option>Emergency</option>
      </select>
      <label>Visibility</label>
      <select name="visibility">
        <option value="public">Public</option>
        <option value="verified_only">Verified Residents Only</option>
      </select>
      <label>Start Date</label>
      <input type="datetime-local" name="start_date">
      <label>End Date</label>
      <input type="datetime-local" name="end_date">
      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>
</div>

<script>
const SUPABASE_URL="https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const BARANGAY=localStorage.getItem("bg_name");

/* 🔔 helper: send notification (resident broadcast) */
async function sendNotification(data){
  try{
    await fetch(`${SUPABASE_URL}rest/v1/notifications`,{
      method:"POST",
      headers:{
        apikey:SUPABASE_KEY,
        Authorization:"Bearer "+SUPABASE_KEY,
        "Content-Type":"application/json"
      },
      body:JSON.stringify(data)
    });
    console.log("✅ Notification queued");
  }catch(err){
    console.error("⚠️ Notification failed:",err);
  }
}

/* CREATE EVENT */
document.getElementById("eventForm").addEventListener("submit",async e=>{
  e.preventDefault();
  const msg=document.getElementById("msg");
  msg.textContent="";
  msg.className="";
  const form=new FormData(e.target);
  const body=Object.fromEntries(form.entries());
  body.barangay_name=BARANGAY;

  const res=await fetch(`${SUPABASE_URL}rest/v1/barangay_events`,{
    method:"POST",
    headers:{
      apikey:SUPABASE_KEY,
      Authorization:"Bearer "+SUPABASE_KEY,
      "Content-Type":"application/json",
      /* 👇 so we can read the new event id */
      Prefer:"return=representation"
    },
    body:JSON.stringify(body)
  });

  if(res.ok){
    msg.className="ok";msg.textContent="✅ Event created!";
    e.target.reset();
    const created=await res.json();
    const eventId=created?.[0]?.id||null;

    /* 🔔 notify residents about new event (no layout changes) */
    sendNotification({
      barangay_name: BARANGAY,
      recipient_type: "resident",
      type: "new_event",
      title: "New Barangay Event Posted",
      message: `A new event "${body.title}" has been scheduled in your barangay.`,
      source_table: "barangay_events",
      source_id: eventId
    });

    loadEvents();
  }else{
    msg.className="error";msg.textContent="❌ Failed to create event.";
  }
});

/* LOAD EVENTS */
async function loadEvents(){
  const postsEl=document.getElementById("posts");
  postsEl.innerHTML="";
  const res=await fetch(`${SUPABASE_URL}rest/v1/barangay_events?barangay_name=eq.${BARANGAY}&order=start_date.desc`,{
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const data=await res.json();
  if(!data.length){postsEl.innerHTML="<p>No events yet.</p>";return;}
  for(const e of data){e.interest_count=await getInterestCount(e.id);}
  data.forEach(ev=>{
    const div=document.createElement("div");
    div.className="post";
    div.innerHTML=`
      <div class="meta"><strong>${ev.category}</strong> • ${new Date(ev.start_date).toLocaleDateString()}</div>
      <h3>${ev.title}</h3>
      <p>${ev.description||""}</p>
      <small>📍 ${ev.venue||"TBA"}</small>
      <small>⭐ ${ev.interest_count} interested</small>
      <div>
        <button class="edit-btn" onclick="openEdit(${ev.id})">✏ Edit</button> |
        <button class="delete-btn" onclick="deleteEvent(${ev.id})">🗑 Delete</button>
      </div>`;
    postsEl.appendChild(div);
  });
}
async function getInterestCount(eventId){
  const res=await fetch(`${SUPABASE_URL}rest/v1/event_interest?event_id=eq.${eventId}`,{
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const d=await res.json();return d.length;
}
async function deleteEvent(id){
  if(!confirm("Delete this event?"))return;
  await fetch(`${SUPABASE_URL}rest/v1/barangay_events?id=eq.${id}`,{
    method:"DELETE",
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  loadEvents();
}

/* MODAL FUNCTIONS */
const modal=document.getElementById("editModal");
const editForm=document.getElementById("editForm");
function closeModal(){modal.classList.remove("active");}

/* OPEN EDIT MODAL */
async function openEdit(id){
  const res=await fetch(`${SUPABASE_URL}rest/v1/barangay_events?id=eq.${id}&select=*`,{
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const [event]=await res.json();
  if(!event)return alert("Event not found.");
  modal.classList.add("active");
  editForm.id.value=event.id;
  editForm.title.value=event.title;
  editForm.description.value=event.description||"";
  editForm.venue.value=event.venue||"";
  editForm.category.value=event.category||"General";
  editForm.visibility.value=event.visibility||"public";
  if(event.start_date)editForm.start_date.value=new Date(event.start_date).toISOString().slice(0,16);
  if(event.end_date)editForm.end_date.value=new Date(event.end_date).toISOString().slice(0,16);
}

/* SUBMIT EDIT FORM */
editForm.addEventListener("submit",async e=>{
  e.preventDefault();
  const id=editForm.id.value;
  const formData=new FormData(editForm);
  const update=Object.fromEntries(formData.entries());
  delete update.id;
  await fetch(`${SUPABASE_URL}rest/v1/barangay_events?id=eq.${id}`,{
    method:"PATCH",
    headers:{
      apikey:SUPABASE_KEY,
      Authorization:"Bearer "+SUPABASE_KEY,
      "Content-Type":"application/json"
    },
    body:JSON.stringify(update)
  });
  closeModal();
  loadEvents();
});

loadEvents();
</script>
</body>
</html>
