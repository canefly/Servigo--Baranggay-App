<?php 
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Servigo · Stores & Services</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&family=Parkinsans:wght@300..700&display=swap" rel="stylesheet">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --accent:#16a34a;
  --bg:#fff;
  --border:#e5e7eb;
  --brand:#1e40af;
  --muted:#6b7280;
  --radius:14px;
  --text:#1e1e1e;
}

/* Base */
body {
  background:var(--bg);
  color:var(--text);
  font-family:"Parkinsans","Outfit",sans-serif;
  margin:0;
  padding:0;
}

/* Wrapper */
.wrapper {
  display:flex;
  flex-direction:column;
  overflow-x:hidden;
  width:100%;
}

/* Header / Hero Row */
.header-full {
  align-items:flex-start;
  display:flex;
  flex-wrap:wrap;
  gap:24px;
  justify-content:space-between;
  padding:40px 6vw 20px;
  width:100%;
}

/* Hero Left */
.hero {
  flex:1 1 600px;
}
.hero h1 {
  color:var(--brand);
  font-family:"Outfit";
  font-size:2.3rem;
  font-weight:700;
  margin-bottom:6px;
}
.hero p {
  color:var(--muted);
  font-size:1rem;
  margin-bottom:18px;
  max-width:500px;
}
.search-box {
  align-items:center;
  display:flex;
  flex-wrap:wrap;
  gap:10px;
}
.search-box input {
  border:1px solid var(--border);
  border-radius:10px;
  flex:1;
  font-size:.95rem;
  max-width:320px;
  min-width:200px;
  padding:10px 14px;
}

/* Apply Right */
.apply-section {
  background:#fff;
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 3px 10px rgba(0,0,0,.08);
  flex:0 0 360px;
  padding:24px 26px;
  text-align:center;
}
.apply-section h2 {
  align-items:center;
  color:var(--brand);
  display:flex;
  font-family:"Outfit";
  font-size:1.1rem;
  font-weight:700;
  gap:6px;
  justify-content:center;
  margin-bottom:6px;
}
.apply-section p {
  color:var(--muted);
  font-size:.9rem;
  margin-bottom:12px;
}

/* Buttons */
.btn-gradient {
  background:linear-gradient(135deg,var(--brand),var(--accent));
  border:none;
  border-radius:10px;
  color:#fff;
  font-weight:600;
  padding:10px 16px;
  transition:.2s;
}
.btn-gradient:hover {opacity:.9;}
.bx {margin-right:6px;vertical-align:-0.15em;}

/* Store Grid — Centered Alignment */
.store-grid {
  display:grid;
  gap:24px;
  justify-content:center;
  grid-template-columns:repeat(auto-fit,minmax(280px,300px));
  margin:0 auto;
  padding:20px 6vw 60px;
  width:100%;
}

/* Cards */
.card {
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  transition:box-shadow .15s ease,transform .15s ease;
}
.card:hover {
  box-shadow:0 4px 12px rgba(0,0,0,.1);
  transform:translateY(-2px);
}
.card-body h5 {
  color:var(--brand);
  font-family:"Outfit";
  font-weight:600;
}
.card-text {
  color:var(--muted);
  font-size:.95rem;
}
.tag {
  background:rgba(30,64,175,.08);
  border-radius:8px;
  color:var(--brand);
  display:inline-block;
  font-size:.8rem;
  padding:3px 9px;
}

/* Footer */
footer {
  color:var(--muted);
  font-size:.9rem;
  padding:20px;
  text-align:center;
  width:100%;
}

/* Toast */
.toast {
  background:#111;
  border-radius:10px;
  bottom:20px;
  box-shadow:0 6px 18px rgba(0,0,0,.25);
  color:#fff;
  font-weight:600;
  opacity:1;
  padding:12px 18px;
  position:fixed;
  right:20px;
  transition:opacity .3s;
  z-index:3000;
}

