<?php 
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Stores</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root{
  --bg:#f5f7fa;--card:#fff;--text:#222;--muted:#6b7280;
  --brand:#1e40af;--accent:#16a34a;--border:#e5e7eb;
  --shadow:0 2px 8px rgba(0,0,0,.08);--radius:16px;
}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,sans-serif;
  background:var(--bg);color:var(--text);}
.container{max-width:1100px;margin:0 auto;padding:16px}

/* Nav Tabs */
.navtabs{display:flex;gap:8px;justify-content:center;
  background:#f9fafb;padding:10px;border-bottom:1px solid var(--border);
  flex-wrap:wrap;}
.tabbtn{all:unset;cursor:pointer;font-weight:600;
  padding:8px 14px;border-radius:10px;color:var(--text);
  border:1px solid var(--border);background:#f3f4f6}
.tabbtn.active{background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;font-weight:700}

/* Hero */
.hero{text-align:center;margin:20px 0}
.hero h1{margin:0;font-size:2rem;color:var(--brand)}
.hero p{color:var(--muted);max-width:600px;margin:8px auto}

/* Search */
.controls{display:grid;grid-template-columns:1fr auto;gap:14px;align-items:end;margin:20px 0;}
@media(max-width:680px){.controls{grid-template-columns:1fr}}
.input{width:100%;padding:12px;border-radius:12px;background:#fff;
  border:1px solid var(--border);font-size:15px}

/* Cards */
.grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:14px;box-shadow:var(--shadow);transition:.15s ease;display:flex;flex-direction:column;justify-content:space-between}
.card:hover{transform:translateY(-3px);box-shadow:0 4px 12px rgba(0,0,0,.12)}
.card h3{margin-top:0;color:var(--brand)}
.card p{color:var(--muted);flex-grow:1}
.tag{display:inline-block;padding:4px 8px;border-radius:10px;font-size:12px;
  margin-top:6px;background:rgba(30,64,175,.08);color:var(--brand)}
.btn{all:unset;cursor:pointer;padding:10px 14px;border-radius:10px;font-weight:600;
  text-align:center;background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;margin-top:10px}
.btn:hover{opacity:.9}

/* Apply Section */
.apply-section{text-align:center;margin:40px auto;padding:20px;background:#fff;
  border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);max-width:600px}
.apply-section h2{margin:0 0 6px 0;color:var(--brand)}
.apply-section p{color:var(--muted);margin:0 0 14px 0}

/* Detail Viewer Modal */
.viewer-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);
  z-index:2100;align-items:center;justify-content:center;padding:20px;}
.viewer-bg.active{display:flex;}
.viewer{background:#fff;border-radius:var(--radius);box-shadow:0 10px 28px rgba(0,0,0,.4);
  max-width:700px;width:100%;overflow:hidden;animation:fadeIn .3s ease;}
@keyframes fadeIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
.slide-box{position:relative;width:100%;height:360px;overflow:hidden;}
.slide-box img{width:100%;height:360px;object-fit:cover;display:none;}
.slide-box img.active{display:block;animation:slideIn .4s ease;}
@keyframes slideIn{from{transform:translateX(50%);opacity:.4}to{transform:translateX(0);opacity:1}}
.arrow{position:absolute;top:50%;transform:translateY(-50%);
  background:rgba(0,0,0,.4);color:#fff;border:none;width:36px;height:36px;
  border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.3rem;}
.arrow:hover{background:rgba(0,0,0,.6)}
.arrow.left{left:10px}.arrow.right{right:10px}
.viewer-content{padding:16px;}
.viewer-content h2{margin:0 0 4px 0;color:var(--brand);}
.viewer-content p{color:var(--muted);margin:0 0 8px 0}
.viewer-content .info{font-size:.9rem;line-height:1.6;}
.viewer-content .info span{display:block;margin:2px 0;}
.close-btn{position:absolute;top:12px;right:12px;background:#fff;
  border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;
  font-size:1.4rem;box-shadow:0 2px 8px rgba(0,0,0,.25);}
.close-btn:hover{background:#f3f4f6}

/* Apply Modal */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);
  z-index:2000;align-items:center;justify-content:center;padding:20px;}
.modal-bg.active{display:flex;}
.modal{background:#fff;border-radius:var(--radius);box-shadow:0 8px 28px rgba(0,0,0,.25);
  max-width:520px;width:100%;padding:20px;display:flex;flex-direction:column;gap:12px;}
