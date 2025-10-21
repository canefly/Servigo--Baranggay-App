<?php include 'Components/barangaySidebar.php'; ?> 
<?php include 'Components/barangayTopbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay · Announcements</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:#f5f7fa;
      --card:#ffffff;
      --text:#222;
      --muted:#6b7280;
      --brand:#047857;
      --accent:#10b981;
      --error:#b91c1c;
      --ok:#166534;
      --shadow:0 2px 8px rgba(0,0,0,.08);
      --radius:14px;
      --gap:16px;
    }

    *{box-sizing:border-box;margin:0;padding:0}
  body {
  margin: 0;              /* prevent browser default margin */
  background: var(--bg);
  color: var(--text);
  font-family: system-ui, sans-serif;
}
    .layout{display:flex;min-height:100vh;}
    .main-content{
      flex:1;
      padding:var(--gap);
      transition:margin-left .3s ease;
      max-width:100%;
    }
    @media(min-width:1024px){.main-content{margin-left:275px;}}

    /* Card */
    .card{
      background:var(--card);
      padding:var(--gap);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      margin-bottom:var(--gap);
      width:100%;
    }
    h2{margin-bottom:12px;font-size:1.25rem;color:var(--brand);}
    label{font-weight:600;display:block;margin-top:12px;}
    input,textarea,select{
      width:100%;
      padding:12px;
      font-size:15px;
      margin-top:6px;
      border:1px solid #e5e7eb;
      border-radius:10px;
    }
    textarea{resize:vertical;min-height:100px;}

    /* Buttons */
    .btn{
      width:100%;
      margin-top:16px;
      padding:12px;
      border-radius:10px;
      border:none;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff;
      font-weight:600;
      cursor:pointer;
      text-align:center;
      transition:.2s;
    }
    .btn:hover{opacity:.9;}

    /* Feedback */
    .error,.ok{
      margin-top:10px;
      padding:10px;
      border-radius:8px;
      font-size:.9rem;
    }
    .error{background:#fee2e2;color:var(--error);border:1px solid #ef4444;}
    .ok{background:#dcfce7;color:var(--ok);border:1px solid #22c55e;}

    /* Post (feed style) */
    .post{
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:12px;
      padding:14px;
      margin-bottom:16px;
      box-shadow:0 2px 6px rgba(0,0,0,.05);
      display:flex;
      flex-direction:column;
      gap:10px;
      word-wrap:break-word;
    }
    .post .meta{font-size:13px;color:var(--muted);}
    .post h3{margin:0;font-size:16px;color:#111;}

    /* Description with truncation */
    .post .desc{
      font-size:14px;
      overflow:hidden;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      text-overflow:ellipsis;
      transition:max-height .3s ease;
    }
    .post.expanded .desc{
      -webkit-line-clamp:unset;
      overflow:visible;
      max-height:1000px;
    }

    .see-toggle,.delete{
      all:unset;
      cursor:pointer;
      font-size:14px;
      font-weight:600;
    }
    .see-toggle{color:#1e40af;}
    .delete{color:var(--error);margin-top:4px;}

    /* Responsive image wrapper */
    .image-wrapper {
      position: relative;
      width: 100%;
      max-height: 500px;
      border-radius: 12px;
      overflow: hidden;
      background: #f0f0f0;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .image-wrapper img {
      width: 100%;
      height: auto;
      max-height: 500px;
      object-fit: contain;
      display: block;
    }

    /* Responsive tweaks */
    @media(max-width:600px){
      .card,.post{padding:12px;border-radius:10px;}
      h2{font-size:1.1rem;}
      .post h3{font-size:15px;}
      .btn{font-size:14px;padding:10px;}
    }
  </style>
</head>
<body>
<div class="layout">
  <main class="main-content">

    <!-- Create -->
    <section class="card">

      </form>
    </section>

    <!-- Feed -->
    <section class="card">
      <h2>My Announcements</h2>
      <div id="posts"></div>
    </section>

  </main>
</div>


</body>
</html>