/* 🧩 Mobile Fix — Remove space completely */
@media (max-width:992px){
  .header-full {
    display:block;        /* force vertical stacking, no flex gap */
    padding:24px 24px 0;
    text-align:center;
  }
  .hero {
    margin-bottom:0;      /* no extra spacing */
    padding-bottom:0;
  }
  .hero p {
    margin:auto;
    margin-bottom:12px;
  }
  .apply-section {
    flex:unset;
    margin:10px auto 0;
    max-width:90%;
  }
}
</style>
</head>

<body>
<div class="wrapper">

  <!-- Hero + Apply -->
  <div class="header-full">
    <section class="hero">
      <h1>Barangay Stores & Services</h1>
      <p>Discover verified local shops and trusted home services within your barangay.</p>
      <div class="search-box">
        <input type="text" id="searchStore" placeholder="Search store or service...">
        <button class="btn-gradient" onclick="filterStores()"><i class='bx bx-search'></i>Search</button>
      </div>
    </section>

    <section class="apply-section">
      <h2><i class='bx bx-store-alt'></i> Want your store listed?</h2>
      <p>Be part of the barangay’s verified directory and reach more residents.</p>
      <button class="btn-gradient mt-2" onclick="openApply()">✨ Apply Now</button>
    </section>
  </div>

  <!-- Store Grid -->
  <div class="store-grid" id="storeGrid"></div>

  <footer>© 2025 Servigo. All rights reserved.</footer>

</div>

<!-- Apply Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" onsubmit="submitApplication(event)">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-primary"><i class='bx bx-edit-alt'></i> Store Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label"><i class='bx bx-store'></i> Store Name</label><input required class="form-control" placeholder="Maria’s Laundry Service"></div>
        <div class="mb-3"><label class="form-label"><i class='bx bx-map'></i> Address</label><input required class="form-control" placeholder="Purok 3, Barangay San Isidro"></div>
        <div class="mb-3"><label class="form-label"><i class='bx bx-time-five'></i> Open Hours</label><input required class="form-control" placeholder="Mon–Sat 8:00 AM – 6:00 PM"></div>
        <div class="mb-3"><label class="form-label"><i class='bx bx-phone'></i> Contact</label><input required type="tel" class="form-control" placeholder="0912 345 6789"></div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class='bx bx-id-card'></i> Classification</label>
            <select class="form-select" required>
              <option value="">Select classification</option>
              <option value="licensed">Licensed Business</option>
              <option value="informal">Barangay-Approved</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class='bx bx-cog'></i> Service Type</label>
            <select class="form-select" required>
              <option value="">Select type</option>
              <option value="fixed">Fixed Location</option>
              <option value="home">Home Service</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-gradient">Submit</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const stores=[
  {name:"Aling Nena’s Sari-Sari Store",desc:"Everyday essentials at affordable prices.",tag:"Retail"},
  {name:"Kuyas Barbershop",desc:"Trusted local barbers for all ages.",tag:"Services"},
  {name:"Electrician Mike",desc:"Barangay-certified electrical repairs and setup.",tag:"Home Repair"}
];

const grid=document.getElementById('storeGrid');
stores.forEach(s=>{
  const div=document.createElement('div');
  div.innerHTML=`
  <div class="card h-100">
    <div class="card-body d-flex flex-column justify-content-between">
      <div>
        <h5>${s.name}</h5>
        <p class="card-text">${s.desc}</p>
        <span class="tag">${s.tag}</span>
      </div>
      <button class="btn-gradient mt-3"><i class='bx bx-info-circle'></i>View Details</button>
    </div>
  </div>`;
  grid.appendChild(div);
});

function filterStores(){
  const term=document.getElementById('searchStore').value.toLowerCase();
  document.querySelectorAll('#storeGrid .card').forEach(c=>{
    c.parentElement.style.display=c.innerText.toLowerCase().includes(term)?'block':'none';
  });
}
function openApply(){
  new bootstrap.Modal(document.getElementById('applyModal')).show();
}
function submitApplication(e){
  e.preventDefault();
  bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
  showToast("✅ Application submitted for barangay review.");
}
function showToast(msg){
  const t=document.createElement('div');
  t.className='toast';
  t.textContent=msg;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.opacity='0'},1800);
  setTimeout(()=>t.remove(),2100);
}
</script>
</body>
</html>
