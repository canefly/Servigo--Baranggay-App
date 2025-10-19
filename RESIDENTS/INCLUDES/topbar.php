<!-- topbar.php -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root{
  --bg:#f5f7fa; --card:#ffffff; --text:#1f2937; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb; --hover:#f3f4f6; --danger:#dc2626;
}

/* Scope EVERYTHING under .sg-topbar so it won't affect other components */
.sg-topbar{
  position:sticky; top:0; z-index:40;
  background:var(--card);
  border-bottom:1px solid var(--border);
  display:flex; justify-content:space-between; align-items:center;
  padding:10px 20px; transition:all .3s ease;
  isolation:isolate; /* extra safety */
}
.sg-topbar .sg-brand{display:flex; align-items:center; gap:10px; flex:1; min-width:0;}
.sg-topbar .sg-brand img{width:36px; height:36px; border-radius:10px; object-fit:cover;}
.sg-topbar .sg-brand h1{margin:0; font-size:1rem; font-weight:700; color:var(--brand); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;}

.sg-topbar .sg-right{display:flex; align-items:center; gap:10px; position:relative; flex-wrap:wrap; justify-content:flex-end;}
.sg-topbar .sg-chip{background:var(--hover); color:var(--text); padding:5px 10px; border-radius:6px; font-size:13px; white-space:nowrap; max-width:150px; overflow:hidden; text-overflow:ellipsis;}

.sg-topbar .sg-icon-btn{position:relative; width:40px; height:40px; border-radius:50%; background:var(--hover); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s ease; flex-shrink:0;}
.sg-topbar .sg-icon-btn:hover{background:rgba(30,64,175,.1);}
.sg-topbar .sg-icon-btn i{font-size:22px; color:var(--brand);}

/* Badge */
.sg-topbar .sg-badge{position:absolute; top:6px; right:6px; background:var(--danger); color:#fff; font-size:10px; font-weight:600; padding:2px 5px; border-radius:10px; display:none;}

/* DROPDOWNS (scoped) */
.sg-topbar .sg-dropdown{
  position:absolute; top:110%;
  background:#fff; border:1px solid var(--border); border-radius:8px;
  box-shadow:0 4px 14px rgba(0,0,0,.08);
  display:none; flex-direction:column; min-width:200px; max-height:350px; overflow-y:auto;
  z-index:999;
}
.sg-topbar .sg-dropdown.sg-show{display:flex;}
/* Positions so they don't overlap */
.sg-topbar #sg-notifDropdown{right:60px;}
.sg-topbar #sg-userDropdown{right:0;}

.sg-topbar .sg-dropdown a{
  padding:10px 14px; text-decoration:none; color:var(--text); font-size:14px;
  display:flex; align-items:center; gap:6px; transition:background .2s;
}
.sg-topbar .sg-dropdown a:hover{background:var(--hover); color:var(--brand);}

/* Notification items */
.sg-topbar .sg-notif-item{padding:10px 14px; border-bottom:1px solid var(--border); font-size:14px; color:var(--text); background:#fff; transition:background .2s;}
.sg-topbar .sg-notif-item:hover{background:var(--hover);}
.sg-topbar .sg-notif-item.sg-unread{border-left:4px solid var(--accent); background:#f0fdf4;}
.sg-topbar .sg-notif-item small{color:var(--muted); font-size:12px; display:block; margin-top:2px;}

/* Responsive */
@media (max-width:768px){
  .sg-topbar{flex-direction:column; align-items:stretch; gap:8px; padding:10px 14px;}
  .sg-topbar .sg-brand{justify-content:center;}
  .sg-topbar .sg-brand h1{font-size:15px;}
  .sg-topbar .sg-right{justify-content:space-between; background:#f9fafb; padding:6px 10px; border-radius:8px;}
  .sg-topbar .sg-chip{flex:1; text-align:center; font-size:12px; padding:4px 6px;}
  .sg-topbar .sg-icon-btn{width:36px; height:36px;}
  .sg-topbar .sg-icon-btn i{font-size:20px;}
  .sg-topbar #sg-notifDropdown, .sg-topbar #sg-userDropdown{right:auto; left:0; min-width:90%;}
}
@media (max-width:480px){
  .sg-topbar .sg-brand img{width:32px; height:32px;}
  .sg-topbar .sg-brand h1{font-size:14px;}
  .sg-topbar .sg-right{gap:6px;}
  .sg-topbar .sg-icon-btn{width:34px; height:34px;}
}
</style>

<header class="sg-topbar" id="sg-topbar">
  <div class="sg-brand">
    <img src="/SERVIGO/RESIDENTS/INCLUDES/logo.png" alt="Servigo Logo">
    <h1>Servigo · Residents</h1>
  </div>

  <div class="sg-right">
    <span class="sg-chip">Logged in as: <strong id="sg-residentName">Resident</strong></span>
    <span class="sg-chip">Barangay: <strong id="sg-brgyName">—</strong></span>

    <!-- Notifications -->
    <div class="sg-icon-btn" id="sg-notifBtn" title="Notifications" aria-haspopup="true" aria-expanded="false">
      <i class='bx bx-bell'></i>
      <span class="sg-badge" id="sg-notifBadge">0</span>
    </div>
    <div class="sg-dropdown" id="sg-notifDropdown" role="menu" aria-label="Notifications">
      <div id="sg-notifList">
        <p style="padding:10px;color:var(--muted);font-size:13px;">No notifications yet.</p>
      </div>
    </div>

    <!-- User -->
    <div class="sg-icon-btn" id="sg-userIcon" title="Account" aria-haspopup="true" aria-expanded="false">
      <i class='bx bx-user'></i>
    </div>
    <div class="sg-dropdown" id="sg-userDropdown" role="menu" aria-label="Account">
      <a href="verifyAccount.php"><i class='bx bx-check-shield'></i> Verify Account</a>
      <a href="#" id="sg-logoutBtn"><i class='bx bx-log-out'></i> Logout</a>
    </div>
  </div>
</header>

<script>
(() => {
  // Read session (namespaced IDs)
  const name = localStorage.getItem('sg_name') || 'Resident';
  const brgy = localStorage.getItem('sg_brgy') || '—';
  document.getElementById('sg-residentName').textContent = name;
  document.getElementById('sg-brgyName').textContent = brgy;

  // Elements
  const topbar = document.getElementById('sg-topbar');
  const userIcon = document.getElementById('sg-userIcon');
  const userDd   = document.getElementById('sg-userDropdown');
  const notifBtn = document.getElementById('sg-notifBtn');
  const notifDd  = document.getElementById('sg-notifDropdown');
  const notifBadge = document.getElementById('sg-notifBadge');
  const notifList  = document.getElementById('sg-notifList');

  // Toggle helpers (scoped; won’t touch other components)
  const show = el => el.classList.add('sg-show');
  const hide = el => el.classList.remove('sg-show');
  const toggle = el => el.classList.toggle('sg-show');

  userIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    toggle(userDd);
    hide(notifDd);
  });
  notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    toggle(notifDd);
    hide(userDd);
    notifBadge.style.display = 'none'; // hide badge when viewing
  });

  // Click outside to close (scoped to topbar)
  document.addEventListener('click', (e) => {
    if (!topbar.contains(e.target)) { hide(userDd); hide(notifDd); }
  });

  // Logout
  document.getElementById('sg-logoutBtn').addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = "index.php";
  });

  // ===== Notifications loader (adjust key/URL to your project) =====

