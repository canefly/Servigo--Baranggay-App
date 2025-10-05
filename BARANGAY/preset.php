<?php include 'INCLUDES/barangaySidebar.php'; ?> 
<?php include 'INCLUDES/barangayTopbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Preset Page</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
  <style>
    :root {
      --topbar-height: 55px;
      --sidebar-width: 240px;

      --bg:#f5f7fa; 
      --card:#ffffff;
      --text:#222; 
      --muted:#6b7280; 
      --border:#e5e7eb;
      --brand:#047857; 
      --accent:#10b981;
      --pending:#f59e42; 
      --declined:#ef4444; 
      --ready:#0ea5e9; 
      --completed:#16a34a;
      --radius:14px; 
      --shadow:0 2px 8px rgba(0,0,0,.08);
    }

    *{box-sizing:border-box;}

    /* Global baseline — prevents topbar shifts */
    body {
      margin: 0;
      padding-top: var(--topbar-height); /* reserve space for fixed topbar */
      font-family: system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .layout { 
      display:flex; 
      min-height:100vh; 
    }

    .main-content { 
      flex:1; 
      padding:16px; 
      transition:margin-left .3s ease; 
      width:100%; 
    }

    @media(min-width:1024px){
      .main-content { 
        margin-left: var(--sidebar-width); 
      }
    }

    /* Header */
    .dashboard-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      margin-bottom:20px;
      background:var(--card);
      border:1px solid var(--border);
      padding:14px 20px;
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      flex-wrap:wrap;
    }

    .dashboard-header-left{
      display:flex;
      align-items:center;
      gap:14px;
    }

    .dashboard-header img{
      height:48px;
      width:48px;
      border-radius:10px;
      object-fit:cover;
    }

    .dashboard-title{
      font-size:1.4rem;
      font-weight:700;
      color:var(--brand);
    }
    
    /* Placeholder container */
    .content-placeholder {
      background: var(--card);
      border: 1px dashed var(--border);
      border-radius: var(--radius);
      padding: 50px;
      text-align: center;
      color: var(--muted);
      font-size: 1rem;
      box-shadow: var(--shadow);
    }

    /* Modal template */
    .modal-bg{
      display:none;
      position:fixed;
      inset:0;
      z-index:2000;
      background:rgba(0,0,0,.35);
      align-items:center;
      justify-content:center;
    }
    .modal-bg.active{display:flex;}
    .modal{
      background:#fff;
      border-radius:var(--radius);
      box-shadow:0 2px 12px rgba(0,0,0,.2);
      max-width:400px;
      width:95%;
      padding:20px;
      display:flex;
      flex-direction:column;
      gap:14px;
    }
    .modal-actions{
      display:flex;
      gap:10px;
      justify-content:flex-end;
    }
    .modal-btn{
      all:unset;
      cursor:pointer;
      padding:8px 16px;
      border-radius:8px;
      font-weight:600;
      font-size:.9rem;
    }
    .modal-btn.decline{background:var(--declined);color:#fff;}
    .modal-btn.cancel{background:#f3f4f6;color:var(--text);}
    .modal-error{color:var(--declined);font-size:.85rem;}

    /* Mobile tweaks */
    @media(max-width:768px){
      .dashboard-header{
        flex-direction:column;
        align-items:flex-start;
      }
      .dashboard-title{font-size:1.2rem;}
    }
  </style>
</head>
<body>
<div class="layout">
  <main class="main-content">
    <div class="dashboard-container">
      <div class="dashboard-header">
        <div class="dashboard-header-left">
          <img src="B.png" alt="Barangay Logo">
          <div class="dashboard-title">Preset Page</div>
        </div>
      </div>

      <!-- Placeholder section -->
      <div class="content-placeholder">
        <i class='bx bx-layer' style="font-size:2rem;display:block;margin-bottom:10px;"></i>
        This is a clean preset page. Add your module content here.
      </div>

    </div>
  </main>
</div>

<!-- Modal template -->
<div id="modalBg" class="modal-bg">
  <form class="modal" id="genericModal">
    <h3 style="margin:0;color:var(--brand)">Example Modal</h3>
    <p>This modal is ready to be reused for new modules.</p>
    <div class="modal-actions">
      <button class="modal-btn cancel" type="button" onclick="closeModal()">Close</button>
    </div>
  </form>
</div>

<script>
function closeModal(){
  document.getElementById("modalBg").classList.remove("active");
}
</script>
</body>
</html>
