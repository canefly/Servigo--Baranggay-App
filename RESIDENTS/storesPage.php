<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Stores</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --bg:#f5f7fa; --card:#ffffff; --text:#222222; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
  --shadow:0 2px 8px rgba(0,0,0,.08); --radius:16px;
  --gap:14px; --pad:14px;
}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;
  background:var(--bg);color:var(--text);}
.container{max-width:1100px;margin:0 auto;padding:16px}

/* Tabs */
.navtabs{
  display:flex;gap:8px;justify-content:center;
  background:#f9fafb;padding:10px;border-bottom:1px solid var(--border);
  flex-wrap:wrap;
}
.tabbtn{
  all:unset;cursor:pointer;font-weight:600;
  padding:8px 14px;border-radius:10px;
  color:var(--text);border:1px solid var(--border);background:#f3f4f6
}
.tabbtn.active{
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;font-weight:700
}

/* Hero */
.hero{text-align:center;margin:20px 0}
.hero h1{margin:0;font-size:2rem;color:var(--brand)}
.hero p{color:var(--muted);max-width:600px;margin:8px auto}

/* Controls */
.controls{
  display:grid;grid-template-columns:1fr auto;gap:var(--gap);align-items:end;
  margin:20px 0;
}
@media(max-width:680px){.controls{grid-template-columns:1fr}}

/* Inputs */
.input{
  width:100%;padding:12px;border-radius:12px;
  background:#fff;border:1px solid var(--border);font-size:15px
}

/* Cards */
.grid{display:grid;gap:var(--gap);grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}
.card{
  background:var(--card);border:1px solid var(--border);
  border-radius:var(--radius);padding:var(--pad);box-shadow:var(--shadow);
  transition:transform .15s ease,box-shadow .15s ease;
  display:flex;flex-direction:column;justify-content:space-between
}
.card:hover{transform:translateY(-3px);box-shadow:0 4px 12px rgba(0,0,0,.12)}
.card h3{margin-top:0;color:var(--brand)}
.card p{color:var(--muted);flex-grow:1}
.tag{
  display:inline-block;padding:4px 8px;border-radius:10px;font-size:12px;
  margin-top:6px;background:rgba(30,64,175,.08);color:var(--brand)
}

/* Buttons */
.btn{
  all:unset;cursor:pointer;padding:10px 14px;border-radius:10px;
  font-weight:600;text-align:center;
  background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;
  margin-top:10px
}
.btn:hover{opacity:.9}

/* Footer */
footer{color:var(--muted);text-align:center;padding:20px 12px;font-size:14px}
</style>
</head>
<body>
<?php include 'INCLUDES/topbar.php'; ?>
<!-- Nav Tabs -->
<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn">Residents</a>
  <a href="permitsPage.php" class="tabbtn">Permits</a>
  <a href="storesPage.php" class="tabbtn active">Stores</a>
</nav>

<main class="container">
  <!-- Hero -->
  <section class="hero">
    <h1>Barangay Stores & Services</h1>
    <p>Find trusted barangay-verified shops and service providers. Support local businesses in your community.</p>
  </section>

  <!-- Search -->
  <div class="controls">
    <input class="input" id="searchStore" placeholder="Search store or service...">
    <button class="btn" onclick="filterStores()">Search</button>
  </div>

  <!-- Store Cards -->
  <div class="grid" id="storeGrid">
    <div class="card">
      <h3>Aling Nena’s Sari-Sari Store</h3>
      <p>Everyday essentials at affordable prices.</p>
      <span class="tag">Retail</span>
      <button class="btn">View Details</button>
    </div>
    <div class="card">
      <h3>Kuyas Barbershop</h3>
      <p>Trusted local barbers for all ages.</p>
      <span class="tag">Services</span>
      <button class="btn">View Details</button>
    </div>
    <div class="card">
      <h3>Electrician Mike</h3>
      <p>Barangay-verified electrician services.</p>
      <span class="tag">Home Repair</span>
      <button class="btn">View Details</button>
    </div>
  </div>
</main>

<footer>
  © 2025 Servigo. All rights reserved.
</footer>

<script>
const searchStore = document.getElementById('searchStore');
const storeCards = document.querySelectorAll('#storeGrid .card');
function filterStores(){
  const term = searchStore.value.toLowerCase();
  storeCards.forEach(c=>{
    c.style.display = c.innerText.toLowerCase().includes(term) ? 'block' : 'none';
  });
}
searchStore.addEventListener('keyup', filterStores);
</script>

</body>
</html>
