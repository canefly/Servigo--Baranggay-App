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
  --brand:#1e40af;--accent:#16a34a;--bg:#f7f9fb;
  --text:#1e1e1e;--muted:#6b7280;--border:#e5e7eb;
  --radius:14px;
}
body {
  background:var(--bg);
  font-family:"Parkinsans", "Outfit", sans-serif;
  color:var(--text);
  margin:0;
}
.container-custom {
  max-width:1280px;
  margin:auto;
  padding:24px 18px;
}


/* Hero */
.hero {text-align:center;margin:40px 0;}
.hero h1 {
  font-family:"Outfit";font-weight:700;
  color:var(--brand);font-size:2rem;
}
.hero p {
  color:var(--muted);max-width:580px;
  margin:8px auto;font-size:1rem;
}

/* Search */
.search-box {
  display:flex;gap:10px;justify-content:center;
  flex-wrap:wrap;margin-bottom:28px;
}
.search-box input {
  flex:1;min-width:220px;max-width:400px;
  padding:10px 14px;border-radius:10px;
  border:1px solid var(--border);font-size:.95rem;
}
.btn-gradient {
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;font-weight:600;
  border:none;border-radius:10px;
  padding:10px 16px;transition:.2s;
}
.btn-gradient:hover {opacity:.9;}
.bx {vertical-align:-0.15em;margin-right:6px;}

/* Cards */
.card {
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  transition:transform .15s ease, box-shadow .15s ease;
}
.card:hover {transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1);}
.card-body h5 {
  color:var(--brand);font-weight:600;
  font-family:"Outfit";
}
.card-text {color:var(--muted);font-size:.95rem;}
.tag {
  display:inline-block;padding:3px 9px;
  font-size:.8rem;background:rgba(30,64,175,.08);
  border-radius:8px;color:var(--brand);
}

/* Apply Section */
.apply-section {
  background:#fff;padding:32px;border-radius:var(--radius);
  border:1px solid var(--border);box-shadow:0 3px 10px rgba(0,0,0,.04);
  text-align:center;margin:60px auto 40px;max-width:700px;
}
.apply-section h2 {
  color:var(--brand);font-family:"Outfit";font-weight:700;
}
.apply-section p {color:var(--muted);}

/* Footer */
footer {
  background:#f9fafb;border-top:1px solid var(--border);
  text-align:center;color:var(--muted);
  padding:18px;font-size:.9rem;
}

/* Toast */
.toast {
  position:fixed;bottom:20px;right:20px;
  background:#111;color:#fff;
  padding:12px 18px;border-radius:10px;
  box-shadow:0 6px 18px rgba(0,0,0,.25);
  font-weight:600;opacity:1;transition:opacity .3s;
  z-index:3000;
}
</style>
</head>

<body>

<div class="container-custom">
  <section class="hero">
    <h1>Barangay Stores & Services</h1>
    <p>Discover verified local shops and trusted home services within your barangay.</p>
  </section>

  <div class="search-box">
    <input type="text" id="searchStore" placeholder="Search store or service...">
    <button class="btn-gradient" onclick="filterStores()"><i class='bx bx-search'></i>Search</button>
  </div>

  <div class="row g-3" id="storeGrid"></div>

  <section class="apply-section mt-5">
    <h2><i class='bx bx-store-alt'></i> Want your store listed?</h2>
    <p>Be part of the barangay’s verified directory and reach more residents.</p>
    <button class="btn-gradient mt-2" onclick="openApply()">✨ Apply Now</button>
  </section>
</div>

<footer>© 2025 Servigo. All rights reserved.</footer>

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
  {name:"Aling Nena’s Sari-Sari Store",desc:"Everyday essentials at affordable prices.",tag:"Retail",address:"Purok 3, Barangay San Isidro"},
  {name:"Kuyas Barbershop",desc:"Trusted local barbers for all ages.",tag:"Services",address:"Purok 4, Barangay San Isidro"},
  {name:"Electrician Mike",desc:"Barangay-certified electrical repairs and setup.",tag:"Home Repair",address:"Covers San Isidro & nearby puroks"}
];

const grid=document.getElementById('storeGrid');
stores.forEach(s=>{
  const col=document.createElement('div');
  col.className='col-md-4 col-sm-6';
  col.innerHTML=`
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
  grid.appendChild(col);
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
  t.className='toast';t.textContent=msg;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.opacity='0'},1800);
  setTimeout(()=>t.remove(),2100);
}
</script>
</body>
</html>
