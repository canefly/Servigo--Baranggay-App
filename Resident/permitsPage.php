<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? "Unknown Barangay";
$email       = $_SESSION['sg_email'] ?? "";
$message     = "";

/* ---------------- FETCH RESIDENT INFO ---------------- */
$resident_name = $dob = $house_street = $city = $province = $phone = "";
if ($resident_id) {
  $stmt = $conn->prepare("SELECT first_name,middle_name,last_name,suffix,email,phone,birthdate,house_no,street,city,province FROM residents WHERE id=?");
  $stmt->bind_param("i", $resident_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($r = $res->fetch_assoc()) {
    $resident_name = trim(($r['first_name'] ?? '') . ' ' . (($r['middle_name'] ?? '') ? ($r['middle_name'].' ') : '') . ($r['last_name'] ?? '') . ' ' . ($r['suffix'] ?? ''));
    $email        = $r['email'] ?? '';
    $phone        = $r['phone'] ?? '';
    $dob          = $r['birthdate'] ?? '';
    $house_street = trim(($r['house_no'] ?? '').' '.($r['street'] ?? ''));
    $city         = $r['city'] ?? '';
    $province     = $r['province'] ?? '';
  }
  $stmt->close();
}

/* ---------------- HANDLE FORM SUBMISSION ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['permit_type'])) {
  $permit_type = trim($_POST['permit_type']);
  $status = "Pending";
  $created_at = date("Y-m-d H:i:s");

  $fullname = trim($_POST['fullname'] ?? $resident_name);
  $email_in = trim($_POST['email'] ?? $email);
  $phone_in = trim($_POST['phone'] ?? $phone);
  $house_street_in = trim($_POST['house_street'] ?? $house_street);
  $city_in = trim($_POST['city'] ?? $city);
  $province_in = trim($_POST['province'] ?? $province);
  $purpose = trim($_POST['purpose'] ?? '');

  if ($house_street_in === '') $house_street_in = $house_street;

  $house_no = ''; $street = '';
  if ($house_street_in !== '') {
    $parts = explode(" ", $house_street_in, 2);
    $house_no = $parts[0] ?? '';
    $street   = $parts[1] ?? '';
  }

  /* ---------------- FILE UPLOAD HANDLER ---------------- */
  $uploads = [];
  foreach ($_FILES as $key => $file) {
    if ($file['error'] === UPLOAD_ERR_OK && $file['name'] !== '') {
      $dir = __DIR__ . "/../uploads/permits/";
      if (!is_dir($dir)) mkdir($dir, 0777, true);
      $fname = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file['name']);
      $dest  = $dir . $fname;
      move_uploaded_file($file['tmp_name'], $dest);
      $uploads[$key] = "uploads/permits/" . $fname;
    }
  }

  /* ---------------- TABLE MAPPING ---------------- */
  $map = [
    "Barangay Clearance"      => "barangay_clearance_requests",
    "Residency"               => "residency_requests",
    "Indigency"               => "indigency_requests",
    "Good Moral"              => "goodmoral_requests",
    "Solo Parent"             => "soloparent_requests",
    "Late Birth Registration" => "latebirth_requests",
    "No Record"               => "norecord_requests",
    "OJT"                     => "ojt_requests",
    "Business Permit"         => "business_permit_requests"
  ];
  $table = $map[$permit_type] ?? "barangay_clearance_requests";

  /* ---------------- COLUMN SCAN ---------------- */
  $colsRes = $conn->query("SHOW COLUMNS FROM `$table`");
  $columns = [];
  while ($row = $colsRes->fetch_assoc()) $columns[] = $row['Field'];

  /* ---------------- BUILD INSERT ---------------- */
  $insert = [];
  foreach ($columns as $c) {
    switch ($c) {
      case 'resident_id':   $insert[$c] = $resident_id; break;
      case 'fullname':      $insert[$c] = $fullname; break;
      case 'email':         $insert[$c] = $email_in; break;
      case 'phone':         $insert[$c] = $phone_in; break;
      case 'birthdate':     $insert[$c] = $dob; break;
      case 'house_no':      $insert[$c] = $house_no; break;
      case 'street':        $insert[$c] = $street; break;
      case 'house_street':  $insert[$c] = $house_street_in; break;
      case 'city':          $insert[$c] = $city_in; break;
      case 'province':      $insert[$c] = $province_in; break;
      case 'purpose':       $insert[$c] = $purpose; break;
      case 'barangay_name': $insert[$c] = $barangay; break;
      case 'permit_type':   $insert[$c] = $permit_type; break;
      case 'status':        $insert[$c] = $status; break;
      case 'created_at':    $insert[$c] = $created_at; break;
      default:
        if (isset($uploads[$c])) {
          $insert[$c] = $uploads[$c];
        } elseif (array_key_exists($c, $_POST)) {
          $val = trim($_POST[$c]);
          $insert[$c] = ($val !== '') ? $val : null;
        } else {
          $insert[$c] = null;
        }
    }
  }

  /* ---------------- EXECUTE INSERT ---------------- */
  $cols = array_keys($insert);
  $placeholders = implode(",", array_fill(0, count($cols), "?"));
  $types = implode("", array_map(fn($c)=>$c==='resident_id'?'i':'s', $cols));
  $values = array_values($insert);

  $sql = "INSERT INTO `$table` (".implode(",", $cols).") VALUES ($placeholders)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$values);

  if ($stmt->execute()) {
    $message = "<div class='toast success'>✅ Request submitted successfully!</div>";
  } else {
    $message = "<div class='toast error'>❌ Insert failed: " . htmlspecialchars($stmt->error) . "</div>";
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Servigo · Barangay Permits</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --accent:#16a34a; --bg:#fff; --border:#e5e7eb;
  --brand:#1e40af; --muted:#6b7280; --radius:14px; --text:#1e1e1e;
}
body{background:var(--bg);color:var(--text);font-family:"Parkinsans","Outfit",sans-serif;margin:0;padding:0;}
.container-custom{max-width:1400px;margin:auto;padding:40px 6vw 80px;}
.hero h1{color:var(--brand);font-family:"Outfit";font-size:2.2rem;font-weight:700;margin-bottom:6px;}
.hero p{color:var(--muted);font-size:1rem;max-width:600px;line-height:1.5;}
.permit-grid{display:grid;gap:1.2rem;justify-content:center;grid-template-columns:repeat(auto-fit,minmax(260px,300px));margin:auto;max-width:85%;margin-top:2.5rem;}
.card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 2px 8px rgba(0,0,0,.05);padding:1.5rem 1.25rem;display:flex;flex-direction:column;justify-content:space-between;transition:box-shadow .2s ease,transform .2s ease;}
.card:hover{transform:translateY(-3px);box-shadow:0 4px 12px rgba(0,0,0,.1);}
.card h3{color:var(--brand);font-weight:700;font-size:1.1rem;margin-bottom:0.5rem;}
.card p{color:var(--muted);font-size:0.95rem;flex-grow:1;margin-bottom:1rem;line-height:1.45;}
.btn-gradient{background:linear-gradient(135deg,var(--brand),var(--accent));border:none;border-radius:10px;color:#fff;font-weight:600;padding:10px 14px;transition:.2s;cursor:pointer;}
.btn-gradient:hover{opacity:.9;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);backdrop-filter:blur(5px);z-index:2000;align-items:center;justify-content:center;padding:1rem;}
.modal.show{display:flex;}
.modal form{background:#fff;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,0.15);width:100%;max-width:480px;display:flex;flex-direction:column;overflow:hidden;animation:fadeIn .3s ease;}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
.modal .body{padding:1.5rem;overflow-y:auto;max-height:75vh;}
.modal .footer{padding:1rem;text-align:center;border-top:1px solid #e5e7eb;background:#fafafa;}
.modal label{display:block;font-weight:600;font-size:0.9rem;margin-top:0.7rem;}
.modal input,.modal textarea{width:100%;border:1px solid #e5e7eb;border-radius:10px;padding:10px;font-size:0.95rem;margin-top:4px;transition:border-color .2s;}
.modal input:focus,.modal textarea:focus{border-color:#1e40af;outline:none;}
.modal h2{color:#1e40af;font-size:1.3rem;margin:0 0 0.8rem;text-align:center;}
footer{background:#fff;color:var(--muted);font-size:0.9rem;padding:20px;text-align:center;border-top:1px solid var(--border);}

/* Toast notification */
.toast {
  position: fixed;
  bottom: 25px;
  right: 25px;
  background: #16a34a;
  color: #fff;
  font-weight: 600;
  padding: 14px 18px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 3000;
  opacity: 0;
  transform: translateY(20px);
  animation: toastSlide 0.4s ease forwards, fadeOut 0.5s ease 4s forwards;
}
.toast.error { background: #dc2626; }
@keyframes toastSlide {
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOut {
  to { opacity: 0; transform: translateY(20px); }
}
</style>
<script>
document.addEventListener("DOMContentLoaded",()=>{
  const toast=document.querySelector(".toast");
  if(toast){setTimeout(()=>toast.remove(),4500);}
});
</script>
</head>
<body>
<div class="container-custom">
  <section class="hero">
    <h1>Barangay Permits & Documents</h1>
    <p>Apply online for barangay clearances, certificates, and permits — available anytime.</p>
    <?php if($message) echo $message; ?>
  </section>

  <div class="permit-grid" id="permitGrid"></div>
</div>

<footer>© 2025 Servigo. All rights reserved.</footer>

<div id="applyModal" class="modal"></div>

<script>
const commonFields = [
  { label:"Full Name", name:"fullname", type:"text", required:true },
  { label:"Email", name:"email", type:"email", required:true },
  { label:"Phone Number", name:"phone", type:"text" },
  { label:"House & Street", name:"house_street", type:"text", required:true },
  { label:"City", name:"city", type:"text", required:true },
  { label:"Province", name:"province", type:"text", required:true },
  { label:"Purpose", name:"purpose", type:"textarea", required:true }
];
const PERMIT_FORMS = {
  "Barangay Clearance": {requirements:["Valid ID","Cedula"],fields:[...commonFields,{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "Residency": {requirements:["Proof of Address"],fields:[...commonFields,{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "Indigency": {requirements:["Valid ID","Proof of Income"],fields:[...commonFields,{label:"Proof of Income",name:"proof_of_income_url",type:"file"},{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "Good Moral": {requirements:["Valid ID","Barangay Clearance"],fields:[...commonFields,{label:"Barangay Clearance",name:"barangay_clearance_url",type:"file"},{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "Solo Parent": {requirements:["Valid ID","Proof of Solo Parent"],fields:[...commonFields,{label:"Proof of Solo Parent",name:"proof_of_solo_status_url",type:"file",required:true},{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "Late Birth Registration": {requirements:["Valid ID","Birth Record"],fields:[...commonFields,{label:"Birth Record",name:"birth_record_url",type:"file",required:true},{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "No Record": {requirements:["Valid ID"],fields:[...commonFields,{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "OJT": {requirements:["Valid ID","School Endorsement"],fields:[...commonFields,{label:"School Name",name:"school_name",type:"text",required:true},{label:"Endorsement Letter",name:"endorsement_letter_url",type:"file",required:true},{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]},
  "Business Permit": {requirements:["DTI/SEC Certificate","Lease Papers","Valid ID"],fields:[...commonFields,{label:"Business Name",name:"business_name",type:"text",required:true},{label:"Owner Name",name:"owner_name",type:"text",required:true},{label:"Business Type",name:"business_type",type:"text"},{label:"DTI/SEC Certificate",name:"dti_cert_url",type:"file"},{label:"Lease Contract",name:"lease_contract_url",type:"file"},{label:"Valid ID",name:"valid_id_url",type:"file",required:true}]}
};
const permitGrid=document.getElementById("permitGrid");
for(const key in PERMIT_FORMS){
  const card=document.createElement("div");
  card.className="card";
  const reqs=PERMIT_FORMS[key].requirements.join(", ");
  card.innerHTML=`<h3>${key}</h3><p><strong>Requirements:</strong> ${reqs}</p><button class="btn-gradient" onclick="openForm('${key}')">Apply Now</button>`;
  permitGrid.appendChild(card);
}
function openForm(type){
  const modal=document.getElementById("applyModal");
  const data=PERMIT_FORMS[type];
  if(!data)return;
  let inputsHTML="";
  data.fields.forEach(f=>{
    const req=f.required?"required":"";
    if(f.type==="textarea"){
      inputsHTML+=`<label>${f.label}<textarea name="${f.name}" rows="3" ${req}></textarea></label>`;
    }else{
      inputsHTML+=`<label>${f.label}<input type="${f.type}" name="${f.name}" ${req}></label>`;
    }
  });
  modal.innerHTML=`<form method="POST" enctype="multipart/form-data"><div class="body"><h2>Barangay ${type} Request</h2><input type="hidden" name="permit_type" value="${type}">${inputsHTML}</div><div class="footer"><button class="btn-gradient" type="submit">Submit</button><button type="button" class="btn-gradient" style="background:#ef4444" onclick="closeForm()">Cancel</button></div></form>`;
  modal.classList.add("show");document.body.style.overflow='hidden';
}
function closeForm(){document.getElementById("applyModal").classList.remove("show");document.body.style.overflow='';}
</script>
</body>
</html>