.modal h3{margin:0;color:var(--brand)}
.modal label{font-weight:600;margin-top:6px;display:block;font-size:.9rem;}
.modal input,.modal select{width:100%;padding:10px;border:1px solid var(--border);
  border-radius:8px;font-size:.95rem;margin-top:4px;}
.modal small{color:var(--muted);font-size:.85rem;}
.modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:10px;}
.modal-btn{all:unset;cursor:pointer;padding:9px 14px;border-radius:10px;font-weight:700;}
.modal-btn.cancel{background:#f3f4f6;color:#111;border:1px solid var(--border);}
.modal-btn.submit{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;}

/* Toast */
.toast{position:fixed;bottom:20px;right:20px;background:#111;color:#fff;
  padding:10px 16px;border-radius:8px;font-weight:600;z-index:3000;
  box-shadow:0 6px 18px rgba(0,0,0,.25);opacity:1;transition:opacity .3s;}
footer{color:var(--muted);text-align:center;padding:20px 12px;font-size:14px}
</style>
</head>
<body>

<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn">News</a>
  <a href="permitsPage.php" class="tabbtn ">Permits</a>
  <a href="storesPage.php" class="tabbtn active">Stores</a>
  <a href="events.php" class="tabbtn">Events</a>
</nav>

<main class="container">
  <section class="hero">
    <h1>Barangay Stores & Services</h1>
    <p>Find trusted barangay-verified shops and service providers. Support local businesses in your community.</p>
  </section>

  <div class="controls">
    <input class="input" id="searchStore" placeholder="Search store or service...">
    <button class="btn" onclick="filterStores()">Search</button>
  </div>

  <div class="grid" id="storeGrid"></div>

  <section class="apply-section">
    <h2>Want your shop or service listed?</h2>
    <p>Join the verified barangay directory and reach more residents.</p>
    <button class="btn apply-btn" onclick="openApply()">✨ Apply Now!</button>
  </section>
</main>
<footer>© 2025 Servigo. All rights reserved.</footer>

<!-- Detail Viewer -->
<div class="viewer-bg" id="viewerBg">
  <div class="viewer" id="viewer">
    <button class="close-btn" onclick="closeViewer()"><i class='bx bx-x'></i></button>
    <div class="slide-box" id="slideBox">
      <button class="arrow left" onclick="prevSlide()"><i class='bx bx-chevron-left'></i></button>
      <button class="arrow right" onclick="nextSlide()"><i class='bx bx-chevron-right'></i></button>
    </div>
    <div class="viewer-content" id="viewerContent"></div>
  </div>
</div>

<!-- Apply Modal -->
<div class="modal-bg" id="applyModal">
  <form class="modal" onsubmit="submitApplication(event)">
    <h3>Apply to List Your Store or Service</h3>
    <label><i class='bx bx-store'></i> Store / Service Name</label>
    <input required placeholder="e.g. Maria’s Laundry Service">
    <label><i class='bx bx-map'></i> Address / Area of Service</label>
    <input required placeholder="e.g. Purok 3, Barangay San Isidro">
    <label><i class='bx bx-time-five'></i> Open Hours</label>
    <input required placeholder="e.g. Mon–Sat 8:00 AM – 6:00 PM">
    <label><i class='bx bx-phone'></i> Contact Number</label>
    <input required type="tel" placeholder="e.g. 0912 345 6789">
    <label><i class='bx bx-file'></i> Upload Documents</label>
    <input type="file" multiple>
    <label><i class='bx bx-id-card'></i> Classification</label>
    <select required>
      <option value="">Select classification</option>
      <option value="licensed">📄 Licensed Business</option>
      <option value="informal">🏠 Informal / Barangay-Approved</option>
    </select>
    <label><i class='bx bx-cog'></i> Type of Service</label>
    <select required>
      <option value="">Select type</option>
      <option value="fixed">📍 Fixed Location</option>
      <option value="home">🏠 Home Service</option>
    </select>
    <small>Barangay staff will verify your details before publishing your listing.</small>
    <div class="modal-actions">
      <button type="button" class="modal-btn cancel" onclick="closeApply()">Cancel</button>
      <button type="submit" class="modal-btn submit">Submit</button>
    </div>
  </form>
</div>

<script>
const stores=[
  {
    name:"Aling Nena’s Sari-Sari Store",
    desc:"Everyday essentials at affordable prices.",
    tag:"Retail",
    images:[
      "https://images.unsplash.com/photo-1581579188871-c3696f44d2eb?auto=format&w=900",
      "https://images.unsplash.com/photo-1607083203670-006c53b2b81a?auto=format&w=900",
      "https://images.unsplash.com/photo-1610465299996-9cf9a31bcb7a?auto=format&w=900"
    ],
    address:"Purok 3, Barangay San Isidro",
    hours:"6 AM – 9 PM",
    contact:"0912 222 3344",
    verified:true
  },
  {
    name:"Kuyas Barbershop",
    desc:"Trusted local barbers for all ages.",
    tag:"Services",
    images:[
      "https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&w=900",
      "https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&w=900",
      "https://images.unsplash.com/photo-1505691723518-36a3f2f81537?auto=format&w=900"
    ],
    address:"Purok 4, Barangay San Isidro",
    hours:"8 AM – 8 PM",
    contact:"0912 555 1111",
    verified:true
  },
  {
    name:"Electrician Mike",
    desc:"Barangay-verified electrician services.",
    tag:"Home Repair",
    images:[
      "https://images.unsplash.com/photo-1581091215367-59ab6f64785c?auto=format&w=900",
      "https://images.unsplash.com/photo-1581091215573-9a363cafb06b?auto=format&w=900",
      "https://images.unsplash.com/photo-1581093588401-58e90a7ab226?auto=format&w=900"
    ],
    address:"Covers San Isidro & nearby puroks",
    hours:"On-call 8 AM – 6 PM",
    contact:"0917 333 7788",
    verified:true
  }
];

/* Render cards */
const grid=document.getElementById('storeGrid');
stores.forEach((s,i)=>{
  const c=document.createElement('div');
  c.className='card';
  c.innerHTML=`
    <h3>${s.name}</h3>
    <p>${s.desc}</p>
    <span class="tag">${s.tag}</span>
    <button class="btn" onclick="openViewer(${i})">View Details</button>
  `;
  grid.appendChild(c);
});

/* Search */
const searchStore=document.getElementById('searchStore');
function filterStores(){
  const term=searchStore.value.toLowerCase();
  document.querySelectorAll('#storeGrid .card').forEach(c=>{
    c.style.display=c.innerText.toLowerCase().includes(term)?'block':'none';
  });
}
searchStore.addEventListener('keyup',filterStores);

/* Viewer Logic */
let currentIndex=0,slideIndex=0,slideTimer;
function openViewer(i){
  currentIndex=i;
  const s=stores[i];
  const box=document.getElementById('slideBox');
  box.innerHTML='';
  s.images.forEach((img,j)=>{
    const im=document.createElement('img');
    im.src=img; if(j===0) im.classList.add('active');
    box.appendChild(im);
  });
  document.getElementById('viewerContent').innerHTML=`
    <h2>${s.name} ${s.verified?"<span style='font-size:.8rem;background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;padding:4px 8px;border-radius:8px;margin-left:6px'>Verified</span>":""}</h2>
    <p>${s.desc}</p>
    <div class="info">
      <span>🏷 ${s.tag}</span>
      <span>📍 ${s.address}</span>
      <span>⏰ ${s.hours}</span>
      <span>☎️ ${s.contact}</span>
    </div>
  `;
  slideIndex=0;
  document.getElementById('viewerBg').classList.add('active');
  startAutoSlide();
}
function closeViewer(){
  document.getElementById('viewerBg').classList.remove('active');
  stopAutoSlide();
}
function showSlide(n){
  const imgs=document.querySelectorAll('#slideBox img');
  imgs.forEach(im=>im.classList.remove('active'));
  imgs[n].classList.add('active');
}
function nextSlide(){
  const imgs=document.querySelectorAll('#slideBox img');
  slideIndex=(slideIndex+1)%imgs.length;
  showSlide(slideIndex);
}
function prevSlide(){
  const imgs=document.querySelectorAll('#slideBox img');
  slideIndex=(slideIndex-1+imgs.length)%imgs.length;
  showSlide(slideIndex);
}
function startAutoSlide(){
  slideTimer=setInterval(()=>{nextSlide()},3000);
}
function stopAutoSlide(){
  clearInterval(slideTimer);
}

/* Apply Modal */
function openApply(){document.getElementById('applyModal').classList.add('active');}
function closeApply(){document.getElementById('applyModal').classList.remove('active');}
function submitApplication(e){
  e.preventDefault();closeApply();showToast("Your application has been sent for barangay review.");
}

/* Toast */
function showToast(msg){
  const t=document.createElement('div');
  t.className='toast';t.textContent=msg;document.body.appendChild(t);
  setTimeout(()=>{t.style.opacity='0'},1800);
  setTimeout(()=>t.remove(),2100);
}
</script>
</body>
</html>
