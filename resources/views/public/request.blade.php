<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Public Request | UCC IT Center Services</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --g900:#0a3323;--g800:#124530;--g700:#18633f;--g600:#1e7d4f;
  --g500:#249660;--g400:#2db877;--g300:#5fce9b;--g200:#a8e8cc;
  --g100:#e4f7ef;--g50:#f2fbf7;
  --white:#fff;--offwhite:#f5f7f6;
  --gray100:#f0f4f2;--gray200:#dde6e2;--gray300:#c5d5cf;
  --gray400:#8aa89f;--gray600:#4d6b61;--gray700:#3d5550;--gray800:#1e3530;
  --blue:#1565c0;--blue-bg:#e3f2fd;
  --orange:#e67e00;--orange-bg:#fff3e0;
  --red:#e53e3e;--red-bg:#fff0f0;
  --shadow-sm:0 1px 4px rgba(10,51,35,.07);
  --shadow-md:0 4px 18px rgba(10,51,35,.13);
  --shadow-lg:0 16px 48px rgba(10,51,35,.22);
  --r:14px;--rs:8px;
}
html,body{min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif}
body{background:var(--offwhite)}

/* NAV */
.pub-nav{
  background:linear-gradient(135deg,var(--g900),var(--g700));
  padding:0 24px;height:60px;
  display:flex;align-items:center;justify-content:space-between;
  box-shadow:var(--shadow-md);position:sticky;top:0;z-index:100;
}
.pub-nav .brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.pub-nav .brand img{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.1);padding:3px;object-fit:contain}
.pub-nav .brand span{font-size:.88rem;font-weight:800;color:#fff;line-height:1.2}
.pub-nav-links{display:flex;align-items:center;gap:8px}
.pub-nav-links a{color:rgba(255,255,255,.75);text-decoration:none;font-size:.78rem;font-weight:600;padding:6px 12px;border-radius:8px;transition:all .2s}
.pub-nav-links a:hover{background:rgba(255,255,255,.1);color:#fff}
.pub-nav-links .btn-nav{background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)}

/* HERO */
.pub-hero{
  background:linear-gradient(135deg,var(--g800) 0%,var(--g600) 100%);
  padding:40px 24px 32px;text-align:center;color:#fff;
  position:relative;overflow:hidden;
}
.pub-hero::before{content:'';position:absolute;inset:0;background:url('{{ asset("images/UCC_South.webp") }}') center/cover no-repeat;opacity:.12}
.pub-hero h1{font-size:1.6rem;font-weight:800;margin-bottom:8px;position:relative}
.pub-hero p{font-size:.84rem;opacity:.8;position:relative;max-width:480px;margin:0 auto}

/* MAIN CONTAINER */
.pub-container{max-width:800px;margin:0 auto;padding:24px 20px 48px}

/* STEPS */
.steps-bar{display:flex;align-items:center;margin-bottom:28px}
.step-item{display:flex;align-items:center;gap:8px;flex:1}
.step-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;flex-shrink:0;transition:all .3s}
.step-dot.done{background:var(--g500);color:#fff}
.step-dot.active{background:var(--g700);color:#fff;box-shadow:0 0 0 3px rgba(36,150,96,.25)}
.step-dot.todo{background:var(--gray200);color:var(--gray400)}
.step-label{font-size:.74rem;font-weight:700;color:var(--gray600)}
.step-label.active{color:var(--g700)}
.step-line{flex:1;height:2px;background:var(--gray200);margin:0 4px}
.step-line.done{background:var(--g400)}

/* CARD */
.pub-card{background:var(--white);border-radius:16px;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200);overflow:hidden;margin-bottom:16px}
.pub-card-hd{padding:18px 22px;border-bottom:1px solid var(--gray100);display:flex;align-items:center;gap:10px}
.pub-card-hd h2{font-size:1rem;font-weight:800;color:var(--gray800)}
.pub-card-body{padding:20px 22px}

/* ROLE CARDS */
.role-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.role-opt{border:2px solid var(--gray200);border-radius:14px;padding:20px 14px;text-align:center;cursor:pointer;transition:all .2s;background:var(--white)}
.role-opt:hover{border-color:var(--g400);background:var(--g50);transform:translateY(-2px)}
.role-opt.selected{border-color:var(--g600);background:var(--g100);transform:translateY(-2px)}
.role-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px}
.role-title{font-size:.88rem;font-weight:800;color:var(--gray800);margin-bottom:4px}
.role-desc{font-size:.7rem;color:var(--gray400);line-height:1.4}

/* SERVICE CARDS */
.svc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.svc-opt{border:2px solid var(--gray200);border-radius:12px;padding:16px 12px;text-align:center;cursor:pointer;transition:all .2s;background:var(--white)}
.svc-opt:hover{transform:translateY(-2px)}
.svc-opt.selected-print{border-color:var(--blue);background:var(--blue-bg)}
.svc-opt.selected-photo{border-color:var(--orange);background:var(--orange-bg)}
.svc-opt.selected-research{border-color:var(--g500);background:var(--g100)}
.svc-opt.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}

