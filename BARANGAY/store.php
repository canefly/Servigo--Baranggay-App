<?php include 'INCLUDES/barangaySidebar.php'; ?> 
<?php include 'INCLUDES/barangayTopbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Local Services Management · Admin</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff;
      --text:#222; --muted:#6b7280; --border:#e5e7eb;
      --brand:#047857; --accent:#10b981; /* barangay-approved */
      --pending:#f59e42; --declined:#ef4444; --ready:#0ea5e9; --completed:#16a34a; /* licensed=ready blue */
      --radius:14px; --shadow:0 2px 8px rgba(0,0,0,.08);
      --sidebar-width:240px;
    }
    *{box-sizing:border-box;}
    body{margin:0;font-family:system-ui,Segoe UI,Roboto,Inter,sans-serif;background:var(--bg);color:var(--text);}
    .layout { display:flex; min-height:100vh; }
    .main-content { flex:1; padding:16px; transition:margin-left .3s ease; width:100%; }
    @media(min-width:1024px){ .main-content { margin-left: var(--sidebar-width); } }

    /* Header */
    .dashboard-header{
      display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px;
      background:var(--card);border:1px solid var(--border);padding:14px 20px;
      border-radius:var(--radius);box-shadow:var(--shadow);flex-wrap:wrap;
    }
    .dashboard-header-left{display:flex;align-items:center;gap:14px;}
    .dashboard-header img{height:48px;width:48px;border-radius:10px;object-fit:cover;}
    .dashboard-title{font-size:1.4rem;font-weight:700;color:var(--brand);}
    .subtle{color:var(--muted);font-size:.9rem;}

    /* Filters Bar */
    .filters-bar{
      display:flex;flex-wrap:wrap;align-items:center;gap:12px;justify-content:space-between;
      background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);
      padding:12px 14px;margin-bottom:18px;
    }
    .filters-left,.filters-right{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
    .chip{
      all:unset;cursor:pointer;padding:8px 12px;border-radius:999px;border:1px solid var(--border);background:#f3f4f6;
      font-weight:600;color:var(--text);font-size:.9rem;display:inline-flex;align-items:center;gap:6px;
    }
    .chip.active{background:var(--brand);border-color:var(--brand);color:#fff;}
    .search{
      position:relative;display:flex;align-items:center;
    }
    .search input{
      padding:10px 38px 10px 12px;border:1px solid var(--border);border-radius:10px;min-width:260px;outline:none;
      font-size:.95rem;background:#fff;
    }
    .search i{position:absolute;right:10px;color:var(--muted);font-size:1.2rem;}

    /* Grid + Cards */
    .grid{
      display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;
    }
    .card{
      background:var(--card);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);
      overflow:hidden;display:flex;flex-direction:column;transition:.18s;cursor:default;
    }
    .card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1);}
    .thumb{width:100%;height:140px;object-fit:cover;background:#eef2f7;}
    .body{padding:12px 14px;display:flex;flex-direction:column;gap:6px;}
    .title{margin:0;font-size:1.05rem;font-weight:700;color:var(--brand);}
    .meta{display:flex;flex-wrap:wrap;gap:8px;color:var(--muted);font-size:.88rem;}
    .badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;}
    .badge{padding:4px 8px;border-radius:8px;font-size:.75rem;font-weight:700;color:#fff;display:inline-flex;gap:6px;align-items:center;}
    .b-approved{background:var(--accent);}        /* 🏠 Barangay-Approved */
    .b-licensed{background:var(--ready);}        /* 📄 Licensed Business */
    .s-pending{background:var(--pending);}       /* ⏳ Pending */
    .s-approved{background:var(--completed);}    /* ✅ Approved */
    .s-declined{background:var(--declined);}     /* ❌ Declined */

    .actions{
      display:flex;gap:8px;padding:12px 14px;border-top:1px solid var(--border);background:#fafafa;flex-wrap:wrap;
    }
    .btn{
      all:unset;cursor:pointer;padding:8px 12px;border-radius:10px;border:1px solid var(--border);background:#fff;
      font-weight:700;font-size:.9rem;display:inline-flex;align-items:center;gap:6px;
    }
    .btn.view{border-color:#d1d5db;}
    .btn.approve{background:rgba(22,163,74,.1);border-color:#86efac;color:#166534;}
    .btn.decline{background:rgba(239,68,68,.08);border-color:#fecaca;color:#991b1b;}
    .btn.delete{background:#fff7f7;border-color:#fecaca;color:#991b1b;}

    /* Reused Modal */
    .modal-bg{display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.35);align-items:center;justify-content:center;padding:16px;}
    .modal-bg.active{display:flex;}
    .modal{background:#fff;border-radius:16px;box-shadow:0 12px 28px rgba(0,0,0,.25);max-width:680px;width:100%;padding:18px;display:flex;flex-direction:column;gap:14px;}
    .modal-head{display:flex;gap:12px;align-items:center;border-bottom:1px solid var(--border);padding-bottom:10px;}
    .modal-head .thumb{width:64px;height:64px;border-radius:10px;}
    .modal-title{margin:0;font-size:1.2rem;font-weight:800;color:var(--brand);display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
    .modal-sub{color:var(--muted);font-size:.9rem;}
    .modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .modal-section{background:#fafafa;border:1px solid var(--border);border-radius:12px;padding:12px;}
    .modal-section h4{margin:0 0 8px 0;font-size:.95rem;color:#111;}
    .doc-list{display:flex;gap:10px;flex-wrap:wrap;}
    .doc{width:100px;height:70px;border:1px solid var(--border);border-radius:8px;object-fit:cover;background:#fff;}
    .modal-actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:6px;}
    .modal-btn{all:unset;cursor:pointer;padding:9px 14px;border-radius:10px;font-weight:800;font-size:.92rem;}
    .modal-btn.close{background:#f3f4f6;color:#111;border:1px solid var(--border);}
    .modal-btn.approve{background:rgba(22,163,74,.12);color:#166534;border:1px solid #86efac;}
    .modal-btn.decline{background:rgba(239,68,68,.1);color:#991b1b;border:1px solid #fecaca;}

    /* Small screens */
    @media(max-width:768px){
      .dashboard-header{flex-direction:column;align-items:flex-start;}
      .dashboard-title{font-size:1.2rem;}
      .modal-grid{grid-template-columns:1fr;}
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
          <div>
            <div class="dashboard-title">Local Services Management</div>
            <div class="subtle">Review, verify, and maintain barangay-endorsed services.</div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-bar">
        <div class="filters-left">
          <!-- TYPE -->
          <button class="chip chip-type active" data-type="all"><i class='bx bx-category'></i> All Types</button>
          <button class="chip chip-type" data-type="barangay-approved"><i class='bx bx-home-smile'></i> Barangay-Approved</button>
          <button class="chip chip-type" data-type="licensed"><i class='bx bx-receipt'></i> Licensed</button>
          <!-- STATUS -->
          <button class="chip chip-status active" data-status="all"><i class='bx bx-slider-alt'></i> All Status</button>
          <button class="chip chip-status" data-status="pending"><i class='bx bx-time-five'></i> Pending</button>
          <button class="chip chip-status" data-status="approved"><i class='bx bx-badge-check'></i> Approved</button>
          <button class="chip chip-status" data-status="declined"><i class='bx bx-x-circle'></i> Declined</button>
        </div>
        <div class="filters-right">
          <div class="search">
            <input type="text" id="searchInput" placeholder="Search name, category, barangay...">
            <i class='bx bx-search'></i>
          </div>
        </div>
      </div>

      <!-- Grid -->
      <div class="grid" id="servicesGrid"></div>
    </div>
  </main>
</div>

<!-- Reused Modal -->
<div id="modalBg" class="modal-bg">
  <div class="modal" id="serviceModal">
    <div class="modal-head">
      <img class="thumb" id="mThumb" src="" alt="">
      <div>
        <h3 class="modal-title" id="mTitle"></h3>
        <div class="modal-sub" id="mSub"></div>
      </div>
    </div>
    <div class="modal-grid">
      <div class="modal-section">
        <h4>Provider Details</h4>
        <div id="mDetails"></div>
      </div>
      <div class="modal-section">
        <h4>Uploaded Documents</h4>
        <div class="doc-list" id="mDocs"></div>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-btn decline" id="mDecline"><i class='bx bx-x-circle'></i> Decline</button>
      <button class="modal-btn approve" id="mApprove"><i class='bx bx-badge-check'></i> Approve</button>
      <button class="modal-btn close" onclick="closeModal()"><i class='bx bx-x'></i> Close</button>
    </div>
  </div>
</div>

<script>
/* ------------ SAMPLE DATA (Admin view) ------------ */
const services = [
  {
    id: 'svc_001',
    name: 'Fade Factory Barbershop',
    type: 'fixed',                    // 'home' or 'fixed'
    tag: 'licensed',                  // 'licensed' | 'barangay-approved'
    status: 'approved',               // 'pending' | 'approved' | 'declined'
    category: 'Barber',
    barangay: 'San Isidro',
    address: 'Purok 5, San Isidro',
    openHours: 'Mon–Sun 8:00 AM – 9:00 PM',
    contact: { phone: '0917 555 2234' },
    thumb: 'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?q=80&w=1200&auto=format&fit=crop',
    docs: [
      { label:'DTI Permit', url:'https://images.unsplash.com/photo-1523966211575-eb4a01e7dd51?q=80&w=600&auto=format&fit=crop' },
      { label:'Barangay Clearance', url:'https://images.unsplash.com/photo-1584988299603-1eac9175b7d6?q=80&w=600&auto=format&fit=crop' },
    ]
  },
  {
    id: 'svc_002',
    name: 'Mario Dela Cruz',
    type: 'home',
    tag: 'barangay-approved',
    status: 'pending',
    category: 'Electrician',
    barangay: 'San Isidro',
    address: 'Covers San Isidro & nearby puroks',
    openHours: 'On-call (8:00 AM – 6:00 PM)',
    contact: { phone: '0908 777 8899' },
    thumb: 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?q=80&w=1200&auto=format&fit=crop',
    docs: [
      { label:'Valid ID', url:'https://images.unsplash.com/photo-1584433144859-1fc3ab64a957?q=80&w=600&auto=format&fit=crop' },
      { label:'Proof of Residence', url:'https://images.unsplash.com/photo-1523419409543-8a99f4e72c2d?q=80&w=600&auto=format&fit=crop' },
    ]
  },
  {
    id: 'svc_003',
    name: 'Liza’s Laundry Corner',
    type: 'fixed',
    tag: 'licensed',
    status: 'declined',
    category: 'Laundry',
    barangay: 'San Jose',
    address: 'Blk 12 Lot 9, San Jose',
    openHours: 'Mon–Sat 9:00 AM – 7:00 PM',
    contact: { phone: '0956 300 1020' },
    thumb: 'https://images.unsplash.com/photo-1581579188871-c3696f44d2eb?q=80&w=1200&auto=format&fit=crop',
    docs: [
      { label:'BIR Registration', url:'https://images.unsplash.com/photo-1611162617271-4975fd0eac4b?q=80&w=600&auto=format&fit=crop' }
    ]
  },
  {
    id: 'svc_004',
    name: 'Ana Ramos',
    type: 'home',
    tag: 'barangay-approved',
    status: 'approved',
    category: 'Tutor',
    barangay: 'San Isidro',
    address: 'Home service within the barangay',
    openHours: 'Weekdays 3:00 PM – 7:00 PM',
    contact: { phone: '0999 122 7744' },
    thumb: 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?q=80&w=1200&auto=format&fit=crop',
    docs: [
      { label:'Valid ID', url:'https://images.unsplash.com/photo-1520975940400-32e1c60bdcf0?q=80&w=600&auto=format&fit=crop' }
    ]
  }
];

/* ------------ STATE ------------ */
let currentType = 'all';     // 'all' | 'licensed' | 'barangay-approved'
let currentStatus = 'all';   // 'all' | 'pending' | 'approved' | 'declined'
let currentQuery = '';

/* ------------ RENDER ------------ */
const grid = document.getElementById('servicesGrid');

function makeBadge(content, cls) {
  const span = document.createElement('span');
  span.className = `badge ${cls}`;
  span.innerHTML = content;
  return span;
}

function cardTemplate(item){
  const card = document.createElement('div');
  card.className = 'card';
  card.dataset.id = item.id;

  const img = document.createElement('img');
  img.className = 'thumb';
  img.src = item.thumb;
  img.alt = item.name;

  const body = document.createElement('div');
  body.className = 'body';

  const title = document.createElement('h3');
  title.className = 'title';
  title.textContent = item.name;

  const meta = document.createElement('div');
  meta.className = 'meta';
  meta.innerHTML = `
    <span><i class='bx bx-user-pin'></i> ${item.category}</span>
    <span><i class='bx bx-map'></i> ${item.barangay}</span>
    ${item.type === 'home' ? "<span><i class='bx bx-home-smile'></i> Home service</span>" : "<span><i class='bx bx-store'></i> Fixed location</span>"}
  `;

  const badges = document.createElement('div');
  badges.className = 'badges';
  // tag badge
  if(item.tag === 'barangay-approved'){
    badges.appendChild(makeBadge("🏠 Barangay-Approved", 'b-approved'));
  } else {
    badges.appendChild(makeBadge("📄 Licensed Business", 'b-licensed'));
  }
  // status badge
  const sClass = item.status === 'pending' ? 's-pending' : (item.status === 'approved' ? 's-approved' : 's-declined');
  const sIcon  = item.status === 'pending' ? '⏳' : (item.status === 'approved' ? '✅' : '❌');
  badges.appendChild(makeBadge(`${sIcon} ${capitalize(item.status)}`, sClass));

  const actions = document.createElement('div');
  actions.className = 'actions';
  actions.innerHTML = `
    <button class="btn view"><i class='bx bx-show'></i> View</button>
    <button class="btn approve"><i class='bx bx-badge-check'></i> Approve</button>
    <button class="btn decline"><i class='bx bx-x-circle'></i> Decline</button>
    <button class="btn delete"><i class='bx bx-trash'></i> Delete</button>
  `;

  body.appendChild(title);
  body.appendChild(meta);
  body.appendChild(badges);

  card.appendChild(img);
  card.appendChild(body);
  card.appendChild(actions);

  return card;
}

function render(){
  grid.innerHTML = '';
  filteredData().forEach(item => {
    const card = cardTemplate(item);
    grid.appendChild(card);
  });
}

function filteredData(){
  return services.filter(s => {
    const matchesType   = currentType === 'all' ? true : (currentType === 'licensed' ? s.tag === 'licensed' : s.tag === 'barangay-approved');
    const matchesStatus = currentStatus === 'all' ? true : s.status === currentStatus;
    const text = (s.name + ' ' + s.category + ' ' + s.barangay + ' ' + s.address).toLowerCase();
    const matchesQuery  = !currentQuery || text.includes(currentQuery);
    return matchesType && matchesStatus && matchesQuery;
  });
}

/* ------------ HELPERS ------------ */
function ucfirst(str){ return str.charAt(0).toUpperCase() + str.slice(1); }
function capitalize(s){ return s.charAt(0).toUpperCase() + s.slice(1); }
function findService(id){ return services.find(s => s.id === id); }

/* ------------ FILTER EVENTS ------------ */
document.querySelectorAll('.chip-type').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    document.querySelectorAll('.chip-type').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    currentType = btn.dataset.type;
    render();
  });
});
document.querySelectorAll('.chip-status').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    document.querySelectorAll('.chip-status').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    currentStatus = btn.dataset.status;
    render();
  });
});
document.getElementById('searchInput').addEventListener('input', (e)=>{
  currentQuery = e.target.value.trim().toLowerCase();
  render();
});

/* ------------ CARD ACTIONS (DELEGATION) ------------ */
grid.addEventListener('click', (e)=>{
  const card = e.target.closest('.card');
  if(!card) return;
  const id = card.dataset.id;
  const svc = findService(id);
  if(!svc) return;

  if(e.target.closest('.view')){
    openModalWith(svc);
  } else if(e.target.closest('.approve')){
    svc.status = 'approved';
    render();
    toast('Approved '+svc.name);
  } else if(e.target.closest('.decline')){
    svc.status = 'declined';
    render();
    toast('Declined '+svc.name);
  } else if(e.target.closest('.delete')){
    const idx = services.findIndex(s=>s.id===id);
    if(idx>-1){ services.splice(idx,1); render(); toast('Deleted service'); }
  }
});

/* ------------ MODAL ------------ */
function openModalWith(svc){
  document.getElementById('mThumb').src = svc.thumb;
  document.getElementById('mTitle').innerHTML = `
    ${svc.name}
    ${svc.tag === 'barangay-approved'
      ? "<span class='badge b-approved'>🏠 Barangay-Approved</span>"
      : "<span class='badge b-licensed'>📄 Licensed Business</span>"
    }
    <span class='badge ${svc.status==='pending'?'s-pending':svc.status==='approved'?'s-approved':'s-declined'}'>
      ${svc.status==='pending'?'⏳ Pending':svc.status==='approved'?'✅ Approved':'❌ Declined'}
    </span>
  `;
  document.getElementById('mSub').textContent = `${ucfirst(svc.type)} • ${svc.category} • ${svc.barangay}`;

  document.getElementById('mDetails').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr;gap:6px;font-size:.95rem;">
      <div><strong>Address/Area:</strong> ${svc.address}</div>
      <div><strong>Open Hours:</strong> ${svc.openHours}</div>
      <div><strong>Contact:</strong> ${svc.contact?.phone || '—'}</div>
    </div>
  `;

  const docsEl = document.getElementById('mDocs');
  docsEl.innerHTML = '';
  (svc.docs||[]).forEach(d=>{
    const a = document.createElement('a');
    a.href = d.url; a.target = '_blank'; a.title = d.label;
    const img = document.createElement('img');
    img.className = 'doc'; img.src = d.url; img.alt = d.label;
    a.appendChild(img);
    docsEl.appendChild(a);
  });

  // Bind Approve/Decline inside modal
  document.getElementById('mApprove').onclick = ()=>{ svc.status='approved'; render(); closeModal(); toast('Approved '+svc.name); };
  document.getElementById('mDecline').onclick = ()=>{ svc.status='declined'; render(); closeModal(); toast('Declined '+svc.name); };

  document.getElementById('modalBg').classList.add('active');
}
function closeModal(){
  document.getElementById("modalBg").classList.remove("active");
}

/* ------------ TOAST (tiny) ------------ */
function toast(msg){
  const t = document.createElement('div');
  t.textContent = msg;
  t.style.position='fixed'; t.style.right='16px'; t.style.bottom='16px';
  t.style.background='#111'; t.style.color='#fff'; t.style.padding='10px 14px';
  t.style.borderRadius='10px'; t.style.boxShadow='0 6px 18px rgba(0,0,0,.25)';
  t.style.zIndex='3000'; t.style.fontWeight='700'; t.style.fontSize='.9rem';
  document.body.appendChild(t);
  setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .3s'; }, 1400);
  setTimeout(()=> t.remove(), 1750);
}

/* ------------ INIT ------------ */
render();
</script>
</body>
</html>
