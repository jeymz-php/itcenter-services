<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Request | UCC IT Center</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--g700:#18633f;--g500:#249660;--g100:#e4f7ef;--g50:#f2fbf7;--white:#fff;--gray100:#f0f4f2;--gray200:#dde6e2;--gray400:#8aa89f;--gray600:#4d6b61;--gray800:#1e3530;--blue:#1565c0;--blue-bg:#e3f2fd;--orange:#e67e00;--orange-bg:#fff3e0;--red:#e53e3e;--red-bg:#fff0f0;--shadow-md:0 4px 18px rgba(10,51,35,.13);--rs:8px}
body{font-family:'Plus Jakarta Sans',sans-serif;background:linear-gradient(135deg,var(--g700),var(--g500));min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.card{background:var(--white);border-radius:20px;box-shadow:var(--shadow-md);padding:32px 30px;max-width:520px;width:100%}
h1{font-size:1.2rem;font-weight:800;color:var(--gray800);margin-bottom:6px}
.sub{font-size:.78rem;color:var(--gray600);margin-bottom:20px}
.fg{margin-bottom:14px}
.flabel{font-size:.74rem;font-weight:600;color:var(--gray600);margin-bottom:5px;display:flex;align-items:center;gap:6px}
.fc{width:100%;padding:11px 14px;border:1.5px solid var(--gray200);border-radius:var(--rs);font-family:inherit;font-size:.88rem;color:var(--gray800);background:var(--gray100);outline:none;transition:all .2s}
.fc:focus{border-color:var(--g500);background:var(--white);box-shadow:0 0 0 3px rgba(36,150,96,.12)}
.btn{width:100%;padding:12px;background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff;border:none;border-radius:var(--rs);font-family:inherit;font-size:.88rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px}
.tag{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:8px;font-size:.72rem;font-weight:700}
.tag-pend{background:var(--orange-bg);color:var(--orange)}
.tag-appr{background:var(--blue-bg);color:var(--blue)}
.tag-res{background:var(--g100);color:var(--g700)}
.tag-done{background:var(--g100);color:var(--g500)}
.tag-rej{background:var(--red-bg);color:var(--red)}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}
.detail-item .lbl{font-size:.63rem;color:var(--gray400);font-weight:700;text-transform:uppercase;margin-bottom:2px}
.detail-item .val{font-size:.82rem;font-weight:700;color:var(--gray800)}
.back-link{display:block;text-align:center;margin-top:14px;font-size:.76rem;color:var(--gray600);text-decoration:none}
.back-link:hover{color:var(--g700)}
</style>
</head>
<body>
<div class="card">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
    <img src="{{ asset('images/UCC_Logo.png') }}" style="width:36px;height:36px;border-radius:8px;background:var(--g100);padding:3px;object-fit:contain">
    <div>
      <h1>Track Your Request</h1>
      <div class="sub">Enter your request number to check its status</div>
    </div>
  </div>

  <form method="GET" action="{{ route('public.track') }}">
    <div class="fg">
      <div class="flabel"><i class="fa-solid fa-hashtag" style="color:var(--g600)"></i> Request Number</div>
      <input type="text" name="number" class="fc" placeholder="e.g. G-000001" value="{{ request('number') }}" required>
    </div>
    <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Track Request</button>
  </form>

  @if(request('number') && !$gr)
  <div style="margin-top:16px;padding:14px;background:var(--red-bg);border-radius:10px;font-size:.78rem;color:var(--red);display:flex;align-items:center;gap:8px">
    <i class="fa-solid fa-circle-xmark"></i>
    No request found with number <strong>{{ request('number') }}</strong>.
  </div>
  @endif

  @if($gr)
  <div style="margin-top:20px;padding:16px;background:var(--g50);border-radius:12px;border:1.5px solid var(--gray200)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div>
        <div style="font-family:monospace;font-size:.9rem;font-weight:800;color:var(--gray800)">{{ $gr->request_number }}</div>
        <div style="font-size:.7rem;color:var(--gray400);margin-top:2px">{{ $gr->created_at->format('M d, Y g:i A') }}</div>
      </div>
      @php
        $sc = ['pending'=>'tag-pend','approved'=>'tag-appr','processing'=>'tag-res','completed'=>'tag-done','rejected'=>'tag-rej'];
      @endphp
      <span class="tag {{ $sc[$gr->status]??'' }}">{{ strtoupper($gr->status) }}</span>
    </div>

    <div class="detail-grid">
      <div class="detail-item">
        <div class="lbl">Name</div>
        <div class="val">{{ $gr->full_name }}</div>
      </div>
      <div class="detail-item">
        <div class="lbl">Role</div>
        <div class="val">{{ ucfirst(str_replace('_',' ',$gr->role)) }}</div>
      </div>
      <div class="detail-item">
        <div class="lbl">Service</div>
        <div class="val">{{ ucfirst($gr->service_type) }}</div>
      </div>
      <div class="detail-item">
        <div class="lbl">Campus</div>
        <div class="val">{{ config('campuses.'.$gr->campus) }}</div>
      </div>
      @if($gr->copies)
      <div class="detail-item">
        <div class="lbl">Copies</div>
        <div class="val">{{ $gr->copies }}</div>
      </div>
      @endif
      @if($gr->paper_size)
      <div class="detail-item">
        <div class="lbl">Paper Size</div>
        <div class="val">{{ strtoupper($gr->paper_size) }}</div>
      </div>
      @endif
    </div>

    @if($gr->admin_note)
    <div style="margin-top:12px;padding:10px 12px;background:var(--red-bg);border-radius:8px;font-size:.75rem;color:var(--red)">
      <strong>Admin Note:</strong> {{ $gr->admin_note }}
    </div>
    @endif

    @if($gr->status === 'approved')
    <div style="margin-top:12px;padding:10px 12px;background:var(--g100);border-radius:8px;font-size:.75rem;color:var(--g700)">
      <i class="fa-solid fa-circle-info"></i> Your request is approved! Please visit the IT Center to proceed.
    </div>
    @endif
  </div>
  @endif

  <a href="{{ route('public.request') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Back to Request Form
  </a>
</div>
</body>
</html>