/* FORM */
.fg{margin-bottom:14px}
.flabel{display:flex;align-items:center;gap:6px;font-size:.74rem;font-weight:600;color:var(--gray600);margin-bottom:5px}
.fc,.fs{width:100%;padding:10px 13px;border:1.5px solid var(--gray200);border-radius:var(--rs);font-family:inherit;font-size:.82rem;color:var(--gray800);background:var(--gray100);outline:none;transition:all .2s;appearance:none}
.fc:focus,.fs:focus{border-color:var(--g500);background:var(--white);box-shadow:0 0 0 3px rgba(36,150,96,.12)}
.fc::placeholder{color:var(--gray400)}
.iw{position:relative}
.iw .ii{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gray400);font-size:.78rem;pointer-events:none}
.iw .fc{padding-left:32px}
.sw{position:relative}
.sw::after{content:'\f107';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:13px;top:50%;transform:translateY(-50%);color:var(--gray400);pointer-events:none;font-size:.75rem}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* PAPER SIZE CARDS */
.paper-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px}
.paper-opt{border:1.5px solid var(--gray200);border-radius:10px;padding:10px 8px;text-align:center;cursor:pointer;background:var(--white);transition:all .2s}
.paper-opt:hover{border-color:var(--g400)}

/* BUTTONS */
.btn-primary{width:100%;padding:13px;background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff;border:none;border-radius:var(--rs);font-family:inherit;font-size:.88rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;box-shadow:0 4px 14px rgba(30,125,79,.3)}
.btn-primary:hover{opacity:.92;transform:translateY(-1px)}
.btn-secondary{padding:10px 20px;background:var(--white);color:var(--g700);border:1.5px solid var(--g300);border-radius:var(--rs);font-family:inherit;font-size:.82rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:all .2s}
.btn-secondary:hover{background:var(--g50)}

