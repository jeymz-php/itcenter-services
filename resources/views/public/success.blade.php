<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Submitted | UCC IT Center</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--g700:#18633f;--g500:#249660;--g100:#e4f7ef;--g50:#f2fbf7;--white:#fff;--gray200:#dde6e2;--gray600:#4d6b61;--gray800:#1e3530;--shadow-md:0 4px 18px rgba(10,51,35,.13)}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--g50);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.card{background:var(--white);border-radius:20px;box-shadow:var(--shadow-md);padding:40px 36px;max-width:500px;width:100%;text-align:center}
.check-icon{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--g500),var(--g700));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;margin:0 auto 18px;animation:pop .5s cubic-bezier(.16,1,.3,1)}
@keyframes pop{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
h1{font-size:1.3rem;font-weight:800;color:var(--gray800);margin-bottom:8px}
p{font-size:.82rem;color:var(--gray600);line-height:1.65;margin-bottom:20px}
.req-badge{background:var(--g100);border-radius:10px;padding:14px 18px;margin-bottom:22px;display:inline-block}
.req-badge span{font-size:.72rem;color:var(--gray600);display:block;margin-bottom:3px}
.req-badge strong{font-size:1.2rem;font-family:monospace;color:var(--g700)}
.btn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:9px;font-family:inherit;font-size:.82rem;font-weight:700;cursor:pointer;text-decoration:none;border:none;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff;box-shadow:0 4px 12px rgba(30,125,79,.25)}
.btn-secondary{background:var(--g100);color:var(--g700);border:1.5px solid var(--gray200)}
.steps{display:flex;flex-direction:column;gap:8px;margin-bottom:22px;text-align:left}
.step-row{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--g50);border-radius:8px}
.step-row .si{width:28px;height:28px;border-radius:50%;background:var(--g100);color:var(--g600);display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0}
.step-row .st{font-size:.76rem;font-weight:600;color:var(--gray800)}
</style>
</head>
<body>
<div class="card">
  <div class="check-icon"><i class="fa-solid fa-check"></i></div>
  <h1>Request Submitted!</h1>
  <p>Your {{ ucfirst($gr->service_type) }} request has been received. Please save your request number below to track its status.</p>

  <div class="req-badge">
    <span>Your Request Number</span>
    <strong>{{ $gr->request_number }}</strong>
  </div>

  <div class="steps">
    <div class="step-row">
      <div class="si"><i class="fa-solid fa-check"></i></div>
      <div class="st">Request submitted — awaiting IT Center review</div>
    </div>
    <div class="step-row">
      <div class="si"><i class="fa-solid fa-hourglass-half"></i></div>
      <div class="st">Admin will approve and process your request</div>
    </div>
    <div class="step-row">
      <div class="si"><i class="fa-solid fa-bell"></i></div>
      <div class="st">Visit the IT Center to claim your request</div>
    </div>
  </div>

  <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
    <a href="{{ route('public.track', ['number' => $gr->request_number]) }}" class="btn btn-primary">
      <i class="fa-solid fa-magnifying-glass"></i> Track Request
    </a>
    <a href="{{ route('public.request') }}" class="btn btn-secondary">
      <i class="fa-solid fa-plus"></i> New Request
    </a>
  </div>
</div>
</body>
</html>