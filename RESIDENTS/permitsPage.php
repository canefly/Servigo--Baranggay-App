<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Servigo · Barangay Requests</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
      --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
      --shadow:0 4px 12px rgba(0,0,0,.08); --radius:16px;
      --pending:#f59e42; --declined:#ef4444; --ready:#0ea5e9; --completed:#374151;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);}
    .container{max-width:1100px;margin:0 auto;padding:16px}
    .navtabs{display:flex;gap:8px;justify-content:center;background:#f9fafb;
      padding:10px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
    .tabbtn{all:unset;cursor:pointer;font-weight:600;padding:8px 14px;
      border-radius:10px;color:var(--text);border:1px solid var(--border);background:#f3f4f6}
    .tabbtn.active{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;font-weight:700}
    .hero{text-align:center;margin:20px 0}
    .hero h1{margin:0;font-size:2rem;color:var(--brand)}
    .hero p{color:var(--muted)}
    .grid{display:grid;gap:14px}
    .cols-3{grid-template-columns:repeat(3,1fr)}
    @media(max-width:1024px){.cols-3{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:680px){.cols-3{grid-template-columns:1fr}}

    .card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
      padding:16px;box-shadow:var(--shadow);transition:.15s;display:flex;flex-direction:column;justify-content:space-between}
    .card:hover{transform:translateY(-2px)}
    .card h3{margin:0;color:var(--brand)}
    .card p{flex-grow:1;color:var(--muted);margin:0 0 12px}
    .btn{all:unset;cursor:pointer;padding:10px 14px;border-radius:10px;
      font-weight:600;text-align:center;background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff}
    .btn:hover{opacity:.9}
    footer{color:var(--muted);text-align:center;padding:20px;font-size:14px}

    /* Modal */
    #applyNowModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;
      z-index:200;background:rgba(30,40,60,.2);backdrop-filter:blur(2px);
      align-items:center;justify-content:center;padding:10px;}
    #applyNowModal.show{display:flex}
    #applyNowModal form{max-width:480px;width:100%;background:#fff;border-radius:18px;
      box-shadow:0 6px 24px rgba(30,40,60,.15);display:flex;flex-direction:column;
      max-height:95vh;overflow:hidden;}
    .modalContentScroll{padding:20px;overflow-y:auto;flex:1;max-height:calc(95vh - 100px);}
    .modalFooterSticky{padding:15px 20px;background:#fff;border-top:1px solid var(--border);
      display:flex;flex-direction:column;gap:10px;}
    #applyNowModal label{font-weight:600;margin-top:10px;display:block}
    #applyNowModal input,#applyNowModal textarea{width:100%;padding:10px;
      border:1px solid var(--border);border-radius:10px;font-size:15px;margin-top:4px}
    .cancelBtn{background:#ef4444 !important;color:#fff}
    .error{color:#dc2626;font-size:13px;min-height:18px}

    /* Request Card Layout */
    .request-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,.05);
      padding: 16px 18px;
      margin-bottom: 14px;
      transition: .2s ease;
    }
    .request-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateY(-2px); }

    .request-name {
      font-weight: 600;
      font-size: 1.05rem;
      margin-bottom: 6px;
      color: var(--text);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .request-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 6px;
    }

    .request-type {
      font-size: .95rem;
      color: var(--brand);
      font-weight: 500;
    }

    .request-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .status-badge { 
    all: unset;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    color: #fff;
    transition: .2s;
    }
    .status-pending { background: var(--pending); }
    .status-ready { background: var(--ready); }
    .status-declined { background: var(--declined); }
    .status-completed { background: var(--completed); }

    .request-date {
      font-size: .85rem;
      color: var(--muted);
    }

    .btn-cancel {
      all: unset;
      cursor: pointer;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: .8rem;
      font-weight: 600;
      background: #ef4444;
      color: #fff;
      transition: .2s;
    }
    .btn-cancel:hover { opacity: .9; transform: scale(.97); }

    .empty{padding:16px;text-align:center;color:var(--muted);border:1px dashed var(--border);border-radius:12px;margin-top:10px}
  </style>
</head>
<body>

<?php include 'INCLUDES/topbar.php'; ?>

<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn">News</a>
  <a href="permitsPage.php" class="tabbtn active">Permits</a>
  <a href="storesPage.php" class="tabbtn">Stores</a>
</nav>

<main class="container">
  <section class="hero">
    <h1>Barangay Permits & Documents</h1>
    <p>Apply online for clearances, residency certificates, and permits. Track status without visiting the hall.</p>
  </section>