/* ALERTS */
.abox{border-radius:var(--rs);padding:10px 14px;font-size:.78rem;display:flex;align-items:flex-start;gap:9px;margin-bottom:14px}
.abox.info{background:var(--g100);border-left:3px solid var(--g400);color:var(--g800)}
.abox.warn{background:#fff8e1;border-left:3px solid #f5a623;color:#7a5200}
.abox.err{background:var(--red-bg);border-left:3px solid var(--red);color:#7a1212}

/* MODAL */
.modal-bg{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px}
.modal-bg.open{display:flex}
.modal-box{background:var(--white);border-radius:16px;width:100%;max-width:560px;max-height:85vh;display:flex;flex-direction:column;box-shadow:var(--shadow-lg);animation:fadeUp .3s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.modal-hd{padding:18px 22px 14px;border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between}
.modal-hd h3{font-size:.95rem;font-weight:800;color:var(--gray800)}
.modal-close{background:none;border:none;color:var(--gray400);cursor:pointer;font-size:1rem;padding:4px}
.modal-body{padding:18px 22px;overflow-y:auto;font-size:.79rem;color:var(--gray600);line-height:1.75}
.modal-body h4{font-size:.82rem;font-weight:700;color:var(--gray800);margin:12px 0 5px}
.modal-body p{margin-bottom:9px}
.modal-body ul{padding-left:17px;margin-bottom:9px}
.modal-footer{padding:14px 22px;border-top:1px solid var(--gray200);display:flex;gap:8px;justify-content:flex-end}
.modal-btn{padding:9px 20px;border-radius:var(--rs);font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;border:none}
.modal-btn.primary{background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff}
.modal-btn.secondary{background:var(--gray100);color:var(--gray700)}

/* Upload zone */
#drop-zone{border:2px dashed var(--gray300);border-radius:10px;padding:22px 16px;text-align:center;cursor:pointer;background:var(--gray100);transition:all .2s}
#drop-zone:hover,#drop-zone.drag-over{border-color:var(--blue);background:var(--blue-bg)}

/* CB */
.cb-row{display:flex;align-items:center;gap:8px;font-size:.77rem;color:var(--gray600);cursor:pointer}
.cb-row input{width:15px;height:15px;accent-color:var(--g600);cursor:pointer;flex-shrink:0}
.cb-row a{color:var(--g700);font-weight:700;text-decoration:none}

/* Footer */
.pub-footer{background:var(--g900);color:rgba(255,255,255,.5);text-align:center;padding:20px;font-size:.72rem}

@media(max-width:600px){
  .role-grid,.svc-grid{grid-template-columns:1fr 1fr}
  .g2{grid-template-columns:1fr}
  .pub-hero h1{font-size:1.2rem}
  .steps-bar .step-label{display:none}
}
</style>
</head>
<body>

{{-- NAVBAR --}}
<nav class="pub-nav">
  <a href="{{ route('public.request') }}" class="brand">
    <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
    <span>UCC IT Center</span>
  </a>
  <div class="pub-nav-links">
    <a href="{{ route('public.track') }}"><i class="fa-solid fa-magnifying-glass"></i> Track Request</a>
    <a href="{{ route('login') }}" class="btn-nav"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
  </div>
</nav>

{{-- HERO --}}
<div class="pub-hero">
  <h1><i class="fa-solid fa-paper-plane" style="margin-right:8px"></i>IT Center Public Request</h1>
  <p>Submit a service request as a student, faculty/staff member, or visitor — no account required.</p>
</div>

{{-- TERMS MODAL --}}
<div class="modal-bg" id="termsModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-file-contract" style="color:var(--g600);margin-right:7px"></i>Terms & Conditions</h3>
      <button class="modal-close" onclick="closeModal('termsModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <h4>1. Public Requests</h4>
      <p>Public requests are processed on a first-come, first-served basis and are subject to the availability of resources at the IT Center.</p>
      <h4>2. Visitors</h4>
      <p>Visitors may only request Printing and Photocopy services. Research / Computer Lab access is exclusively for UCC students and faculty/staff.</p>
      <h4>3. Accuracy of Information</h4>
      <p>You are responsible for providing accurate personal information. False information may result in request cancellation.</p>
      <h4>4. File Submissions</h4>
      <p>Uploaded files are used solely for processing your request and deleted after 30 days.</p>
      <h4>5. Data Privacy</h4>
      <p>Personal information collected is subject to the UCC Data Privacy Policy in accordance with RA 10173.</p>
      <h4>6. Prohibited Content</h4>
      <p>Requests for printing or photocopying of offensive, illegal, or copyrighted content are strictly prohibited.</p>
    </div>
    <div class="modal-footer">
      <button class="modal-btn secondary" onclick="closeModal('termsModal')">Close</button>
      <button class="modal-btn primary" onclick="acceptTerms()"><i class="fa-solid fa-check"></i> I Agree</button>
    </div>
  </div>
</div>

{{-- MAIN --}}
<div class="pub-container">

  @if($errors->any())
  <div class="abox err">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  </div>
  @endif

  {{-- STEPS BAR --}}
  <div class="steps-bar">
    <div class="step-item">
      <div class="step-dot active" id="dot-1">1</div>
      <span class="step-label active">Select Role</span>
    </div>
    <div class="step-line" id="line-1"></div>
    <div class="step-item">
      <div class="step-dot todo" id="dot-2">2</div>
      <span class="step-label">Your Info</span>
    </div>
    <div class="step-line" id="line-2"></div>
    <div class="step-item">
      <div class="step-dot todo" id="dot-3">3</div>
      <span class="step-label">Select Service</span>
    </div>
    <div class="step-line" id="line-3"></div>
    <div class="step-item">
      <div class="step-dot todo" id="dot-4">4</div>
      <span class="step-label">Service Details</span>
    </div>
    <div class="step-line" id="line-4"></div>
    <div class="step-item">
      <div class="step-dot todo" id="dot-5">5</div>
      <span class="step-label">Review & Submit</span>
    </div>
  </div>

  <form action="{{ route('public.request.store') }}" method="POST" enctype="multipart/form-data" id="mainForm">
    @csrf

    {{-- ── STEP 1: SELECT ROLE ── --}}
    <div id="step-1">
      <div class="pub-card">
        <div class="pub-card-hd">
          <div style="width:36px;height:36px;border-radius:9px;background:var(--g100);display:flex;align-items:center;justify-content:center;color:var(--g600);font-size:.9rem;flex-shrink:0">
            <i class="fa-solid fa-user-tag"></i>
          </div>
          <h2>Step 1 — Who are you?</h2>
        </div>
        <div class="pub-card-body">
          <input type="hidden" name="role" id="role-input" value="{{ old('role') }}">
          <div class="role-grid">

            {{-- Student --}}
            <div class="role-opt {{ old('role')==='student'?'selected':'' }}" onclick="selectRole('student',this)">
              <div class="role-icon" style="background:var(--blue-bg);color:var(--blue)">
                <i class="fa-solid fa-graduation-cap"></i>
              </div>
              <div class="role-title">Student</div>
              <div class="role-desc">UCC student with a student ID number</div>
            </div>

            {{-- Faculty/Staff --}}
            <div class="role-opt {{ old('role')==='faculty_staff'?'selected':'' }}" onclick="selectRole('faculty_staff',this)">
              <div class="role-icon" style="background:var(--g100);color:var(--g700)">
                <i class="fa-solid fa-chalkboard-user"></i>
              </div>
              <div class="role-title">Faculty / Staff</div>
              <div class="role-desc">UCC faculty or administrative staff</div>
            </div>

            {{-- Visitor --}}
            <div class="role-opt {{ old('role')==='visitor'?'selected':'' }}" onclick="selectRole('visitor',this)">
              <div class="role-icon" style="background:var(--orange-bg);color:var(--orange)">
                <i class="fa-solid fa-person"></i>
              </div>
              <div class="role-title">Visitor</div>
              <div class="role-desc">Non-UCC individuals (Printing & Photocopy only)</div>
            </div>

          </div>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end">
        <button type="button" onclick="nextStep(1)" class="btn-primary" style="max-width:180px">
          Next <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>

    {{-- ── STEP 2: PERSONAL INFO ── --}}
    <div id="step-2" style="display:none">
      <div class="pub-card">
        <div class="pub-card-hd">
          <div style="width:36px;height:36px;border-radius:9px;background:var(--g100);display:flex;align-items:center;justify-content:center;color:var(--g600);font-size:.9rem;flex-shrink:0">
            <i class="fa-solid fa-id-card"></i>
          </div>
          <h2>Step 2 — Your Information</h2>
        </div>
        <div class="pub-card-body">
          <div class="g2">
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-user"></i> First Name <span style="color:var(--red)">*</span></div>
              <input type="text" name="first_name" class="fc" placeholder="Enter first name" value="{{ old('first_name') }}" required>
            </div>
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-user"></i> Last Name <span style="color:var(--red)">*</span></div>
              <input type="text" name="last_name" class="fc" placeholder="Enter last name" value="{{ old('last_name') }}" required>
            </div>
          </div>
          <div class="fg">
            <div class="flabel"><i class="fa-solid fa-envelope"></i> Email Address <span style="color:var(--red)">*</span></div>
            <div class="iw">
              <i class="fa-solid fa-envelope ii"></i>
              <input type="email" name="email" class="fc" placeholder="your@email.com" value="{{ old('email') }}" required>
            </div>
          </div>
          <div class="fg" id="id-number-group">
            <div class="flabel" id="id-number-label">
              <i class="fa-solid fa-id-card"></i> <span id="id-number-lbl-text">ID Number</span> <span style="color:var(--red)">*</span>
            </div>
            <div class="iw">
              <i class="fa-solid fa-hashtag ii"></i>
              <input type="text" name="id_number" id="id-number-input" class="fc" placeholder="Enter your ID number" value="{{ old('id_number') }}">
            </div>
          </div>
          <div class="fg">
            <div class="flabel"><i class="fa-solid fa-building-columns"></i> Campus <span style="color:var(--red)">*</span></div>
            <div class="sw">
              <select name="campus" class="fs" required>
                <option value="" disabled selected>Select Campus</option>
                @foreach(config('campuses') as $k => $v)
                <option value="{{ $k }}" {{ old('campus')===$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between">
        <button type="button" onclick="prevStep(2)" class="btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <button type="button" onclick="nextStep(2)" class="btn-primary" style="max-width:180px">
          Next <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>

    {{-- ── STEP 3: SELECT SERVICE ── --}}
    <div id="step-3" style="display:none">
      <div class="pub-card">
        <div class="pub-card-hd">
          <div style="width:36px;height:36px;border-radius:9px;background:var(--g100);display:flex;align-items:center;justify-content:center;color:var(--g600);font-size:.9rem;flex-shrink:0">
            <i class="fa-solid fa-list"></i>
          </div>
          <h2>Step 3 — Select Service</h2>
        </div>
        <div class="pub-card-body">
          <input type="hidden" name="service_type" id="svc-input" value="{{ old('service_type') }}">
          <div class="svc-grid">

            <div class="svc-opt {{ old('service_type')==='printing'?'selected-print':'' }}" onclick="selectService('printing',this)">
              <div style="font-size:1.6rem;margin-bottom:8px;color:var(--blue)"><i class="fa-solid fa-print"></i></div>
              <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">Printing</div>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">Document & photo printing</div>
            </div>

            <div class="svc-opt {{ old('service_type')==='photocopy'?'selected-photo':'' }}" onclick="selectService('photocopy',this)">
              <div style="font-size:1.6rem;margin-bottom:8px;color:var(--orange)"><i class="fa-solid fa-copy"></i></div>
              <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">Photocopy</div>
              <div style="font-size:.7rem;color:var(--gray400);margin-top:4px">Fast copying services</div>
            </div>

            {{-- In step-3, update research option --}}
            <div class="svc-opt" id="research-svc-opt" onclick="selectService('research',this)">
                <div style="font-size:1.6rem;margin-bottom:8px;color:var(--g600)"><i class="fa-solid fa-desktop"></i></div>
                <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">Research / PC Lab</div>
                <div style="font-size:.7rem;color:var(--gray400);margin-top:4px" id="research-desc">Computer use (Students & Faculty only)</div>
            </div>

          </div>
          <div id="visitor-notice" style="display:none;margin-top:12px">
            <div class="abox warn">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <div>Visitors are <strong>not allowed</strong> to use the Research / PC Lab. Only Printing and Photocopy services are available.</div>
            </div>
          </div>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between">
        <button type="button" onclick="prevStep(3)" class="btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <button type="button" onclick="nextStep(3)" class="btn-primary" style="max-width:180px">
          Next <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>

    {{-- ── STEP 4: SERVICE DETAILS ── --}}
    <div id="step-4" style="display:none">
      <div class="pub-card">
        <div class="pub-card-hd">
          <div style="width:36px;height:36px;border-radius:9px;background:var(--g100);display:flex;align-items:center;justify-content:center;color:var(--g600);font-size:.9rem;flex-shrink:0">
            <i class="fa-solid fa-pen-to-square"></i>
          </div>
          <h2 id="step4-title">Step 4 — Service Details</h2>
        </div>
        <div class="pub-card-body">

          {{-- PRINTING FIELDS --}}
          <div id="printing-fields" style="display:none">
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-file-arrow-up" style="color:var(--blue)"></i> Upload File <span style="color:var(--red)">*</span></div>
              <div id="drop-zone" onclick="document.getElementById('file-input').click()">
                <div id="drop-icon" style="font-size:1.8rem;color:var(--gray400);margin-bottom:6px"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div id="drop-text" style="font-size:.8rem;font-weight:700;color:var(--gray700)">Click to browse or drag & drop</div>
                <div style="font-size:.68rem;color:var(--gray400);margin-top:3px">PDF, DOC, DOCX, JPG, PNG · Max 10MB</div>
                <div id="file-preview" style="display:none;margin-top:10px;padding:8px 12px;background:var(--blue-bg);border-radius:8px;align-items:center;gap:8px">
                  <i class="fa-solid fa-file" style="color:var(--blue)"></i>
                  <div>
                    <div id="file-name-show" style="font-size:.76rem;font-weight:700;color:var(--blue)"></div>
                    <div id="file-size-show" style="font-size:.65rem;color:var(--gray400)"></div>
                  </div>
                </div>
              </div>
              <input type="file" id="file-input" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display:none" onchange="handleFile(this)">
            </div>
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-expand" style="color:var(--blue)"></i> Paper Size <span style="color:var(--red)">*</span></div>
              <div class="paper-grid">
                @foreach($paperSizes as $ps)
                <label style="cursor:pointer{{ $ps->stock<=0?' opacity:.5;pointer-events:none':'' }}">
                  <input type="radio" name="paper_size" value="{{ $ps->value }}" style="display:none" {{ old('paper_size')===$ps->value?'checked':'' }} {{ $ps->stock<=0?'disabled':'' }}>
                  <div class="paper-opt" style="border-color:var(--gray200)">
                    <div style="font-size:.82rem;font-weight:700">{{ explode(' ',$ps->name)[0] }}</div>
                    <div style="font-size:.62rem;color:var(--gray400);margin-top:2px">{{ Str::after($ps->name,' ') }}</div>
                    <div style="margin-top:5px">
                      <span style="font-size:.6rem;padding:2px 6px;border-radius:6px;font-weight:700;
                        background:{{ $ps->stock>50?'var(--g100)':($ps->stock>0?'#fff8e1':'var(--red-bg)') }};
                        color:{{ $ps->stock>50?'var(--g700)':($ps->stock>0?'var(--orange)':'var(--red)') }}">
                        {{ $ps->stock>0 ? $ps->stock.' left' : 'Out' }}
                      </span>
                    </div>
                  </div>
                </label>
                @endforeach
              </div>
            </div>
            <div class="g2">
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-palette" style="color:var(--blue)"></i> Print Type <span style="color:var(--red)">*</span></div>
                <div style="display:flex;gap:8px">
                  <label style="flex:1;cursor:pointer">
                    <input type="radio" name="print_type" value="black_white" style="display:none" {{ old('print_type','black_white')==='black_white'?'checked':'' }}>
                    <div class="paper-opt" style="padding:10px">
                      <i class="fa-solid fa-circle-half-stroke" style="font-size:.9rem;margin-bottom:3px"></i>
                      <div style="font-size:.72rem;font-weight:700">B&W</div>
                    </div>
                  </label>
                  <label style="flex:1;cursor:pointer">
                    <input type="radio" name="print_type" value="colored" style="display:none" {{ old('print_type')==='colored'?'checked':'' }}>
                    <div class="paper-opt" style="padding:10px">
                      <i class="fa-solid fa-droplet" style="font-size:.9rem;margin-bottom:3px;color:#e53935"></i>
                      <div style="font-size:.72rem;font-weight:700">Colored</div>
                    </div>
                  </label>
                </div>
              </div>
              <div class="fg">
                <div class="flabel"><i class="fa-solid fa-hashtag" style="color:var(--blue)"></i> Copies <span style="color:var(--red)">*</span></div>
                <input type="number" name="copies" class="fc" min="1" max="100" value="{{ old('copies',1) }}">
              </div>
            </div>
          </div>

          {{-- PHOTOCOPY FIELDS --}}
          <div id="photocopy-fields" style="display:none">
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-expand" style="color:var(--orange)"></i> Paper Size <span style="color:var(--red)">*</span></div>
              <div class="paper-grid">
                @foreach($paperSizes as $ps)
                <label style="cursor:pointer{{ $ps->stock<=0?' opacity:.5;pointer-events:none':'' }}">
                  <input type="radio" name="paper_size" value="{{ $ps->value }}" style="display:none" {{ old('paper_size')===$ps->value?'checked':'' }} {{ $ps->stock<=0?'disabled':'' }}>
                  <div class="paper-opt" style="border-color:var(--gray200)">
                    <div style="font-size:.82rem;font-weight:700">{{ explode(' ',$ps->name)[0] }}</div>
                    <div style="font-size:.62rem;color:var(--gray400);margin-top:2px">{{ Str::after($ps->name,' ') }}</div>
                    <div style="margin-top:5px">
                      <span style="font-size:.6rem;padding:2px 6px;border-radius:6px;font-weight:700;
                        background:{{ $ps->stock>50?'var(--g100)':($ps->stock>0?'#fff8e1':'var(--red-bg)') }};
                        color:{{ $ps->stock>50?'var(--g700)':($ps->stock>0?'var(--orange)':'var(--red)') }}">
                        {{ $ps->stock>0 ? $ps->stock.' left' : 'Out' }}
                      </span>
                    </div>
                  </div>
                </label>
                @endforeach
              </div>
            </div>
            <div class="fg">
              <div class="flabel"><i class="fa-solid fa-hashtag" style="color:var(--orange)"></i> Copies <span style="color:var(--red)">*</span></div>
              <input type="number" name="copies" class="fc" min="1" max="100" value="{{ old('copies',1) }}">
            </div>
            <div class="abox info">
              <i class="fa-solid fa-circle-info"></i>
              <div>Please bring your <strong>original document</strong> to the IT Center when you arrive.</div>
            </div>
          </div>

          {{-- RESEARCH FIELDS --}}
            <div id="research-fields" style="display:none">
                <div class="fg">
                    <div class="flabel"><i class="fa-solid fa-clock"></i> Duration <span style="color:var(--red)">*</span></div>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
                    @foreach($durations as $d)
                    <label style="cursor:pointer">
                        <input type="radio" name="duration_minutes" value="{{ $d->value }}" style="display:none"
                        {{ old('duration_minutes')==$d->value?'checked':'' }}>
                        <div style="border:1.5px solid var(--gray200);border-radius:12px;padding:14px 8px;text-align:center;background:var(--white);transition:all .2s" class="dur-pub-opt">
                        <div style="font-size:1.3rem;font-weight:800;color:var(--g700)">{{ $d->value }}</div>
                        <div style="font-size:.68rem;color:var(--gray400);margin-top:2px">minutes</div>
                        </div>
                    </label>
                    @endforeach
                    </div>
                </div>
                <div class="abox info">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>Proceed to IT Center after approval. Bring your UCC ID. Research is for <strong>Students and Faculty/Staff only</strong>.</div>
                </div>
            </div>

          {{-- PURPOSE (shared) --}}
          <div class="fg">
            <div class="flabel"><i class="fa-solid fa-pen-to-square"></i> Purpose <span style="color:var(--red)">*</span></div>
            <textarea name="purpose" class="fc" rows="3" placeholder="State the purpose of your request..." required style="resize:vertical">{{ old('purpose') }}</textarea>
          </div>

        </div>
      </div>
      <div style="display:flex;justify-content:space-between">
        <button type="button" onclick="prevStep(4)" class="btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <button type="button" onclick="nextStep(4)" class="btn-primary" style="max-width:180px">
          Next <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>

    {{-- ── STEP 5: REVIEW & SUBMIT ── --}}
    <div id="step-5" style="display:none">
      <div class="pub-card">
        <div class="pub-card-hd">
          <div style="width:36px;height:36px;border-radius:9px;background:var(--g100);display:flex;align-items:center;justify-content:center;color:var(--g600);font-size:.9rem;flex-shrink:0">
            <i class="fa-solid fa-clipboard-check"></i>
          </div>
          <h2>Step 5 — Review & Submit</h2>
        </div>
        <div class="pub-card-body">

          <div id="review-summary" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
          </div>

          <div style="background:var(--gray100);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px">
            <input type="checkbox" id="terms_check" name="terms" value="1"
              style="width:16px;height:16px;accent-color:var(--g600);cursor:pointer;flex-shrink:0"
              required {{ old('terms')?'checked':'' }}>
            <label for="terms_check" style="font-size:.76rem;color:var(--gray600);cursor:pointer;line-height:1.4">
              I have read and agree to the
              <a href="#" onclick="openModal('termsModal');return false;" style="color:var(--g700);font-weight:700">Terms & Conditions</a>
              and confirm all information provided is accurate.
            </label>
          </div>

          <button type="submit" class="btn-primary">
            <i class="fa-solid fa-paper-plane"></i> Submit Request
          </button>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-start">
        <button type="button" onclick="prevStep(5)" class="btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Back
        </button>
      </div>
    </div>

  </form>

  {{-- INFO CARDS --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:28px">
    <div style="background:var(--white);border-radius:12px;padding:16px;border:1.5px solid var(--gray200);text-align:center">
      <i class="fa-solid fa-clock" style="color:var(--g600);font-size:1.3rem;margin-bottom:8px;display:block"></i>
      <div style="font-size:.78rem;font-weight:800;color:var(--gray800)">Processing Time</div>
      <div style="font-size:.72rem;color:var(--gray400);margin-top:4px">Printing: 15–30 min<br>Photocopy: 10–20 min</div>
    </div>
    <div style="background:var(--white);border-radius:12px;padding:16px;border:1.5px solid var(--gray200);text-align:center">
      <i class="fa-solid fa-magnifying-glass" style="color:var(--g600);font-size:1.3rem;margin-bottom:8px;display:block"></i>
      <div style="font-size:.78rem;font-weight:800;color:var(--gray800)">Track Your Request</div>
      <div style="font-size:.72rem;color:var(--gray400);margin-top:4px">
        <a href="{{ route('public.track') }}" style="color:var(--g700);font-weight:700">Track here →</a>
      </div>
    </div>
    <div style="background:var(--white);border-radius:12px;padding:16px;border:1.5px solid var(--gray200);text-align:center">
      <i class="fa-solid fa-envelope" style="color:var(--g600);font-size:1.3rem;margin-bottom:8px;display:block"></i>
      <div style="font-size:.78rem;font-weight:800;color:var(--gray800)">Need Help?</div>
      <div style="font-size:.72rem;color:var(--gray400);margin-top:4px">itcenter@ucc-caloocan.edu.ph</div>
    </div>
  </div>

</div>

<div class="pub-footer">
  &copy; {{ date('Y') }} University of Caloocan City — IT Center Services System
</div>

<script>
let currentRole = '{{ old('role','') }}';
let currentSvc  = '{{ old('service_type','') }}';

// Role selection
function selectRole(role, el) {
  currentRole = role;
  document.getElementById('role-input').value = role;
  document.querySelectorAll('.role-opt').forEach(o => o.classList.remove('selected'));
  if (el && el.classList) el.classList.add('selected');

  const idGroup  = document.getElementById('id-number-group');
  const idInput  = document.getElementById('id-number-input');
  const idLabel  = document.getElementById('id-number-lbl-text');
  const resOpt   = document.getElementById('research-svc-opt');
  const visNotice= document.getElementById('visitor-notice');

  if (role === 'visitor') {
    idGroup.style.display = 'none';
    idInput.removeAttribute('required');
    resOpt.classList.add('disabled');
    visNotice.style.display = 'block';
  } else {
    idGroup.style.display = 'block';
    idInput.setAttribute('required','required');
    idLabel.textContent = role === 'student' ? 'Student ID Number' : 'Faculty/Staff ID Number';
    resOpt.classList.remove('disabled');
    visNotice.style.display = 'none';
  }
}

// Service selection
function selectService(svc, el) {
  if (currentRole === 'visitor' && svc === 'research') return;
  currentSvc = svc;
  document.getElementById('svc-input').value = svc;
  document.querySelectorAll('.svc-opt').forEach(o => {
    o.classList.remove('selected-print','selected-photo','selected-research');
  });
  el.classList.add(`selected-${svc === 'research' ? 'research' : (svc === 'printing' ? 'print' : 'photo')}`);
}

// Show/hide service fields in step 4
function updateStep4() {
  document.getElementById('printing-fields').style.display  = currentSvc === 'printing'  ? 'block' : 'none';
  document.getElementById('photocopy-fields').style.display = currentSvc === 'photocopy' ? 'block' : 'none';
  document.getElementById('research-fields').style.display  = currentSvc === 'research'  ? 'block' : 'none';
  const titles = {
    printing:  'Step 4 — Printing Details',
    photocopy: 'Step 4 — Photocopy Details',
    research:  'Step 4 — Research Details',
  };
  document.getElementById('step4-title').textContent = titles[currentSvc] || 'Step 4 — Service Details';
}

// Step navigation
function showStep(n) {
  for (let i = 1; i <= 5; i++) {
    const s = document.getElementById('step-'+i);
    if (s) s.style.display = i === n ? 'block' : 'none';
  }
  updateStepBar(n);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepBar(n) {
  for (let i = 1; i <= 5; i++) {
    const dot  = document.getElementById('dot-'+i);
    const line = document.getElementById('line-'+i);
    if (!dot) continue;
    dot.className = 'step-dot ' + (i < n ? 'done' : (i === n ? 'active' : 'todo'));
    if (i < n) dot.innerHTML = '<i class="fa-solid fa-check" style="font-size:.65rem"></i>';
    else dot.textContent = i;
    if (line) line.className = 'step-line ' + (i < n ? 'done' : '');
  }
}

function nextStep(from) {
  if (from === 1) {
    if (!currentRole) { alert('Please select your role to continue.'); return; }
  }
  if (from === 2) {
    const fn = document.querySelector('[name=first_name]').value;
    const ln = document.querySelector('[name=last_name]').value;
    const em = document.querySelector('[name=email]').value;
    const ca = document.querySelector('[name=campus]').value;
    if (!fn || !ln || !em || !ca) { alert('Please fill in all required fields.'); return; }
    if (currentRole !== 'visitor') {
      const id = document.getElementById('id-number-input').value;
      if (!id) { alert('Please enter your ID number.'); return; }
    }
  }
  if (from === 3) {
    if (!currentSvc) { alert('Please select a service.'); return; }
    updateStep4();
  }
  if (from === 4) {
    buildReview();
  }
  showStep(from + 1);
}

function prevStep(from) {
  showStep(from - 1);
}

// Build review summary
function buildReview() {
  const roleNames = { student:'Student', faculty_staff:'Faculty / Staff', visitor:'Visitor' };
  const svcNames  = { printing:'Printing', photocopy:'Photocopy', research:'Research / PC Lab' };
  const fn = document.querySelector('[name=first_name]').value;
  const ln = document.querySelector('[name=last_name]').value;
  const em = document.querySelector('[name=email]').value;
  const ca = document.querySelector('[name=campus]') ? document.querySelector('[name=campus] option:checked').text : '';
  const id = document.getElementById('id-number-input').value;

  let html = `
    <div><div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Role</div><div style="font-size:.84rem;font-weight:700;color:var(--gray800)">${roleNames[currentRole]||currentRole}</div></div>
    <div><div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Service</div><div style="font-size:.84rem;font-weight:700;color:var(--gray800)">${svcNames[currentSvc]||currentSvc}</div></div>
    <div><div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Full Name</div><div style="font-size:.84rem;font-weight:700;color:var(--gray800)">${fn} ${ln}</div></div>
    <div><div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Email</div><div style="font-size:.82rem;color:var(--gray800)">${em}</div></div>
    <div><div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">Campus</div><div style="font-size:.82rem;color:var(--gray800)">${ca}</div></div>`;

  if (id) html += `<div><div style="font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:3px">ID Number</div><div style="font-size:.82rem;font-weight:700;color:var(--gray800)">${id}</div></div>`;

  document.getElementById('review-summary').innerHTML = html;
}

// File upload
function handleFile(input) {
  if (!input.files || !input.files[0]) return;
  const f = input.files[0];
  document.getElementById('file-name-show').textContent = f.name;
  document.getElementById('file-size-show').textContent = (f.size/1024/1024).toFixed(2)+' MB';
  const fp = document.getElementById('file-preview');
  fp.style.display = 'flex';
  document.getElementById('drop-text').textContent = 'File selected:';
  document.getElementById('drop-icon').innerHTML = '<i class="fa-solid fa-file-circle-check" style="color:var(--blue)"></i>';
}

const dz = document.getElementById('drop-zone');
if (dz) {
  dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
      document.getElementById('file-input').files = e.dataTransfer.files;
      handleFile(document.getElementById('file-input'));
    }
  });
}

// Paper opt selection highlight
document.querySelectorAll('.paper-opt').forEach(o => {
  const inp = o.closest('label')?.querySelector('input');
  if (!inp) return;
  inp.addEventListener('change', () => {
    const name = inp.name;
    document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
      r.closest('label').querySelector('.paper-opt').style.borderColor = 'var(--gray200)';
      r.closest('label').querySelector('.paper-opt').style.background  = 'var(--white)';
    });
    o.style.borderColor = inp.name === 'paper_size' && document.getElementById('photocopy-fields').style.display !== 'none' ? 'var(--orange)' : 'var(--blue)';
    o.style.background  = inp.name === 'paper_size' && document.getElementById('photocopy-fields').style.display !== 'none' ? 'var(--orange-bg)' : 'var(--blue-bg)';
  });
});

// Print type / sides highlight
document.querySelectorAll('input[type=radio][name=print_type]').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('input[name=print_type]').forEach(x => {
      x.closest('label').querySelector('.paper-opt').style.borderColor = 'var(--gray200)';
      x.closest('label').querySelector('.paper-opt').style.background  = 'var(--white)';
    });
    r.closest('label').querySelector('.paper-opt').style.borderColor = 'var(--blue)';
    r.closest('label').querySelector('.paper-opt').style.background  = 'var(--blue-bg)';
  });
});

document.querySelectorAll('input[name=duration_minutes]').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('input[name=duration_minutes]').forEach(x => {
      const opt = x.closest('label')?.querySelector('.dur-pub-opt');
      if (opt) { opt.style.borderColor='var(--gray200)'; opt.style.background='var(--white)'; }
    });
    const opt = r.closest('label')?.querySelector('.dur-pub-opt');
    if (opt) { opt.style.borderColor='var(--g500)'; opt.style.background='var(--g100)'; }
  });
});

// Modal
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function acceptTerms() {
  document.getElementById('terms_check').checked = true;
  closeModal('termsModal');
}
document.querySelectorAll('.modal-bg').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));

// If old() data exists, restore state
@if(old('role'))
  selectRole('{{ old('role') }}', document.querySelector('.role-opt[onclick*="{{ old('role') }}"]') || document.createElement('div'));
@endif
@if(old('service_type'))
  currentSvc = '{{ old('service_type') }}';
@endif
@if($errors->any())
  // Restore to appropriate step on validation error
  showStep(4);
@else
  showStep(1);
@endif
</script>
</body>
</html>