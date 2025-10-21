<?php include 'Components/barangaySidebar.php'; ?> 
<?php include 'Components/barangayTopbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Resident Requests Dashboard</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff;
      --text:#222; --muted:#6b7280; --border:#e5e7eb;
      --brand:#047857; --accent:#10b981;
      --pending:#f59e42; --declined:#ef4444; --ready:#0ea5e9; --completed:#16a34a;
      --radius:14px; --shadow:0 2px 8px rgba(0,0,0,.08);
      --sidebar-width:240px;
    }
    *{box-sizing:border-box;}
    body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);}
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
    .search-box{margin-left:auto;position:relative;}
    .search-box input{
      padding:8px 150px 8px 12px;border:1px solid var(--border);
      border-radius:10px;font-size:.95rem;
    }
    .search-box i{
      position:absolute;right:10px;top:50%;transform:translateY(-50%);
      font-size:18px;color:var(--muted);
    }

    /* Tabs */
    .filter-tabs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;  overflow-x: auto;
  scrollbar-width: thin;
}
    .filter-tab{
      all:unset;cursor:pointer;padding:8px 18px;border-radius:999px;
      font-weight:600;font-size:.95rem;
      border:1px solid var(--border);background:#f3f4f6;color:var(--brand);
      transition:.2s; white-space: nowrap;
    }
    .filter-tab.active{
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff;border:none;box-shadow:0 2px 6px rgba(0,0,0,.1);
    }

    /* Requests grid */
    .requests-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;}
    .request-card{
      background:var(--card);border:1px solid var(--border);
      border-radius:var(--radius);box-shadow:var(--shadow);
      padding:16px;display:flex;flex-direction:column;gap:10px;
      transition:.2s;
    }
    .request-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);}
    .request-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;}
    .resident-name{font-weight:700;font-size:1.05rem;color:var(--text);}
    .request-type{font-size:.85rem;font-weight:600;border-radius:12px;padding:4px 10px;background:#f3f4f6;color:var(--brand);}
    .date-status-row{display:flex;justify-content:space-between;align-items:center;}
    .submission-date{color:var(--muted);font-size:.85rem;}
    .status-badge{padding:4px 12px;border-radius:999px;font-size:.8rem;font-weight:600;color:#fff;}
    .status-pending{background:var(--pending);}
    .status-declined{background:var(--declined);}
    .status-ready{background:var(--ready);}
    .status-completed{background:var(--completed);}

    /* Actions */
    .request-actions { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }
    .print-btn, .decline-btn, .view-btn, .complete-btn {
      all:unset; cursor:pointer; display:flex; align-items:center; justify-content:center;
      gap:6px; border-radius:12px; font-weight:700; font-size:.9rem; padding:7.5px 18px;
      box-shadow:0 2px 8px rgba(30,64,175,.07); transition:.15s;
    }
    .print-btn { background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff; }
    .decline-btn { background:var(--declined);color:#fff; }
    .view-btn { background:#f3f4f6;border:1px solid var(--border);color:var(--brand); }
    .complete-btn { background:var(--completed);color:#fff; }
    .print-btn:active, .decline-btn:active, .view-btn:active, .complete-btn:active { opacity:.92; }

    .no-requests{text-align:center;color:var(--muted);font-size:1rem;margin-top:30px;}

    /* Modals */
    .modal-bg{display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:2000;background:rgba(0,0,0,.35);align-items:center;justify-content:center;}
    .modal-bg.active{display:flex;}
    .modal{background:#fff;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.2);max-width:400px;width:95%;padding:20px;display:flex;flex-direction:column;gap:14px;}
    .modal-actions{display:flex;gap:10px;justify-content:flex-end;}
    .modal-btn{all:unset;cursor:pointer;padding:8px 16px;border-radius:8px;font-weight:600;font-size:.9rem;}
    .modal-btn.decline{background:var(--declined);color:#fff;}
    .modal-btn.cancel{background:#f3f4f6;color:var(--text);}
    .modal-error{color:var(--declined);font-size:.85rem;}

    /* View Modal */
    .view-modal{background:#fff;border-radius:var(--radius);box-shadow:0 2px 12px rgba(0,0,0,.2);max-width:600px;width:95%;padding:20px;display:flex;flex-direction:column;gap:12px;max-height:90vh;overflow-y:auto;}
    .view-row{display:flex;flex-wrap:wrap;gap:6px;font-size:.95rem;}
    .view-label{font-weight:600;min-width:140px;color:var(--muted);}
    .view-value{flex:1;}
    .view-modal img{max-width:100%;border:1px solid var(--border);border-radius:10px;margin-top:4px;}

    /* Small screens */
    @media(max-width:768px){
      .dashboard-header{flex-direction:column;align-items:flex-start;}
      .dashboard-title{font-size:1.2rem;}
      .search-box{width:100%;}
      .search-box input{width:100%;}
      .print-btn,.decline-btn,.view-btn,.complete-btn{flex:1;}
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
          <div class="dashboard-title">Resident Requests Dashboard</div>
        </div>
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Search requests...">
          <i class='bx bx-search'></i>
        </div>
      </div>

    <nav class="filter-tabs">
  <button class="filter-tab active" data-filter="all">All</button>
  <button class="filter-tab" data-filter="completed">Completed (0)</button>
  <button class="filter-tab" data-filter="declined">Declined (0)</button>

  <!-- Specific document filters -->
  <button class="filter-tab" data-filter="Barangay Clearance">Barangay Clearance</button>
  <button class="filter-tab" data-filter="Indigency">Indigency</button>
  <button class="filter-tab" data-filter="Residency">Residency</button>
  <button class="filter-tab" data-filter="Good Moral">Good Moral</button>
  <button class="filter-tab" data-filter="Solo Parent">Solo Parent</button>
  <button class="filter-tab" data-filter="Late Birth Registration">Late Birth Registration</button>
  <button class="filter-tab" data-filter="No Record">No Record</button>
  <button class="filter-tab" data-filter="Employment">Employment</button>
  <button class="filter-tab" data-filter="OJT">OJT</button>
  <button class="filter-tab" data-filter="Business Permit">Business Permit</button>
</nav>


      <section id="requestsList" class="requests-list"></section>
      <div id="noRequests" class="no-requests" style="display:none;">
        <i class='bx bx-user-x' style="font-size:2rem;"></i><br>
        No requests found for this category.
      </div>
    </div>
  </main>
</div>

<!-- Decline Modal -->
<div id="modalBg" class="modal-bg">
  <form class="modal" id="declineModal">
    <h3 style="margin:0;color:var(--brand)">Decline Request</h3>
    <label for="declineReason">Reason for Decline <span style="color:var(--declined)">*</span></label>
    <textarea id="declineReason" required placeholder="State the reason..."></textarea>
    <div id="declineError" class="modal-error"></div>
    <div class="modal-actions">
      <button class="modal-btn decline" id="declineConfirmBtn">Decline</button>
      <button class="modal-btn cancel" type="button" onclick="closeModal()">Cancel</button>
    </div>
  </form>
</div>

<!-- View Modal -->
<div id="viewModalBg" class="modal-bg">
  <div class="view-modal" id="viewModal">
    <h3>Resident Request Details</h3>
    <div id="viewContent"></div>
    <div class="modal-actions" style="margin-top:10px;">
      <button class="modal-btn cancel" type="button" onclick="closeViewModal()">Close</button>
    </div>
  </div>
</div>

<script>
const BARANGAY_INFO = {
  name: "Barangay Apolonio Samson",
  address: "Ap. Samson Road, Quezon City, Metro Manila, Philippines",
  captain: "Hon. LeBron James",
  logo: "M.png",
  bagongPilipinasLogo: "B.png"
};

const REQUESTS = [
  { id: 1, fullname:"Jeremy Lin", civil_status:"Single", date_of_birth:"1990-05-12", house_street:"12 Sampaguita St.", city:"Quezon City", province:"NCR", date_of_residency:"2020-01-01", years_residency:5, purpose:"Scholarship requirement", valid_id_url:"https://via.placeholder.com/350x200.png?text=Valid+ID", email:"jeremy@example.com", phone:"09170000000", barangay_name:"Apolonio Samson", type:"Indigency", date:"2025-04-09", status:"Pending", reason:"" },
  { id: 2, fullname:"Efren Reyes", civil_status:"Married", date_of_birth:"1985-01-05", house_street:"45 Sampaguita St.", city:"Quezon City", province:"NCR", date_of_residency:"2015-03-10", years_residency:10, purpose:"Proof of residency for employment", valid_id_url:"https://via.placeholder.com/350x200.png?text=Valid+ID", email:"efren@email.com", phone:"09171234567", barangay_name:"Apolonio Samson", type:"Residency", date:"2025-04-08", status:"Pending", reason:"" }
];

let currentFilter="all";
let searchQuery="";

const statusClass = s => ({
  Pending:"status-badge status-pending",
  Declined:"status-badge status-declined",
  Ready:"status-badge status-ready",
  Completed:"status-badge status-completed"
}[s]||"status-badge");

function formatDate(d){return (new Date(d)).toLocaleDateString('en-US',{month:"short",day:"numeric",year:"numeric"});}

function renderRequests(filter){
  currentFilter=filter||currentFilter;
  const list=document.getElementById("requestsList");
  list.innerHTML="";
  let filtered;

  if(currentFilter==="all"){
    filtered=REQUESTS.filter(r=>r.status!=="Completed" && r.status!=="Declined");
  } else if(currentFilter==="completed"){
    filtered=REQUESTS.filter(r=>r.status==="Completed");
  } else if(currentFilter==="declined"){
    filtered=REQUESTS.filter(r=>r.status==="Declined");
  } else {
    filtered=REQUESTS.filter(r=>r.type===currentFilter && r.status!=="Completed" && r.status!=="Declined");
  }

  // Apply search
  if(searchQuery.trim()){
    let q=searchQuery.toLowerCase();
    filtered=filtered.filter(r=>
      r.fullname.toLowerCase().includes(q) ||
      r.type.toLowerCase().includes(q) ||
      r.status.toLowerCase().includes(q) ||
      r.city.toLowerCase().includes(q) ||
      r.province.toLowerCase().includes(q) ||
      r.barangay_name.toLowerCase().includes(q)
    );
  }

  document.getElementById("noRequests").style.display=filtered.length?"none":"block";

  filtered.forEach(r=>{
    let card=document.createElement("div");
    card.className="request-card";
    card.innerHTML=`
      <div class="request-header">
        <span class="resident-name"><i class='bx bx-user'></i> ${r.fullname}</span>
        <span class="request-type">${r.type}</span>
      </div>
      <div class="date-status-row">
        <span class="submission-date"><i class='bx bx-calendar'></i> ${formatDate(r.date)}</span>
        <span class="${statusClass(r.status)}">${r.status}</span>
      </div>
      <div class="request-actions">
        ${r.status==="Pending" ? `
          <button class="print-btn" onclick="printRequestDoc(${r.id})"><i class='bx bx-printer'></i> Print</button>
          <button class="decline-btn" onclick="openDeclineModal(${r.id})"><i class='bx bx-x'></i> Decline</button>
          <button class="view-btn" onclick="openViewModal(${r.id})"><i class='bx bx-show'></i> View</button>
        ` : r.status==="Ready" ? `
          <button class="complete-btn" onclick="markCompleted(${r.id})"><i class='bx bx-check'></i> Mark Completed</button>
          <button class="view-btn" onclick="openViewModal(${r.id})"><i class='bx bx-show'></i> View</button>
        ` : `
          <button class="view-btn" onclick="openViewModal(${r.id})"><i class='bx bx-show'></i> View</button>
        `}
      </div>
    `;
    list.appendChild(card);
  });

  updateTabCounts();
}

function updateTabCounts(){
  document.querySelector('[data-filter="completed"]').textContent = `Completed (${REQUESTS.filter(r=>r.status==="Completed").length})`;
  document.querySelector('[data-filter="declined"]').textContent = `Declined (${REQUESTS.filter(r=>r.status==="Declined").length})`;
}

document.querySelectorAll(".filter-tab").forEach(btn=>{
  btn.onclick=()=>{
    document.querySelectorAll(".filter-tab").forEach(b=>b.classList.remove("active"));
    btn.classList.add("active");
    renderRequests(btn.dataset.filter);
  };
});

// Live search
document.getElementById("searchInput").addEventListener("input", e=>{
  searchQuery=e.target.value;
  renderRequests();
});

/* Decline Modal */
let declineReqId=null;
function openDeclineModal(id){declineReqId=id;document.getElementById("modalBg").classList.add("active");}
function closeModal(){document.getElementById("modalBg").classList.remove("active");declineReqId=null;}
document.getElementById("declineModal").onsubmit=function(e){
  e.preventDefault();
  let reason=document.getElementById("declineReason").value.trim();
  if(!reason){document.getElementById("declineError").textContent="Reason is required.";return;}
  let req=REQUESTS.find(r=>r.id===declineReqId);
  if(req){req.status="Declined";req.reason=reason;}
  closeModal();
  renderRequests(currentFilter);
};

/* === Print Button Logic (right-aligned signature block) === */
function printRequestDoc(id){
  let req = REQUESTS.find(r=>r.id===id);
  if(!req) return;
  if(req.status!=="Declined") req.status = "Ready";
  renderRequests(currentFilter);

  let docType = ({
  "Business Permit": "BUSINESS PERMIT",
  "Indigency": "CERTIFICATE OF INDIGENCY",
  "Residency": "CERTIFICATE OF RESIDENCY",
  "Barangay Clearance": "BARANGAY CLEARANCE",
  "Good Moral": "CERTIFICATE OF GOOD MORAL CHARACTER",
  "Solo Parent": "CERTIFICATE OF SOLO PARENT",
  "Late Birth Registration": "CERTIFICATION FOR LATE REGISTRATION OF BIRTH",
  "No Record": "CERTIFICATION OF NO RECORD",
  "Employment": "CERTIFICATE OF EMPLOYMENT / RESIDENCY",
  "OJT": "CERTIFICATE OF OJT / TRAINING ENDORSEMENT"
})[req.type] || req.type;

let today = new Date();
let dateStr = today.toLocaleDateString('en-US', { month: "long", day: "numeric", year: "numeric" });

let letterBody = {
  "Business Permit": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a registered resident of <b>${BARANGAY_INFO.name}</b>
      and has complied with all barangay requirements for business operations within the locality.
      This permit is issued to legalize and recognize their business, subject to existing rules and regulations.
    </p>
  `,
  "Indigency": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b>, of legal age and a Filipino citizen,
      is an indigent resident of <b>${BARANGAY_INFO.name}</b>, Quezon City.
      This certification is issued to support their application for financial, medical, or legal assistance.
    </p>
  `,
  "Residency": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a bonafide resident of <b>${BARANGAY_INFO.name}</b>
      and has continuously lived in the community for not less than six (6) months.
      This certificate is issued for whatever legal purpose it may serve the bearer.
    </p>
  `,
  "Barangay Clearance": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a resident of <b>${BARANGAY_INFO.name}</b>
      and is known to be of good moral character with no derogatory record within this barangay.
      This clearance is issued upon request for employment, business, or any legal purpose.
    </p>
  `,
  "Good Moral": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> has been a law-abiding and morally upright resident of
      <b>${BARANGAY_INFO.name}</b>. During their stay in this community, they have not been involved in any
      unlawful or immoral act as per barangay records.
      This certification is issued for school, employment, or any legal purpose it may serve.
    </p>
  `,
  "Solo Parent": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a resident of <b>${BARANGAY_INFO.name}</b>
      and has been verified as a <b>solo parent</b> under the Republic Act No. 8972 or the Solo Parents’ Welfare Act.
      This certification is issued for DSWD, employment, or assistance purposes.
    </p>
  `,
  "Late Birth Registration": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a resident of <b>${BARANGAY_INFO.name}</b>
      and is known personally to the Barangay Officials.
      This certification is issued to support the late registration of their birth certificate.
    </p>
  `,
  "No Record": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that after diligent verification of records in the barangay,
      there is <b>no record found</b> under the name of <b>${req.fullname}</b> pertaining to any blotter,
      complaint, or administrative case.
      This certification is issued upon request for legal and administrative purposes.
    </p>
  `,
  "Employment": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a resident of <b>${BARANGAY_INFO.name}</b>
      and is currently employed/self-employed within the barangay jurisdiction.
      This certification is issued upon their request for employment verification or documentation purposes.
    </p>
  `,
  "OJT": `
    <p style="text-indent:2.4em;text-align:justify;">
      This is to certify that <b>${req.fullname}</b> is a resident of <b>${BARANGAY_INFO.name}</b>
      and is being endorsed by the barangay to undergo On-the-Job Training or Internship,
      having no derogatory records in this locality.
      This certification is issued for educational purposes only.
    </p>
  `
}[req.type] || `
  <p style="text-indent:2.4em;text-align:justify;">
    This is to certify that <b>${req.fullname}</b> has made a request for ${req.type} in this barangay.
  </p>
`;


  let logo = BARANGAY_INFO.logo ? 
    `<img src="${BARANGAY_INFO.logo}" alt="Barangay Logo" style="height:82px;max-width:100px;display:block;">` : '';
  let bagongLogo = BARANGAY_INFO.bagongPilipinasLogo ? 
    `<img src="${BARANGAY_INFO.bagongPilipinasLogo}" alt="Bagong Pilipinas Logo" style="height:82px;max-width:110px;display:block;">` : '';

  let html = `
    <html>
    <head>
      <title>${docType} - ${req.fullname}</title>
      <style>
        @media print {
          body { margin:0; }
          .doc-a4 { width:210mm; min-height:297mm; padding:0; margin:0; box-sizing:border-box; background:#fff; }
          .doc-inner { width:100%; max-width:670px; margin:0 auto; padding:54px 46px 44px 46px; }
        }
        body { background:#fff; margin:0; }
        .doc-a4 {
          width:210mm; min-height:297mm; margin:auto; background:#fff; padding:0; 
          box-sizing:border-box; display:flex;flex-direction:column;justify-content:center;
        }
        .doc-inner {
          width:100%; max-width:670px; margin:0 auto; padding:54px 46px 44px 46px; 
          background:#fff; border-radius:12px;
        }
        .doc-logos {
          width:100%; display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;
        }
        .doc-logo-left, .doc-logo-right { width:115px;height:82px;display:flex;align-items:center;justify-content:center;}
        .doc-header-main {text-align:center;margin-top:0;margin-bottom:10px;line-height:1.2;}
        .office-label { font-size:1.01em; font-weight:600; letter-spacing:1.7px; }
        .doc-title {font-size:1.25em;font-weight:800;margin-bottom:1.7px;text-transform:uppercase;letter-spacing:1.2px;}
        .doc-type {font-size:1.18em;font-weight:700;text-decoration:underline;margin-bottom:18px;letter-spacing:1.3px;}
        .doc-body {font-size:1.11em;line-height:1.65;color:#232;text-align:justify;margin-bottom:38px;}
        .doc-issue-date {font-size:1.05em;color:#222;margin-top:14px;margin-bottom:0px;}
        .doc-footer { margin-top:66px; text-align: right; width: 100%; }
        .captain {
          font-weight:700; font-size:1.15em; margin-top:40px; border-top:1.6px solid #333;
          width:265px; margin-left:auto; margin-right:0; padding-top:7px; letter-spacing:.5px;
          text-align:center; display:block;
        }
        .captain-label { font-size:1em; font-weight:400; color:#444; margin-top:2px; display:block; text-align:center; }
        @page { size: A4; margin: 0; }
      </style>
    </head>
    <body>
      <div class="doc-a4">
        <div class="doc-inner">
          <div class="doc-logos">
            <div class="doc-logo-left">${logo}</div>
            <div class="doc-logo-right">${bagongLogo}</div>
          </div>
          <div class="doc-header-main">
            <div class="office-label">Republic of the Philippines<br>City of Quezon<br><b>${BARANGAY_INFO.name.toUpperCase()}</b></div>
            <div class="doc-title">OFFICE OF THE BARANGAY CAPTAIN</div>
            <div class="doc-type">${docType}</div>
          </div>
          <div class="doc-body">
            <div>TO WHOM IT MAY CONCERN:</div>
            ${letterBody}
          </div>
          <div class="doc-issue-date">Issued this <b>${dateStr}</b> at the Office of the Barangay Captain, ${BARANGAY_INFO.name}, ${BARANGAY_INFO.address}</div>
          <div class="doc-footer">
            <div class="captain">
              ${BARANGAY_INFO.captain}
              <div class="captain-label">Punong Barangay</div>
            </div>
          </div>
        </div>
      </div>
    </body>
    </html>
  `;

  let w = window.open("", "PrintDoc", "width=900,height=1400");
  w.document.write(html);
  w.document.close();
  w.focus();
  setTimeout(()=>w.print(), 200);
}

/* Mark Completed */
function markCompleted(id){
  let req=REQUESTS.find(r=>r.id===id);
  if(!req) return;
  req.status="Completed";
  renderRequests(currentFilter);
}

/* View Modal */
function openViewModal(id){
  let req=REQUESTS.find(r=>r.id===id);if(!req)return;
  document.getElementById("viewModalBg").classList.add("active");
  const c=document.getElementById("viewContent");
  c.innerHTML=`
    <div class="view-row"><span class="view-label">Full Name:</span><span class="view-value">${req.fullname}</span></div>
    <div class="view-row"><span class="view-label">Civil Status:</span><span class="view-value">${req.civil_status}</span></div>
    <div class="view-row"><span class="view-label">Date of Birth:</span><span class="view-value">${new Date(req.date_of_birth).toLocaleDateString()}</span></div>
    <div class="view-row"><span class="view-label">House/Street:</span><span class="view-value">${req.house_street}</span></div>
    <div class="view-row"><span class="view-label">City:</span><span class="view-value">${req.city}</span></div>
    <div class="view-row"><span class="view-label">Province:</span><span class="view-value">${req.province}</span></div>
    <div class="view-row"><span class="view-label">Date of Residency:</span><span class="view-value">${new Date(req.date_of_residency).toLocaleDateString()}</span></div>
    <div class="view-row"><span class="view-label">Years of Residency:</span><span class="view-value">${req.years_residency}</span></div>
    <div class="view-row"><span class="view-label">Purpose:</span><span class="view-value">${req.purpose}</span></div>
    ${req.valid_id_url?`<div class="view-row"><span class="view-label">Valid ID:</span><span class="view-value"><img src="${req.valid_id_url}"></span></div>`:""}
    <div class="view-row"><span class="view-label">Email:</span><span class="view-value">${req.email}</span></div>
    <div class="view-row"><span class="view-label">Phone:</span><span class="view-value">${req.phone}</span></div>
    <div class="view-row"><span class="view-label">Barangay:</span><span class="view-value">${req.barangay_name}</span></div>
    <hr>
    <div class="view-row"><span class="view-label">Request Type:</span><span class="view-value">${req.type}</span></div>
    <div class="view-row"><span class="view-label">Status:</span><span class="view-value">${req.status}</span></div>
    <div class="view-row"><span class="view-label">Submitted on:</span><span class="view-value">${new Date(req.date).toLocaleDateString()}</span></div>
    ${req.reason?`<div class="view-row"><span class="view-label">Reason (if Declined):</span><span class="view-value">${req.reason}</span></div>`:""}
  `;
}
function closeViewModal(){document.getElementById("viewModalBg").classList.remove("active");}

renderRequests("all");
</script>
</body>
</html>
