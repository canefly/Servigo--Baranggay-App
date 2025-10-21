<?php include 'Components/topbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Barangay Events</title>
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

/* Event cards */
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
.event-grid{display:grid;gap:16px}
.event{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:12px;
  box-shadow:var(--shadow);
  padding:14px 16px;
  display:flex;
  flex-direction:column;
  gap:8px;
}
.event h3{margin:0;font-size:17px;color:var(--brand)}
.event p{margin:0;font-size:14px;color:var(--text)}
.event .meta{font-size:13px;color:var(--muted)}
.event .btn{
  all:unset;cursor:pointer;text-align:center;
  background:var(--accent);color:#fff;
  padding:8px 12px;border-radius:8px;
  font-size:14px;margin-top:6px;
}
.event .btn:hover{background:#15803d}
.counter{font-size:13px;color:var(--muted);margin-left:6px}
.empty{
  padding:16px;text-align:center;
  color:var(--muted);
  border:1px dashed var(--border);
  border-radius:12px;
}
footer{
  color:var(--muted);
  text-align:center;
  padding:20px 12px;
  font-size:14px;
}
</style>
</head>
<body>



<!-- Navtabs -->
<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn">News</a>
  <a href="permitsPage.php" class="tabbtn">Permits</a>
  <a href="storesPage.php" class="tabbtn">Stores</a>
  <a href="events.php" class="tabbtn active">Events</a>
</nav>

<main class="container" id="app" tabindex="-1">
  <section class="card">
    <h2>📅 Barangay Events</h2>
    <p class="muted">Community activities and programs for <strong id="brgyName"></strong>.</p>
    <div class="divider"></div>
    <section id="eventGrid" class="event-grid" aria-live="polite"></section>
    <div id="emptyState" class="empty" hidden>No upcoming events found.</div>
  </section>
</main>

<footer>
  <small>© 2025 Servigo (Prototype)</small>
</footer>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";

const RESIDENT_ID = localStorage.getItem("sg_id");
const BARANGAY = localStorage.getItem("sg_brgy");
const RESIDENT_NAME = localStorage.getItem("sg_name") || "Resident";
document.getElementById("brgyName").textContent = BARANGAY || "Barangay";

/* Load all barangay events */
async function loadEvents() {
  const res = await fetch(`${SUPABASE_URL}/rest/v1/barangay_events?barangay_name=eq.${BARANGAY}&order=start_date.asc`, {
    headers: {
      apikey: SUPABASE_KEY,
      Authorization: `Bearer ${SUPABASE_KEY}`
    }
  });
  const events = await res.json();
  displayEvents(events);
}

/* Display events list */
function displayEvents(events) {
  const grid = document.getElementById("eventGrid");
  const empty = document.getElementById("emptyState");

  if (events.length === 0) {
    grid.innerHTML = "";
    empty.hidden = false;
    return;
  }
  empty.hidden = true;

  grid.innerHTML = events.map(event => `
    <article class="event">
      <h3>${event.title}</h3>
      <p>${event.description || ""}</p>
      <div class="meta">
        📍 <strong>${event.venue || "TBA"}</strong><br>
        🕒 ${new Date(event.start_date).toLocaleString()}${event.end_date ? ` - ${new Date(event.end_date).toLocaleString()}` : ""}
        <br>🏷️ ${event.category}
      </div>
      <button class="btn" onclick="markInterested(${event.id})">⭐ Mark as Interested</button>
    </article>
  `).join("");
}

/* Mark as Interested + Notify Admin */
async function markInterested(eventId) {
  if(!RESIDENT_ID){ alert("Please log in first."); return; }

  // 1️⃣ Save interest record
  const res = await fetch(`${SUPABASE_URL}/rest/v1/event_interest`, {
    method: "POST",
    headers: {
      apikey: SUPABASE_KEY,
      Authorization: `Bearer ${SUPABASE_KEY}`,
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      event_id: eventId,
      resident_id: RESIDENT_ID
    })
  });

  if (!res.ok) {
    alert("⚠️ Failed to mark interest. Please try again.");
    return;
  }

  alert("✅ You are marked as interested in this event!");

  // 2️⃣ Send notification to barangay admin
  try {
    await fetch(`${SUPABASE_URL}/rest/v1/notifications`, {
      method: "POST",
      headers: {
        apikey: SUPABASE_KEY,
        Authorization: `Bearer ${SUPABASE_KEY}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        barangay_name: BARANGAY,
        recipient_type: "admin",
        type: "event_interest",
        title: "Resident Interested in Event",
        message: `${RESIDENT_NAME} marked interest in "${eventId}" event.`,
        source_table: "barangay_events",
        source_id: eventId
      })
    });
    console.log("✅ Notification sent to admin.");
  } catch (err) {
    console.error("⚠️ Failed to send notification:", err);
  }
}

loadEvents();
</script>
</body>
</html>
