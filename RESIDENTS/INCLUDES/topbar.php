<!-- topbar.php -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --bg:#f5f7fa;
  --card:#ffffff;
  --text:#1f2937;
  --muted:#6b7280;
  --brand:#1e40af;
  --accent:#16a34a;
  --border:#e5e7eb;
  --hover:#f3f4f6;
  --danger:#dc2626;
}

/* ==================== Base Topbar ==================== */
.topbar {
  position:sticky; top:0; z-index:40;
  background:var(--card);
  border-bottom:1px solid var(--border);
  display:flex; justify-content:space-between; align-items:center;
  padding:10px 20px;
  transition:all .3s ease;
}

/* ==================== Branding ==================== */
.brand {
  display:flex; align-items:center; gap:10px;
  flex:1;
  min-width:0;
}
.brand img {
  width:36px; height:36px; border-radius:10px; object-fit:cover;
}
.brand h1 {
  margin:0; font-size:1rem; font-weight:700; color:var(--brand);
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}

/* ==================== Right Section ==================== */
.right {
  display:flex; align-items:center; gap:10px;
  position:relative;
  flex-wrap:wrap;
  justify-content:flex-end;
}
.chip {
  background:var(--hover);
  color:var(--text);
  padding:5px 10px;
  border-radius:6px;
  font-size:13px;
  white-space:nowrap;
  max-width:150px;
  overflow:hidden;
  text-overflow:ellipsis;
}

/* ==================== Circle Buttons ==================== */
.icon-btn {
  position:relative;
  width:40px; height:40px;
  border-radius:50%;
  background:var(--hover);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer;
  transition:all .2s ease;
  flex-shrink:0;
}
.icon-btn:hover {
  background:rgba(30,64,175,.1);
}
.icon-btn i {
  font-size:22px;
  color:var(--brand);
}

/* ==================== Notification Badge ==================== */
.notification-badge {
  position:absolute;
  top:6px; right:6px;
  background:var(--danger);
  color:#fff;
  font-size:10px;
  font-weight:600;
  padding:2px 5px;
  border-radius:10px;
  display:none;
}

/* ==================== Dropdown ==================== */
.dropdown {
  position:absolute; top:110%; right:0;
  background:#fff;
  border:1px solid var(--border);
  border-radius:8px;
  box-shadow:0 4px 14px rgba(0,0,0,.08);
  display:none; flex-direction:column;
  min-width:160px;
}
.dropdown.show { display:flex; }
.dropdown a {
  padding:10px 14px;
  text-decoration:none; color:var(--text);
  font-size:14px;
  display:flex; align-items:center; gap:6px;
  transition:background .2s;
}
.dropdown a:hover { background:var(--hover); color:var(--brand); }

/* ==================== Modal ==================== */
.modal-overlay {
  position:fixed;
  top:0; left:0; width:100%; height:100%;
  background:rgba(0,0,0,0.4);
  display:none; align-items:center; justify-content:center;
  z-index:1000;
}
.modal {
  background:#fff;
  border-radius:10px;
  padding:20px;
  width:90%;
  max-width:400px;
  box-shadow:0 10px 30px rgba(0,0,0,0.15);
  animation:pop .25s ease;
}
@keyframes pop {
  from { transform:scale(.9); opacity:0; }
  to { transform:scale(1); opacity:1; }
}
.modal h2 {
  font-size:18px; margin-bottom:12px; color:var(--brand);
}
.modal p {
  font-size:14px; color:var(--text); margin-bottom:8px;
}
.modal button {
  margin-top:10px; background:var(--brand);
  border:none; color:#fff; padding:8px 14px;
  border-radius:6px; cursor:pointer;
  transition:background .2s;
}
.modal button:hover { background:#16308a; }

/* ==================== Responsiveness ==================== */

/* Medium: Tablets */
@media (max-width:768px){
  .topbar {
    flex-direction:column;
    align-items:stretch;
    gap:8px;
    padding:10px 14px;
  }
  .brand { justify-content:center; }
  .brand h1 { font-size:15px; }
  .right {
    justify-content:space-between;
    background:#f9fafb;
    padding:6px 10px;
    border-radius:8px;
  }
  .chip { flex:1; text-align:center; font-size:12px; padding:4px 6px; }
  .icon-btn { width:36px; height:36px; }
  .icon-btn i { font-size:20px; }
}

/* Small: Phones */
@media (max-width:480px){
  .brand img { width:32px; height:32px; }
  .brand h1 { font-size:14px; }
  .right { flex-wrap:wrap; gap:6px; }
  .chip {
    width:100%;
    justify-content:center;
    font-size:12px;
  }
  .icon-btn { width:34px; height:34px; }
  .dropdown { right:5px; min-width:140px; }
  .modal { width:95%; }
}
</style>

<header class="topbar">
  <div class="brand">
    <img src="/SERVIGO/RESIDENTS/INCLUDES/logo.png" alt="Servigo Logo">
    <h1>Servigo · Residents</h1>
  </div>

  <div class="right">
    <span class="chip">Logged in as: <strong id="residentName">Resident</strong></span>
    <span class="chip">Barangay: <strong id="brgyName">—</strong></span>

    <div class="icon-btn" id="notifBtn" title="Notifications">
      <i class='bx bx-bell'></i>
      <span class="notification-badge" id="notifBadge">3</span>
    </div>

    <div class="icon-btn" id="userIcon" title="Account">
      <i class='bx bx-user'></i>
    </div>

    <div class="dropdown" id="userDropdown">
      <a href="verifyAccount.php"><i class='bx bx-check-shield'></i> Verify Account</a>
      <a href="#" id="logoutBtn"><i class='bx bx-log-out'></i> Logout</a>
    </div>
  </div>
</header>

<div class="modal-overlay" id="notifModal">
  <div class="modal">
    <h2>Notifications</h2>
    <p>📢 Barangay announcement: Road repair on 4th Street starting tomorrow.</p>
    <p>🗂 Your document request has been approved.</p>
    <p>✅ Account verified successfully.</p>
    <button id="closeModal">Close</button>
  </div>
</div>

<script>
const residentName = localStorage.getItem('sg_name') || 'Resident';
const barangayName = localStorage.getItem('sg_brgy') || '—';
document.getElementById('residentName').textContent = residentName;
document.getElementById('brgyName').textContent = barangayName;

// User dropdown
const userIcon = document.getElementById('userIcon');
const dropdown = document.getElementById('userDropdown');
userIcon.addEventListener('click', e => {
  e.stopPropagation();
  dropdown.classList.toggle('show');
});
document.addEventListener('click', e => {
  if (!dropdown.contains(e.target) && !userIcon.contains(e.target))
    dropdown.classList.remove('show');
});

// Logout
document.getElementById('logoutBtn').addEventListener('click', e => {
  e.preventDefault();
  localStorage.clear();
  window.location.href = "index.php";
});

// Modal
const notifBtn = document.getElementById('notifBtn');
const notifModal = document.getElementById('notifModal');
const closeModal = document.getElementById('closeModal');
const notifBadge = document.getElementById('notifBadge');

notifBtn.addEventListener('click', () => {
  notifModal.style.display = 'flex';
  notifBadge.style.display = 'none';
});
closeModal.addEventListener('click', () => notifModal.style.display = 'none');
notifModal.addEventListener('click', (e) => {
  if (e.target === notifModal) notifModal.style.display = 'none';
});
</script>
