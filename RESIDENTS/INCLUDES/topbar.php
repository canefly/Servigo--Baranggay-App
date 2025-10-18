<!-- topbar.php -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --bg:#f5f7fa; --card:#ffffff; --text:#222222; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
}
.topbar {
  position:sticky; top:0; z-index:40; background:#fff;
  border-bottom:1px solid var(--border);
  display:flex; justify-content:space-between; align-items:center;
  padding:10px 16px;
}
.topbar .brand { display:flex; align-items:center; gap:10px; }
.brand img { width:32px; height:32px; border-radius:8px; object-fit:cover; }
.brand h1 { margin:0; font-size:18px; font-weight:700; color:var(--brand); }

.topbar .right { display:flex; gap:12px; align-items:center; position:relative; }

.user-menu, .bell-wrap {
  position:relative;
  display:flex;
  align-items:center;
}
.user-icon, .bx-bell {
  font-size:22px;
  color:var(--brand);
  cursor:pointer;
  padding:8px;
  border-radius:50%;
  background:#f3f4f6;
  transition:background .2s;
}
.user-icon:hover, .bx-bell:hover {
  background:rgba(30,64,175,.1);
}

/* Dropdowns */
.dropdown, .notif-dropdown {
  position:absolute;
  top:115%;
  right:0;
  background:#fff;
  border:1px solid var(--border);
  border-radius:8px;
  box-shadow:0 4px 12px rgba(0,0,0,.08);
  display:none;
  flex-direction:column;
  min-width:160px;
  z-index:99;
}
.dropdown a, .notif-item {
  padding:10px 14px;
  text-decoration:none;
  color:var(--text);
  font-size:14px;
  border-radius:6px;
}
.dropdown a:hover, .notif-item:hover {
  background:#f3f4f6;
}

/* 🔽 Show menu when toggled */
.dropdown.show {
  display:flex !important;
}

@media(max-width:480px){
  .topbar { flex-direction:column; align-items:flex-start; gap:8px; }
  .right {
    width:100%;
    justify-content:space-between;
    background:#f9fafb;
    padding:6px 10px;
    border-radius:8px;
  }
  .user-icon, .bx-bell {
    padding:6px;
    font-size:20px;
  }
}
</style>

<header class="topbar">
  <div class="brand">
    <img src="/SERVIGO/RESIDENTS/INCLUDES/logo.png" alt="Servigo Logo">
    <h1>Servigo · Residents</h1>
  </div>

  <div class="right">
    <span class="chip">Logged in as: <strong id="residentName">Resident</strong></span>
    <span>Barangay: <strong id="brgyName">—</strong></span>

    <!-- 🔔 Notification Bell -->
    <div class="bell-wrap">
      <i class='bx bx-bell'></i>
      <div class="notif-dropdown">
        <div class="notif-item muted">No notifications yet.</div>
      </div>
    </div>

    <!-- 👤 Account Menu -->
    <div class="user-menu">
      <i class='bx bx-user user-icon'></i>
      <div class="dropdown">
        <a href="verifyAccount.php"><i class='bx bx-id-card'></i> Verify Account</a>
        <a href="#" id="logoutBtn"><i class='bx bx-log-out'></i> Logout</a>
      </div>
    </div>
  </div>
</header>

<script>
 const residentName = localStorage.getItem('sg_name') || 'Resident';
  const barangayName = localStorage.getItem('sg_brgy') || '—';
  document.getElementById('residentName').textContent = residentName;
  document.getElementById('brgyName').textContent = barangayName;

  // Logout clears session and redirects
  document.getElementById('logoutBtn').addEventListener('click', e => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = "index.php";
  });

  // 🧠 Dropdown Toggle Logic
  const userMenuIcon = document.querySelector('.user-icon');
  const dropdown = document.querySelector('.dropdown');

  userMenuIcon.addEventListener('click', (e) => {
    e.stopPropagation(); // Prevent closing when clicking the icon itself
    dropdown.classList.toggle('show');
  });

  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target) && !userMenuIcon.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  });

  
/* 🔔 Supabase setup */
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

const notifCount = document.getElementById('notifCount');
const notifDropdown = document.getElementById('notifDropdown');

/* 📨 Load notifications */
async function loadNotifications(){
  if(!residentId) return;
  const res = await fetch(`${SUPABASE_URL}/rest/v1/notifications?resident_id=eq.${residentId}&order=created_at.desc&limit=10`, {
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const data = await res.json();
  renderNotifications(data);
}

function renderNotifications(notifs){
  notifDropdown.innerHTML='';
  if(!notifs.length){
    notifDropdown.innerHTML='<div class="notif-item muted">No notifications yet.</div>';
    notifCount.style.display='none'; return;
  }
  let unread = notifs.filter(n=>!n.is_read).length;
  notifCount.textContent = unread;
  notifCount.style.display = unread>0?'block':'none';
  notifs.forEach(n=>{
    const div=document.createElement('div');
    div.className='notif-item';
    div.textContent = `${n.title}: ${n.message}`;
    notifDropdown.appendChild(div);
  });
}

/* 🧠 Toast helper */
function showToast(msg){
  const t=document.createElement('div');
  t.className='notif-toast';
  t.textContent=msg;
  document.body.appendChild(t);
  requestAnimationFrame(()=>t.style.opacity='1');
  setTimeout(()=>{t.style.opacity='0';setTimeout(()=>t.remove(),400)},5000);
}

/* ⚡ Realtime subscription */
if(residentId){
  supabase.channel('notif_channel')
    .on('postgres_changes',{event:'INSERT',schema:'public',table:'notifications'},payload=>{
      const n=payload.new;
      if(n.resident_id===residentId){
        showToast(`${n.title}: ${n.message}`);
        animateBell();
        loadNotifications();
      }
    })
    .subscribe();
}

/* 🌀 Animation handler */
function animateBell(){
  const bell = document.querySelector('.bx-bell');
  const badge = document.getElementById('notifCount');
  bell.classList.add('animate');
  badge.classList.add('pop');
  setTimeout(()=>bell.classList.remove('animate'),800);
  setTimeout(()=>badge.classList.remove('pop'),400);
}

loadNotifications();
</script>
