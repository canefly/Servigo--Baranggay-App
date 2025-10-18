<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Home (Residents)</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --bg:#f5f7fa; 
  --card:#ffffff; 
  --text:#222; 
  --muted:#6b7280;
  --brand:#1e40af; 
  --accent:#16a34a; 
  --border:#e5e7eb;
  --shadow:0 2px 8px rgba(0,0,0,.08); 
  --radius:16px; 
  --gap:14px; 
  --pad:14px;
}

*{box-sizing:border-box}
body{
  margin:0;
  font-family:system-ui,sans-serif;
  background:var(--bg);
  color:var(--text);
  line-height:1.5;
}
.container{max-width:1100px;margin:0 auto;padding:16px}

/* Nav tabs */
.navtabs{
  display:flex; gap:8px; justify-content:center;
  background:#f9fafb; padding:10px; border-bottom:1px solid var(--border);
  flex-wrap:wrap;
}
.tabbtn{
  all:unset; cursor:pointer; font-weight:600;
  padding:8px 14px; border-radius:10px;
  color:var(--text); border:1px solid var(--border); background:#f3f4f6;
}
.tabbtn.active{
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff; font-weight:700;
}

/* Filter card */
.card{
  background:var(--card);
  border:1px solid var(--border); 
  border-radius:var(--radius);
  padding:var(--pad); 
  box-shadow:var(--shadow);
  margin-bottom:20px;
}
h2{margin-top:0;color:var(--brand)}
.muted{color:var(--muted)}
.divider{height:1px;background:var(--border);margin:12px 0}

.controls{
  display:grid;
  grid-template-columns:1fr auto auto;
  gap:var(--gap);
  align-items:end
}
@media(max-width:780px){
  .controls{grid-template-columns:1fr}
  .controls .full{grid-column:1/-1}
}

label{font-size:14px;font-weight:600;margin-bottom:4px;display:block}
.input{
  width:100%;padding:12px;border-radius:12px;
  border:1px solid var(--border);font-size:15px
}

