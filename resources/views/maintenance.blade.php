<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Under Maintenance | UCC IT Center Services</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    font-family:'Plus Jakarta Sans',sans-serif;
    min-height:100vh;
    background:linear-gradient(155deg,#0a3323 0%,#18633f 100%);
    display:flex;align-items:center;justify-content:center;padding:24px;
  }
  .card{
    background:#fff;border-radius:22px;max-width:460px;width:100%;
    padding:44px 36px;text-align:center;box-shadow:0 16px 48px rgba(10,51,35,.3);
  }
  .icon{
    width:76px;height:76px;border-radius:50%;background:#fff3e0;color:#e67e00;
    display:flex;align-items:center;justify-content:center;font-size:1.9rem;
    margin:0 auto 20px;
  }
  h1{font-size:1.3rem;font-weight:800;color:#1e3530;margin-bottom:10px}
  p{font-size:.86rem;color:#4d6b61;line-height:1.7;margin-bottom:22px}
  .brand{display:flex;align-items:center;justify-content:center;gap:9px;margin-bottom:26px}
  .brand img{width:34px;height:34px;object-fit:contain}
  .brand span{font-size:.8rem;font-weight:800;color:#124530}
  .foot{font-size:.68rem;color:#8aa89f;margin-top:6px}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">
      <img src="{{ asset('images/UCC_Logo.png') }}" alt="UCC">
      <span>UCC IT Center Services</span>
    </div>
    <div class="icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
    <h1>We'll Be Right Back</h1>
    <p>{{ $message }}</p>
    <div class="foot">If you're an administrator, please sign in as usual.</div>
  </div>
</body>
</html>