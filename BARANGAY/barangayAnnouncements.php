<?php include 'INCLUDES/barangaySidebar.php'; ?> 
<?php include 'INCLUDES/barangayTopbar.php'; ?>

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
    body{
      font-family:system-ui,sans-serif;
      background:var(--bg);
      color:var(--text);
      line-height:1.5;
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
      <h2>Create Announcement</h2>
      <form id="announceForm" enctype="multipart/form-data">
        <label>Title</label>
        <input type="text" name="title" required>
        <label>Description</label>
        <textarea name="description" required></textarea>
        <label>Category</label>
        <select name="category" required>
          <option>Advisory</option>
          <option>Event</option>
          <option>Emergency</option>
        </select>
        <label>Image (optional)</label>
        <input type="file" id="image" accept="image/*">
        <button type="submit" class="btn">Post Announcement</button>
        <p id="msg"></p>
      </form>
    </section>

    <!-- Feed -->
    <section class="card">
      <h2>My Announcements</h2>
      <div id="posts"></div>
    </section>

  </main>
</div>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";
const BARANGAY=localStorage.getItem("bg_name")||"San Isidro";

/* ✅ Fixed Upload Image */
async function uploadImage(file){
  const fileName=Date.now()+"-"+file.name;
  const formData=new FormData();
  formData.append("file",file);

  const res=await fetch(`${SUPABASE_URL}/storage/v1/object/announcements/${fileName}`,{
    method:"POST",
    headers:{
      apikey:SUPABASE_KEY,
      Authorization:"Bearer "+SUPABASE_KEY,
      "x-upsert":"true"
    },
    body:formData
  });
  if(!res.ok) throw new Error("Upload failed: "+(await res.text()));
  return `${SUPABASE_URL}/storage/v1/object/public/announcements/${fileName}`;
}

/* Submit Form */
document.getElementById("announceForm").addEventListener("submit",async e=>{
  e.preventDefault();
  const msg=document.getElementById("msg");
  msg.textContent=""; msg.className="";

  const title=e.target.title.value.trim();
  const description=e.target.description.value.trim();
  const category=e.target.category.value;
  let image_url=null;

  const file=document.getElementById("image").files[0];
  if(file){ try{ image_url=await uploadImage(file);}catch(err){msg.className="error";msg.textContent=err.message;return;} }

  const data={title,description,category,barangay_name:BARANGAY,image_url};
  const res=await fetch(`${SUPABASE_URL}/rest/v1/announcements`,{
    method:"POST",
    headers:{
      apikey:SUPABASE_KEY,
      Authorization:"Bearer "+SUPABASE_KEY,
      "Content-Type":"application/json",
      Prefer:"return=representation"
    },
    body:JSON.stringify(data)
  });
  const result=await res.json();

  if(res.ok){
    msg.className="ok";msg.textContent="✅ Posted!";
    e.target.reset();
    loadPosts();
  } else {
    msg.className="error";msg.textContent="❌ "+(result.message||JSON.stringify(result));
  }
});

/* Load Posts */
async function loadPosts(){
  const postsEl=document.getElementById("posts");
  postsEl.innerHTML="";
  const res=await fetch(`${SUPABASE_URL}/rest/v1/announcements?barangay_name=eq.${BARANGAY}&order=created_at.desc`,{
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  const data=await res.json();
  if(!data.length){postsEl.innerHTML="<p>No announcements yet.</p>";return;}

  data.forEach(p=>{
    const div=document.createElement("div");
    div.className="post";
    div.innerHTML=`
      <div class="meta"><strong>${p.category}</strong> • ${new Date(p.created_at).toLocaleDateString()}</div>
      <h3>${p.title}</h3>
      <p class="desc">${p.description}</p>
      ${p.image_url?`<div class="image-wrapper"><img src="${p.image_url}" alt="Announcement image"></div>`:""}
      <button class="delete" onclick="deletePost(${p.id})">🗑 Delete</button>
    `;

    // See More/See Less
    const descEl=div.querySelector(".desc");
    requestAnimationFrame(()=>{
      if(descEl.scrollHeight>descEl.clientHeight){
        const toggle=document.createElement("button");
        toggle.className="see-toggle";
        toggle.textContent="See More";
        toggle.onclick=()=>{
          div.classList.toggle("expanded");
          toggle.textContent=div.classList.contains("expanded")?"See Less":"See More";
        };
        div.insertBefore(toggle,div.querySelector(".image-wrapper"));
      }
    });

    postsEl.appendChild(div);
  });
}

/* Delete Post */
async function deletePost(id){
  if(!confirm("Delete this post?")) return;
  await fetch(`${SUPABASE_URL}/rest/v1/announcements?id=eq.${id}`,{
    method:"DELETE",
    headers:{apikey:SUPABASE_KEY,Authorization:"Bearer "+SUPABASE_KEY}
  });
  loadPosts();
}

loadPosts();
</script>
</body>
</html>