.catbar{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.chipbtn{
  all:unset;cursor:pointer;padding:10px 14px;
  border-radius:999px;border:1px solid var(--border);
  color:var(--brand);background:#f9fafb;font-size:14px
}
.chipbtn.active{
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;border:none
}
.ghost{
  all:unset;cursor:pointer;padding:9px 12px;
  border-radius:10px;background:#f3f4f6;
  border:1px solid var(--border);font-weight:600;color:var(--text)
}

/* News feed */
.news{
  background:#fff;
  border:1px solid var(--border);
  border-radius:12px;
  box-shadow:var(--shadow);
  padding:14px 16px;
  margin-bottom:16px;
  display:flex;
  flex-direction:column;
  gap:10px;
  word-wrap:break-word;
}
.news .meta{font-size:13px;color:var(--muted)}
.news h3{margin:0;font-size:16px;color:#111}
.news .desc{
  margin:0;color:var(--text);font-size:14px;line-height:1.4;
  overflow:hidden;
  white-space:pre-wrap;
  max-height:6.2em; /* ~4 lines */
}
.news.expanded .desc{max-height:none}
.news .see-toggle{
  all:unset;
  cursor:pointer;
  color:var(--brand);
  font-weight:600;
  font-size:14px;
  margin-top:4px;
  align-self:flex-start;
}

/* Image handling */
.image-wrapper{
  position:relative;width:100%;
  border-radius:10px;overflow:hidden;
  background:#f0f0f0;
}
.image-wrapper img{
  width:100%;
  height:auto;
  display:block;
  object-fit:contain; /* ✅ ensures no cutoff */
  max-height:500px;
}

/* Empty state + footer */
.empty{
  padding:16px;text-align:center;
  color:var(--muted);
  border:1px dashed var(--border);
  border-radius:12px
}
footer{
  color:var(--muted);
  text-align:center;
  padding:20px 12px;
  font-size:14px
}
</style>
</head>
<body>

<?php include 'INCLUDES/topbar.php'; ?>

<!-- Navtabs -->
<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn active">News</a>
  <a href="permitsPage.php" class="tabbtn">Permits</a>
  <a href="suggestion.php" class="tabbtn">Feedback</a>
  <a href="storesPage.php" class="tabbtn">Stores</a>
</nav>

<main class="container" id="app" tabindex="-1">
  <!-- Filter section -->
  <section class="card" aria-labelledby="news-title">
    <h2 id="news-title">Barangay News & Advisories</h2>
    <p class="muted">Showing updates  <strong id="brgyName"></strong>.</p>
    <div class="divider"></div>

    <div class="controls" style="margin-bottom:10px">
      <div class="full">
        <label for="q">Search</label>
        <input id="q" class="input" placeholder="e.g., vaccination, road closure, permit schedule" />
      </div>
      <div>
        <label for="from">From</label>
        <input id="from" class="input" type="date" />
      </div>
      <div>
        <label for="to">To</label>
        <input id="to" class="input" type="date" />
      </div>
    </div>

    <div class="catbar" role="tablist" aria-label="Categories">
      <button class="chipbtn active" data-cat="All" role="tab" aria-selected="true">All</button>
      <button class="chipbtn" data-cat="Advisory" role="tab">Advisory</button>
      <button class="chipbtn" data-cat="Event" role="tab">Event</button>
      <button class="chipbtn" data-cat="Emergency" role="tab">Emergency</button>
      <button class="ghost" id="apply">Apply Filters</button>
      <button class="ghost" id="clear">Clear</button>
    </div>
  </section>

  <!-- Feed -->
  <section id="newsGrid" aria-live="polite"></section>
  <div id="emptyState" class="empty" hidden>No results found for your filters.</div>
</main>

<footer>
  <small>© 2025 Servigo (Prototype)</small>
</footer>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const storage={get brgy(){return localStorage.getItem('sg_brgy')||'BARANGAY';}};
const brgyName=document.getElementById('brgyName');
const grid=document.getElementById('newsGrid');
const emptyState=document.getElementById('emptyState');
const q=document.getElementById('q');
const from=document.getElementById('from');
const to=document.getElementById('to');
const applyBtn=document.getElementById('apply');
const clearBtn=document.getElementById('clear');
const catBtns=[...document.querySelectorAll('.chipbtn[data-cat]')];

let activeCat='All';let announcements=[];

async function loadAnnouncements(){
  const savedBrgy = localStorage.getItem("sg_brgy") || "Unknown Barangay";
  document.getElementById("brgyName").textContent = savedBrgy;


  const res = await fetch(`${SUPABASE_URL}rest/v1/announcements?barangay_name=eq.${savedBrgy}&order=created_at.desc`, {
    headers: {
      apikey: SUPABASE_KEY,
      Authorization: "Bearer " + SUPABASE_KEY
    }
  });
  announcements = await res.json();
  render();
}

function currentFilters(){
  const term=q.value.trim().toLowerCase();
  const f=from.value?new Date(from.value):null;
  const t=to.value?new Date(to.value):null;
  return{term,f,t,cat:activeCat};
}
function applyFilters(items){
  const {term,f,t,cat}=currentFilters();
  return items.filter(n=>cat==='All'?true:n.category===cat)
    .filter(n=>term?(n.title.toLowerCase().includes(term)||n.description.toLowerCase().includes(term)):true)
    .filter(n=>{const d=new Date(n.created_at);if(f&&d<f)return false;if(t&&d>t)return false;return true});
}
function newsCard(item){
  const el=document.createElement('article');el.className='news';
  const desc=document.createElement('p');desc.className='desc';desc.textContent=item.description||'';
  el.innerHTML=`<div class="meta"><span class="tag ${item.category}">${item.category}</span> • ${item.barangay_name} • ${new Date(item.created_at).toLocaleDateString()}</div>
                <h3>${item.title}</h3>`;
  el.appendChild(desc);

  // See More / See Less
  if(item.description && item.description.length > 150){
    const toggle=document.createElement('button');
    toggle.className="see-toggle";
    toggle.textContent="See More";
    toggle.onclick=()=>{
      el.classList.toggle("expanded");
      toggle.textContent = el.classList.contains("expanded") ? "See Less" : "See More";
    };
    el.appendChild(toggle);
  }

  if(item.image_url){
    const wrapper=document.createElement('div');wrapper.className='image-wrapper';
    const img=document.createElement('img');img.src=item.image_url;img.alt="Announcement image";
    wrapper.appendChild(img);el.appendChild(wrapper);
  }
  return el;
}
function render(){
  grid.innerHTML='';const rows=applyFilters(announcements);
  if(!rows.length){emptyState.hidden=false;return;}emptyState.hidden=true;
  rows.forEach(n=>grid.appendChild(newsCard(n)));
}
applyBtn.addEventListener('click',render);
clearBtn.addEventListener('click',()=>{q.value='';from.value='';to.value='';activeCat='All';
  catBtns.forEach(b=>b.classList.toggle('active',b.dataset.cat==='All'));render();});
catBtns.forEach(btn=>btn.addEventListener('click',()=>{activeCat=btn.dataset.cat;
  catBtns.forEach(b=>b.classList.toggle('active',b===btn));render();}));
q.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();render();}});
loadAnnouncements();
</script>
</body>
</html>