<!-- Apply cards -->
<div class="grid cols-3" id="permitGrid">
  <!-- Barangay Clearance -->
  <div class="card">
    <h3>Barangay Clearance</h3>
    <p>Certification of good moral standing.<br>
      <strong>Requirements:</strong> Valid ID, Cedula
    </p>
    <button class="btn" data-permit="Barangay Clearance">Apply Now</button>
  </div>

  <!-- Residency Certificate -->
  <div class="card">
    <h3>Residency Certificate</h3>
    <p>Proof of current residence.<br>
      <strong>Requirements:</strong> Barangay ID or Proof of Address
    </p>
    <button class="btn" data-permit="Residency">Apply Now</button>
  </div>

  <!-- Certificate of Indigency -->
  <div class="card">
    <h3>Certificate of Indigency</h3>
    <p>Issued to financially challenged residents.<br>
      <strong>Requirements:</strong> Valid ID, Proof of Income (if applicable)
    </p>
    <button class="btn" data-permit="Indigency">Apply Now</button>
  </div>

  <!-- Good Moral Certificate -->
  <div class="card">
    <h3>Certificate of Good Moral Character</h3>
    <p>Certification of good conduct and behavior.<br>
      <strong>Requirements:</strong> Valid ID, Barangay Clearance
    </p>
    <button class="btn" data-permit="Good Moral">Apply Now</button>
  </div>

  <!-- Solo Parent Certificate -->
  <div class="card">
    <h3>Certificate of Solo Parent</h3>
    <p>Recognition for single parents under R.A. 8972.<br>
      <strong>Requirements:</strong> Valid ID, Proof of Solo Parent Status
    </p>
    <button class="btn" data-permit="Solo Parent">Apply Now</button>
  </div>

  <!-- Late Birth Registration -->
  <div class="card">
    <h3>Certification for Late Birth Registration</h3>
    <p>Support for delayed PSA birth registration.<br>
      <strong>Requirements:</strong> Valid ID, Birth Record (if available)
    </p>
    <button class="btn" data-permit="Late Birth Registration">Apply Now</button>
  </div>

  <!-- Certificate of No Record -->
  <div class="card">
    <h3>Certificate of No Record</h3>
    <p>Proof that no blotter or complaint record exists.<br>
      <strong>Requirements:</strong> Valid ID
    </p>
    <button class="btn" data-permit="No Record">Apply Now</button>
  </div>

  <!-- Certificate of Employment -->
  <div class="card">
    <h3>Certificate of Employment / Residency</h3>
    <p>Proof of employment or self-employment within barangay.<br>
      <strong>Requirements:</strong> Valid ID, Employment Proof or Business Permit
    </p>
    <button class="btn" data-permit="Employment">Apply Now</button>
  </div>

  <!-- OJT Endorsement -->
  <div class="card">
    <h3>Certificate of OJT / Training Endorsement</h3>
    <p>Endorsement for students applying for internships.<br>
      <strong>Requirements:</strong> Valid ID, School Endorsement Letter
    </p>
    <button class="btn" data-permit="OJT">Apply Now</button>
  </div>

  <!-- Business Permit -->
  <div class="card">
    <h3>Business Permit</h3>
    <p>Authorization for business operations.<br>
      <strong>Requirements:</strong> DTI/SEC Registration, Lease/Ownership Papers
    </p>
    <button class="btn" data-permit="Business Permit">Apply Now</button>
  </div>
</div>


  <!-- My Requests -->
  <section style="margin-top:40px;">
    <h2 style="color:#1e40af;">My Requests</h2>
    <div id="requestsList"></div>
    <div id="noRequests" class="empty" style="display:none;">
      <i class='bx bx-folder-open' style="font-size:2rem;"></i><br>
      You have not submitted any requests yet.
    </div>
  </section>
</main>

<!-- Modal -->
<div id="applyNowModal" role="dialog" aria-modal="true">
  <form id="applyForm">
    <div class="modalContentScroll">
      <h2 style="margin-top:0;color:#1e40af">Barangay Clearance Request</h2>
      <input type="hidden" id="permitTypeInput" name="permit_type" value="Barangay Clearance">

      <label>Full Name<input name="fullname" required></label>
      <label>Email<input name="email" type="email" required></label>
      <label>Phone<input name="phone"></label>
      <label>Civil Status<input name="civil_status" required></label>
      <label>Date of Birth<input name="date_of_birth" type="date" required></label>
      <label>House & Street<input name="house_street" required></label>
      <label>City<input name="city" required></label>
      <label>Province<input name="province" required></label>
      <label>Date of Residency<input name="date_of_residency" type="date"></label>
      <label>Years of Residency<input name="years_residency" type="number"></label>
      <label>Purpose<textarea name="purpose" rows="3" required></textarea></label>
      <label>Valid ID<input name="valid_id" type="file" accept=".jpg,.jpeg,.png,.pdf" required></label>
    </div>
    <div class="modalFooterSticky">
      <button class="btn" type="submit"><span id="applyBtnText">Submit Request</span></button>
      <button type="button" onclick="closeApplyForm()" class="btn cancelBtn">Cancel</button>
      <div id="applyResult" style="text-align:center;font-size:14px"></div>
    </div>
  </form>
</div>

<footer>© 2025 Servigo</footer>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const BUCKET = "permits";

const BARANGAY = localStorage.getItem("sg_brgy");
const RESIDENT_ID = localStorage.getItem("sg_id");
const EMAIL = localStorage.getItem("sg_email");

