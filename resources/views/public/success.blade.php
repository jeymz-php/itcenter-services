<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Submitted | UCC IT Center Services</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--g700:#18633f;--g500:#249660;--g100:#e4f7ef;--g50:#f2fbf7;--white:#fff;--gray200:#dde6e2;--gray300:#c5d5cf;--gray400:#8aa89f;--gray600:#4d6b61;--gray800:#1e3530;--red:#e53e3e;--red-bg:#fff5f5;--orange:#f5a623;--shadow-md:0 4px 18px rgba(10,51,35,.13)}
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

/* ── RATING MODAL — this page is standalone (no shared layout), so all of
   this has to be self-contained rather than reusing the main app's CSS ── */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(10,51,35,.55);align-items:center;justify-content:center;z-index:999;padding:20px}
.modal-bg.open{display:flex}
.modal-box{background:var(--white);border-radius:16px;max-width:480px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-md);text-align:left}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--gray200)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--gray800)}
.modal-close{background:none;border:none;font-size:1rem;color:var(--gray400);cursor:pointer;padding:4px}
.modal-body{padding:20px 22px}
.modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px;border-top:1px solid var(--gray200)}
.modal-btn{padding:10px 18px;border-radius:9px;font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;border:none}
.modal-btn.secondary{background:var(--g100);color:var(--g700)}
.modal-btn.primary{background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff}
.rate-fg{margin-bottom:16px}
.rate-flabel{font-size:.76rem;font-weight:700;color:var(--gray800);margin-bottom:8px}
.rate-fc{width:100%;padding:10px 12px;border:1.5px solid var(--gray200);border-radius:8px;font-family:inherit;font-size:.8rem;resize:vertical}
.rate-info-box{background:var(--g50);border-left:3px solid var(--g500);border-radius:8px;padding:12px 14px;font-size:.78rem;color:var(--gray800);margin-bottom:16px;display:flex;gap:8px}
.rate-radio-row{display:flex;align-items:flex-start;gap:8px;padding:10px 12px;border:1.5px solid var(--gray200);border-radius:10px;margin-bottom:8px;cursor:pointer}
.rate-radio-row .rt{font-size:.8rem;font-weight:700;color:var(--gray800)}
.rate-radio-row .rs{font-size:.68rem;color:var(--gray400)}
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

{{-- REVIEW RATINGS MODAL — auto-opens right after this success page loads --}}
<div class="modal-bg" id="guestRatingModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-star" style="color:var(--orange);margin-right:6px"></i>Rate Your Experience</h3>
      <button class="modal-close" onclick="closeModal('guestRatingModal')" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('public.request.ratings') }}" method="POST">
      @csrf
      <input type="hidden" name="guest_request_id" value="{{ $gr->id }}">
      <input type="hidden" name="stars" id="guest-stars-input" value="0">
      <div class="modal-body">

        <div class="rate-info-box">
          <i class="fa-solid fa-circle-info"></i>
          <div>Your request <strong>{{ $gr->request_number }}</strong> ({{ ucfirst($gr->service_type) }}) has been submitted. We'd love your feedback!</div>
        </div>

        <div class="rate-fg">
          <div class="rate-flabel">Show My Details</div>
          <label class="rate-radio-row">
            <input type="radio" name="visibility" value="public" checked>
            <div>
              <div class="rt">Show my Name, ID Number &amp; Campus publicly</div>
              <div class="rs">Your full details will be visible with this review.</div>
            </div>
          </label>
          <label class="rate-radio-row">
            <input type="radio" name="visibility" value="anonymous">
            <div>
              <div class="rt">Make me Anonymous</div>
              <div class="rs">Only part of your first name and last 4 ID digits will show. Your campus still shows.</div>
            </div>
          </label>
        </div>

        <div class="rate-fg">
          <div class="rate-flabel">How would you rate your overall experience submitting this request — from start to finish?</div>
          <div id="guest-star-selector" style="display:flex;gap:6px;font-size:1.8rem">
            @for($i=1;$i<=5;$i++)
            <i class="fa-regular fa-star guest-star-icon" data-value="{{ $i }}" style="cursor:pointer;color:var(--gray300)"></i>
            @endfor
          </div>
        </div>

        <div class="rate-fg">
          <div class="rate-flabel">Comment</div>
          <textarea name="comment" class="rate-fc" rows="3" placeholder="Tell us more about your experience..."></textarea>
        </div>

        <div class="rate-fg" style="margin-bottom:0">
          <div class="rate-flabel">Suggestions / Questions <span style="color:var(--gray400);font-weight:400">(about the IT Center Services System)</span></div>
          <textarea name="suggestions" class="rate-fc" rows="3" placeholder="Any suggestions or questions for the IT Center?"></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('guestRatingModal')">Maybe Later</button>
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-paper-plane"></i> Submit Feedback</button>
      </div>
    </form>
  </div>
</div>

<script>
// This page is standalone (no shared layout), so openModal/closeModal are
// defined right here rather than relying on the main app's global copy.
function openModal(id){ const el=document.getElementById(id); if(el) el.classList.add('open'); }
function closeModal(id){ const el=document.getElementById(id); if(el) el.classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(m=>{
  m.addEventListener('click', e=>{ if(e.target===m) m.classList.remove('open'); });
});

document.addEventListener('DOMContentLoaded', () => openModal('guestRatingModal'));

document.querySelectorAll('#guest-star-selector .guest-star-icon').forEach(star => {
  star.addEventListener('click', () => {
    const value = parseInt(star.dataset.value, 10);
    document.getElementById('guest-stars-input').value = value;
    document.querySelectorAll('#guest-star-selector .guest-star-icon').forEach(s => {
      const v = parseInt(s.dataset.value, 10);
      s.className = v <= value ? 'fa-solid fa-star guest-star-icon' : 'fa-regular fa-star guest-star-icon';
      s.style.color = v <= value ? 'var(--orange)' : 'var(--gray300)';
    });
  });
});

document.querySelector('#guestRatingModal form')?.addEventListener('submit', (e) => {
  if (parseInt(document.getElementById('guest-stars-input').value, 10) < 1) {
    e.preventDefault();
    alert('Please select a star rating before submitting.');
  }
});
</script>
</body>
</html>