const SUPABASE_URL="https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
  const RESIDENT_ID  = localStorage.getItem("sg_id");

  async function loadNotifications(){
    if(!RESIDENT_ID){
      notifList.innerHTML = "<p style='padding:10px'>Please log in.</p>";
      notifBadge.style.display = "none";
      return;
    }
    try{
      const res = await fetch(`${SUPABASE_URL}/rest/v1/notifications?recipient_type=eq.resident&recipient_id=eq.${RESIDENT_ID}&order=created_at.desc`,{
        headers:{apikey:SUPABASE_KEY,Authorization:`Bearer ${SUPABASE_KEY}`}
      });
      const data = await res.json();

      if(!Array.isArray(data) || data.length===0){
        notifList.innerHTML = "<p style='padding:10px;color:var(--muted);font-size:13px;'>No notifications yet.</p>";
        notifBadge.style.display = "none";
        return;
      }
      const unread = data.filter(n => !n.is_read).length;
      notifBadge.textContent = unread;
      notifBadge.style.display = unread > 0 ? "block" : "none";

      notifList.innerHTML = data.map(n => `
        <div class="sg-notif-item ${n.is_read ? '' : 'sg-unread'}" data-id="${n.id}">
          <strong>${n.title}</strong>
          <p style="margin:4px 0">${n.message}</p>
          <small>${new Date(n.created_at).toLocaleString()}</small>
        </div>
      `).join("");

      // Optional: mark as read on click
      notifList.querySelectorAll('.sg-notif-item').forEach(el => {
        el.addEventListener('click', async () => {
          const id = el.getAttribute('data-id');
          // mark read
          await fetch(`${SUPABASE_URL}/rest/v1/notifications?id=eq.${id}`,{
            method:"PATCH",
            headers:{apikey:SUPABASE_KEY,Authorization:`Bearer ${SUPABASE_KEY}`,"Content-Type":"application/json"},
            body:JSON.stringify({is_read:true})
          });
          el.classList.remove('sg-unread');
          // (optional) navigate if you store n.link
          // window.location.href = n.link;
        });
      });

    }catch(err){
      notifList.innerHTML = "<p style='padding:10px;color:var(--muted)'>Failed to load notifications.</p>";
      notifBadge.style.display = "none";
    }
  }

  loadNotifications();
})();
</script>