function lockBodyScroll(lock){document.body.style.overflow = lock?'hidden':''}

// Modal open/close
document.querySelectorAll('#permitGrid .btn[data-permit]').forEach(btn=>{
  btn.onclick=()=>{
    document.getElementById('applyNowModal').classList.add('show');
    lockBodyScroll(true);
  };
});
function closeApplyForm(){
  document.getElementById('applyNowModal').classList.remove('show');
  document.getElementById('applyForm').reset();
  document.getElementById('applyResult').textContent='';
  lockBodyScroll(false);
}
window.addEventListener('keydown',e=>{
  if(e.key==='Escape'&&document.getElementById('applyNowModal').classList.contains('show'))closeApplyForm();
});

// Upload file
async function uploadFile(file,folder=""){
  const safeName=file.name.replace(/[^a-zA-Z0-9._-]/g,"_");
  const path=`${folder?folder+"/":""}${Date.now()}_${safeName}`;
  const res=await fetch(`${SUPABASE_URL}/storage/v1/object/${BUCKET}/${path}`,{
    method:"POST",
    headers:{ apikey:SUPABASE_KEY, Authorization:`Bearer ${SUPABASE_KEY}`, "x-upsert":"true", "Content-Type":file.type },
    body:file
  });
  if(!res.ok){ throw new Error(await res.text()); }
  return { url:`${SUPABASE_URL}/storage/v1/object/public/${BUCKET}/${path}`, path };
}

// Submit
document.getElementById('applyForm').addEventListener('submit',async e=>{
  e.preventDefault();
  const form=e.target;
  const fileInput=form.querySelector('input[name="valid_id"]');
  let uploadedFileUrl=null;
  if(fileInput&&fileInput.files[0]){
    const {url}=await uploadFile(fileInput.files[0],"valid_ids");
    uploadedFileUrl=url;
  }
  const data={
    resident_id: RESIDENT_ID,
    fullname: form.fullname.value,
    civil_status: form.civil_status.value,
    date_of_birth: form.date_of_birth.value,
    house_street: form.house_street.value,
    city: form.city.value,
    province: form.province.value,
    date_of_residency: form.date_of_residency?.value,
    years_residency: form.years_residency?.value,
    purpose: form.purpose.value,
    valid_id_url: uploadedFileUrl,
    email: EMAIL || form.email.value,
    phone: form.phone.value,
    barangay_name: BARANGAY,
    status: "Pending"
  };
  const res=await fetch(`${SUPABASE_URL}/rest/v1/barangay_clearance_requests`,{
    method:"POST",
    headers:{ apikey:SUPABASE_KEY, Authorization:`Bearer ${SUPABASE_KEY}`, "Content-Type":"application/json", Prefer:"return=representation" },
    body:JSON.stringify(data)
  });
  const out=await res.json();
  if(res.ok){
    document.getElementById('applyResult').innerHTML="<span style='color:#16a34a'>✔️ Request submitted!</span>";
    loadMyRequests();
    setTimeout(closeApplyForm,2000);
  }else{
    document.getElementById('applyResult').innerHTML="<span style='color:#dc2626'>❌ "+(out.message||JSON.stringify(out))+"</span>";
  }
});

// Fetch requests
async function loadMyRequests(){
  const res=await fetch(`${SUPABASE_URL}/rest/v1/barangay_clearance_requests?resident_id=eq.${RESIDENT_ID}&order=created_at.desc`,{
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const requests=await res.json();
  const list=document.getElementById("requestsList");
  const noReq=document.getElementById("noRequests");
  list.innerHTML="";
  if(!requests.length){noReq.style.display="block";return;}
  noReq.style.display="none";
  requests.forEach(r=>{
    const div=document.createElement("div");
    div.className="request-card";
    const status=r.status||"Pending";
    const date=new Date(r.created_at).toLocaleDateString();

    div.innerHTML=`
      <div class="request-name"><i class="bx bx-user"></i> ${r.fullname || "Resident"}</div>
      <div class="request-row">
        <span class="request-type">${r.permit_type || "Barangay Clearance"}</span>
        <div class="request-actions">
          <span class="status-badge status-${status.toLowerCase()}">${status}</span>
          ${status==="Pending"
            ? `<button class="btn-cancel" onclick="cancelRequest(${r.id})"><i class='bx bx-x'></i> Cancel</button>`
            : ""}
        </div>
      </div>
      <div class="request-date"><i class='bx bx-calendar'></i> ${date}</div>
    `;
    list.appendChild(div);
  });
}

// Cancel request
async function cancelRequest(id){
  if(!confirm("Are you sure you want to cancel this request?")) return;
  await fetch(`${SUPABASE_URL}/rest/v1/barangay_clearance_requests?id=eq.${id}`,{
    method:"PATCH",
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY,"Content-Type":"application/json"},
    body:JSON.stringify({status:"Cancelled"})
  });
  loadMyRequests();
}

loadMyRequests();
</script>
</body>
</html>
