<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Account · Servigo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
      --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
      --shadow:0 4px 12px rgba(0,0,0,.08); --radius:14px;
    }
    *{box-sizing:border-box; margin:0; padding:0;}
    body {
      font-family:'Poppins',sans-serif;
      background:var(--bg); color:var(--text);
      display:flex; justify-content:center; padding:20px;
    }
    .container {
      width:100%; max-width:750px;
      display:flex; flex-direction:column; gap:20px;
    }
    /* Profile Card */
    .profile-card {
      background:var(--card); padding:20px; border-radius:var(--radius);
      box-shadow:var(--shadow); display:flex; justify-content:space-between;
      align-items:center; flex-wrap:wrap; gap:16px;
    }
    .profile-info { display:flex; gap:14px; align-items:center; flex:1; }
    .user-icon {
    font-size:40px; color:var(--brand); ;
    padding:8px; border-radius:50%; background:#f3f4f6;
    transition:background .2s;
    }

    .profile-info h2 { margin:0; font-size:18px; color:var(--brand); }
    .profile-info p { font-size:14px; color:var(--muted); margin:2px 0; }
    .status-pill {
      padding:6px 14px; border-radius:999px; font-size:13px;
      font-weight:600; white-space:nowrap;
    }
    .status-pill.unverified { background:#fef2f2; color:#b91c1c; }
    .status-pill.verified { background:#dcfce7; color:#15803d; }

    /* Form Card */
    .form-card {
      background:var(--card); padding:24px 20px;
      border-radius:var(--radius); box-shadow:var(--shadow);
    }
    .form-card h1 {
      font-size:20px; margin-bottom:6px; color:var(--brand);
      display:flex; align-items:center; gap:6px;
    }
    .form-card p { font-size:14px; color:var(--muted); margin-bottom:20px; }

    .form-group { margin-bottom:18px; }
    label { font-weight:600; font-size:14px; display:block; margin-bottom:6px; }
    input, select {
      width:100%; padding:12px; font-size:15px;
      border:1px solid var(--border); border-radius:8px;
      background:#fff;
    }
    input:focus, select:focus {
      border-color:var(--brand); outline:none;
      box-shadow:0 0 0 3px rgba(30,64,175,.15);
    }

    /* File input */
    input[type="file"] {
      border:2px dashed var(--border);
      padding:10px; background:#fafafa; cursor:pointer;
    }
    input[type="file"]::-webkit-file-upload-button {
      margin-right:10px; background:var(--brand); color:#fff;
      border:none; padding:8px 12px; border-radius:6px; cursor:pointer;
    }
    #preview {
      margin-top:12px; max-width:100%; border-radius:8px;
      border:1px solid var(--border); display:none;
    }

    /* Button */
    .btn {
      width:100%; padding:14px; border:none;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff; border-radius:8px; font-weight:600; font-size:15px;
      cursor:pointer; transition:.2s;
    }
    .btn:hover { opacity:.9; transform:translateY(-1px); }
    .btn:disabled { opacity:.6; cursor:not-allowed; }

    .form-actions {
    display:flex; justify-content:space-between; gap:10px;
    margin-top:10px;
    }

    .ghost-btn {
    all:unset; cursor:pointer;
    padding:12px 20px; border-radius:8px;
    font-weight:600; font-size:14px;
    border:1px solid var(--border); color:var(--brand);
    background:#f9fafb; text-align:center;
    transition:.2s;
    }
    .ghost-btn:hover { background:#f3f4f6; }


    /* Footer */
    footer { text-align:center; color:var(--muted); font-size:13px; margin-top:20px; }

    /* Responsive */
    @media(max-width:600px){
      .profile-card { flex-direction:column; align-items:flex-start; }
      .profile-info { flex-direction:column; align-items:flex-start; }
      .user-icon { width:56px; height:56px; }
    }
  </style>
</head>
<body>
  <div class="container">

    <!-- Profile Card -->
    <section class="card profile-card">
      <div class="profile-info">
        <i class='bx bx-user user-icon'></i>
        <div>
          <h2 id="profileName">Juan Dela Cruz</h2>
          <p>Barangay: <span id="profileBrgy">San Isidro</span></p>
          <p>Email: <span id="profileEmail">juan.delacruz@example.com</span></p>
        </div>
      </div>
      <div class="profile-actions">
        <span id="verifyStatus" class="status-pill unverified">Unverified</span>
      </div>
    </section>

    <!-- Verification Form -->
    <section class="form-card">
      <h1> Verify Your Account</h1>
      <p>Fill in your details and upload a valid ID. Your Barangay Admin will review within 1–3 days.</p>

      <form>
        <div class="form-group">
          <label for="validID">Valid ID</label>
          <select id="validID" required>
            <option value="">-- Select ID --</option>
            <option value="national">National ID</option>
            <option value="passport">Passport</option>
            <option value="drivers">Driver’s License</option>
            <option value="voter">Voter’s ID</option>
          </select>
        </div>

        <div class="form-group">
          <label for="fullName">Full Name</label>
          <input id="fullName" type="text" placeholder="Enter full name" required>
        </div>

        <div class="form-group">
          <label for="barangay">Barangay</label>
          <input id="barangay" type="text" placeholder="Enter barangay" required>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" type="email" placeholder="Enter email" required>
        </div>

        <div class="form-group">
          <label for="uploadID">Upload Valid ID</label>
          <input id="uploadID" type="file" accept="image/*" onchange="previewImage(event)" required>
          <img id="preview" alt="Preview">
        </div>
                <!-- Inside the form -->
        <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="history.back()">← Back</button>
        <button type="submit" class="btn">Verify Account</button>
        </div>

      </form>
    </section>

    <footer>
      © 2025 Servigo | Barangay Verification Portal
    </footer>
  </div>

<script>
  function previewImage(event){
    const [file] = event.target.files;
    if(file){
      const preview = document.getElementById('preview');
      preview.src = URL.createObjectURL(file);
      preview.style.display = 'block';
    }
  }
</script>
</body>
</html>
