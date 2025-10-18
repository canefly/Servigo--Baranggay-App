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
.topbar .logo {
  width:32px; height:32px; border-radius:8px;
  background:linear-gradient(135deg,var(--brand),var(--accent));
  display:grid; place-items:center; font-weight:800; color:#fff;
}
.topbar h1 { margin:0; font-size:16px; color:var(--brand); }
.topbar .right { display:flex; gap:12px; align-items:center; position:relative; }
.user-icon {
  font-size:22px; color:var(--brand); cursor:pointer;
  padding:8px; border-radius:50%; background:#f3f4f6;
  transition:background .2s;
}
.user-icon:hover { background:rgba(30,64,175,.1); }

/* Dropdown */
.dropdown {
  position:absolute; top:100%; right:0;
  background:#fff; border:1px solid var(--border);
  border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.08);
  display:none; flex-direction:column; min-width:140px;
}
.dropdown a {
  padding:10px 14px; text-decoration:none; color:var(--text);
  font-size:14px; border-radius:6px;
}
.dropdown a:hover { background:#f3f4f6; }

/* Show dropdown when parent is hovered */
.right:hover .dropdown { display:flex; }

 .brand {
    display: flex;
    align-items: center;
    gap: 8px; /* space between logo and text */
  }
  .brand img {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    object-fit: cover;
  }
  .brand h1 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    color: #1e40af; /* matches your brand color */
  }

@media(max-width:480px){
  .topbar { flex-direction:column; align-items:flex-start; gap:8px }
  .brand { flex:none }
  .topbar h1 { font-size:15px }
  .topbar .chip { font-size:13px }
  .right {
    width:100%; justify-content:space-between;
    background:#f9fafb; padding:6px 10px; border-radius:8px;
  }
  .right span { font-size:13px }
  .user-icon { padding:6px; font-size:20px }
}

</style>

<header class="topbar">
 <div class="brand">
  <img src="/SERVIGO/RESIDENTS/INCLUDES/logo.png" alt="Servigo Logo" >
  <h1>Servigo · Residents</h1>
</div>
 
  
  <!-- ✅ Your original right block preserved -->
  <div class="right">
   
    <span class="chip">Logged in as: <strong id="residentName">Resident</strong></span>
    <span class="">Barangay: <strong id="brgyName">—</strong></span>
     <a href="verifyAccount.php" title="Verify Account">
      <i class='bx bx-user user-icon'></i>
    </a>

    <!-- Dropdown (appears on hover) -->
    <div class="dropdown">
      <a href="#" id="logoutBtn"><i class='bx bx-log-out'></i> Logout</a>
    </div>
  </div>
</header>

<script>
  // Fill resident data from localStorage
  const residentName = localStorage.getItem('sg_name') || 'Resident';
  const barangayName = localStorage.getItem('sg_brgy') || '—';
  document.getElementById('residentName').textContent = residentName;
  document.getElementById('brgyName').textContent = barangayName;

  // Logout clears session and redirects
  document.getElementById('logoutBtn').addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = "index.php";
  });
</script>


