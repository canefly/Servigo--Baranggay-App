<?php include 'INCLUDES/barangaySidebar.php'; ?> 
<?php include 'INCLUDES/barangayTopbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Resident Verification · Servigo</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff;
      --text:#222; --muted:#6b7280; --border:#e5e7eb;
      --brand:#047857; --accent:#10b981;
      --declined:#ef4444;
      --radius:14px; --shadow:0 2px 8px rgba(0,0,0,.08);
      --sidebar-width:240px;
    }
    *{box-sizing:border-box;}
    body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);}
    .layout{display:flex;min-height:100vh;}
    .main-content{flex:1;padding:16px;transition:margin-left .3s ease;width:100%;}
    @media(min-width:1024px){.main-content{margin-left:var(--sidebar-width);}}

    /* Header */
    .dashboard-header{
      display:flex;align-items:center;gap:14px;margin-bottom:20px;
      background:var(--card);border:1px solid var(--border);padding:14px 20px;
      border-radius:var(--radius);box-shadow:var(--shadow);flex-wrap:wrap;
    }
    .dashboard-title{font-size:1.4rem;font-weight:700;color:var(--brand);}

    /* Tabs */
    .filter-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
    .filter-tab{
      all:unset;cursor:pointer;padding:8px 18px;border-radius:999px;
      font-weight:600;font-size:.95rem;
      border:1px solid var(--border);background:#f3f4f6;color:var(--brand);
      transition:.2s;
    }
    .filter-tab.active{
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff;border:none;box-shadow:0 2px 6px rgba(0,0,0,.1);
    }

    /* Residents grid */
    .residents-list{
      grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
      gap:16px;
    }
    .resident-card{
      background:var(--card);border:1px solid var(--border);
      border-radius:var(--radius);box-shadow:var(--shadow);
      padding:16px;display:flex;flex-direction:column;gap:10px;
      transition:.2s;
    }
    .resident-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);}

    .resident-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;}
    .resident-info{display:flex;flex-direction:column;}
    .resident-name{font-weight:700;font-size:1.05rem;color:var(--text);}
    .resident-meta{font-size:.85rem;color:var(--muted);}
    .toggle-btn{
      all:unset;cursor:pointer;padding:6px 14px;border-radius:8px;
      background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;
      font-weight:600;font-size:.85rem;display:flex;align-items:center;gap:6px;
    }

    .resident-body{display:none;animation:fade .25s ease;}
    .resident-card.open .resident-body{display:block;}
    @keyframes fade{from{opacity:0;transform:translateY(-4px);}to{opacity:1;transform:none;}}

    .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;margin-top:10px;}
    .field{background:#f9fafb;border:1px solid var(--border);padding:8px;border-radius:8px;font-size:.9rem;}

    .id-section{margin-top:14px;}
    .id-type{font-size:.9rem;font-weight:600;color:var(--brand);margin-bottom:6px;}
    .id-preview img{max-width:100%;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.1);}

    .actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;}
    .btn{
      all:unset;cursor:pointer;display:flex;align-items:center;justify-content:center;
      gap:6px;padding:8px 16px;border-radius:12px;font-weight:700;font-size:.9rem;
      box-shadow:0 2px 8px rgba(0,0,0,.07);
    }
    .btn.verify{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;}
    .btn.reject{background:var(--declined);color:#fff;}
    .no-residents{text-align:center;color:var(--muted);font-size:1rem;margin-top:30px;}

    @media(max-width:768px){
      .dashboard-title{font-size:1.2rem;}
      .btn{flex:1;}
    }
  </style>
</head>
<body>
<div class="layout">
  <main class="main-content">
    <div class="dashboard-container">
      <div class="dashboard-header">
        <div class="dashboard-title">Resident Verification</div>
      </div>

      <nav class="filter-tabs">
        <button class="filter-tab active" id="tab-unverified">Unverified</button>
        <button class="filter-tab" id="tab-verified">Verified</button>
      </nav>

      <section id="residentsList" class="residents-list"></section>
      <div id="noResidents" class="no-residents" style="display:none;">
        <i class='bx bx-user-x' style="font-size:2rem;"></i><br>
        No residents in this list.
      </div>
    </div>
  </main>
</div>

<script>
let unverified=[
  {id:1,last_name:"Garcia",first_name:"Juan",middle_name:"Santos",suffix:"",birthdate:"1998-05-10",house_no:"123",street:"Mango St",purok:"2",subdivision:"",barangay:"San Isidro",city:"Quezon City",province:"Metro Manila",region:"NCR",postal:"1100",nationality:"Filipino",id_type:"PhilHealth ID",valid_id_url:"https://placehold.co/400x240?text=Resident+ID+1"},
  {id:2,last_name:"Reyes",first_name:"Maria",middle_name:"Lopez",suffix:"",birthdate:"2000-11-20",house_no:"45",street:"Banana Rd",purok:"5",subdivision:"Palm Subd",barangay:"Sta. Lucia",city:"Pasig",province:"Metro Manila",region:"NCR",postal:"1600",nationality:"Filipino",id_type:"Driver’s License",valid_id_url:"https://placehold.co/400x240?text=Resident+ID+2"}
];
let verified=[];
let activeTab="unverified";

function render(){
  const list=document.getElementById("residentsList");list.innerHTML="";
  const data=activeTab==="unverified"?unverified:verified;
  document.getElementById("noResidents").style.display=data.length?"none":"block";

  data.forEach(r=>{
    let card=document.createElement("div");card.className="resident-card";
    card.innerHTML=`
      <div class="resident-header">
        <div class="resident-info">
          <span class="resident-name"><i class='bx bx-user'></i> ${r.last_name}, ${r.first_name}</span>
          
        </div>
        <button class="toggle-btn" onclick="toggleResident(${r.id})" id="toggle-${r.id}">
          <i class='bx bx-show'></i> Show Details
        </button>
      </div>
      <div class="resident-body" id="body-${r.id}">
        <div class="info-grid">
          <div class="field">DOB: ${r.birthdate}</div>
          <div class="field">House No: ${r.house_no}</div>
          <div class="field">Street: ${r.street}</div>
          <div class="field">Purok: ${r.purok}</div>
          <div class="field">Subdivision: ${r.subdivision||"-"}</div>
          <div class="field">Barangay: ${r.barangay}</div>
          <div class="field">Municipality/City: ${r.city}</div>
          <div class="field">Province: ${r.province}</div>
          <div class="field">Postal: ${r.postal}</div>
          <div class="field">Region: ${r.region}</div>
          <div class="field">Nationality: ${r.nationality}</div>
          <div class="field">ID Type: ${r.id_type}</div>
        </div>
        <div class="id-section">
          
          <div class="id-preview"><img src="${r.valid_id_url}" alt="Valid ID"></div>
        </div>
        ${activeTab==="unverified"?`
        <div class="actions">
          <button class="btn verify" onclick="verifyResident(${r.id})"><i class='bx bx-check'></i> Verify</button>
          <button class="btn reject" onclick="rejectResident(${r.id})"><i class='bx bx-x'></i> Reject</button>
        </div>`:""}
      </div>`;
    list.appendChild(card);
  });
}

function toggleResident(id){
  const card=document.querySelector(`#body-${id}`);
  const btn=document.getElementById(`toggle-${id}`);
  const parent=card.parentNode;
  parent.classList.toggle("open");
  if(parent.classList.contains("open")){
    card.style.display="block";
    btn.innerHTML="<i class='bx bx-x'></i> Close";
  } else {
    card.style.display="none";
    btn.innerHTML="<i class='bx bx-show'></i> Show Details";
  }
}

function verifyResident(id){
  const r=unverified.find(x=>x.id===id);
  if(!r)return;
  unverified=unverified.filter(x=>x.id!==id);
  verified.push(r);
  render();
}
function rejectResident(id){
  unverified=unverified.filter(x=>x.id!==id);
  render();
}

document.getElementById("tab-unverified").onclick=()=>{activeTab="unverified";setActiveTab();};
document.getElementById("tab-verified").onclick=()=>{activeTab="verified";setActiveTab();};
function setActiveTab(){
  document.querySelectorAll(".filter-tab").forEach(t=>t.classList.remove("active"));
  document.getElementById("tab-"+activeTab).classList.add("active");
  render();
}
render();
</script>
</body>
</html>
