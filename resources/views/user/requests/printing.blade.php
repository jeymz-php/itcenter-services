@extends('user.requests._layout')
@section('title','Printing Request | IT Center')
@section('page-title','Printing Service')
@section('page-sub','Submit a document or photo printing request')

@section('request-content')

<!-- TERMS MODAL -->
<div class="modal-bg" id="printTerms">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-print" style="color:var(--blue);margin-right:7px"></i>Printing — Terms & Conditions</h3>
      <button class="modal-close" onclick="closeModal('printTerms')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <h4>1. Accepted File Formats</h4>
      <p>PDF, DOC, DOCX, JPG, JPEG, PNG only. Maximum file size is 10MB.</p>
      <h4>2. Printing Limits</h4>
      <p>Maximum of 100 copies per request.</p>
      <h4>3. Processing Time</h4>
      <p>Requests are processed on a first-come, first-served basis. Usually 15–30 minutes.</p>
      <h4>4. Prohibited Content</h4>
      <p>Copyrighted materials, offensive content, or materials unrelated to academic purposes are prohibited.</p>
      <h4>5. Responsibility</h4>
      <p>Please proofread before submitting. The IT Center is not responsible for content errors.</p>
      <h4>6. Claiming</h4>
      <p>Requests not claimed within 24 hours may be cancelled.</p>
    </div>
    <div class="modal-footer">
      <button class="modal-btn primary" onclick="acceptTerms('printTerms','terms_check')">
        <i class="fa-solid fa-check"></i> I Agree
      </button>
    </div>
  </div>
</div>

<div style="max-width:680px;margin:0 auto">
  @if($errors->any())
    <div class="abox err" style="margin-bottom:16px">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
  @endif

  <div style="background:var(--white);border-radius:16px;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200);overflow:hidden">

    <div style="background:linear-gradient(135deg,var(--blue),#1976d2);padding:18px 22px;display:flex;align-items:center;gap:12px">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;flex-shrink:0">
        <i class="fa-solid fa-print"></i>
      </div>
      <div>
        <div style="font-size:.95rem;font-weight:800;color:#fff">Printing Request</div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.75)">Upload your file and configure options</div>
      </div>
    </div>

    <form action="{{ route('requests.printing.store') }}" method="POST" enctype="multipart/form-data" style="padding:20px">
      @csrf

      <!-- File Upload — replace the existing drop zone section -->
      <div class="fg">
        <div class="flabel">
          <i class="fa-solid fa-file-arrow-up" style="color:var(--blue)"></i>
          Upload File <span style="color:var(--red)">*</span>
        </div>
        <div id="drop-zone" onclick="document.getElementById('file-input').click()"
          style="border:2px dashed var(--gray300);border-radius:10px;padding:22px 16px;
                text-align:center;cursor:pointer;background:var(--gray100);transition:all .2s">
          <div id="drop-icon" style="font-size:1.8rem;color:var(--gray400);margin-bottom:6px">
            <i class="fa-solid fa-cloud-arrow-up"></i>
          </div>
          <div id="drop-text" style="font-size:.8rem;font-weight:700;color:var(--gray700)">
            Click to browse or drag & drop
          </div>
          <div style="font-size:.68rem;color:var(--gray400);margin-top:3px">
            PDF, DOC, DOCX, JPG, PNG · Max 10MB
          </div>
          <div id="file-preview"
              style="display:none;margin-top:10px;padding:8px 12px;
                      background:var(--blue-bg);border-radius:8px;
                      align-items:center;gap:8px">
            <i class="fa-solid fa-file" style="color:var(--blue)"></i>
            <div style="text-align:left">
              <div id="file-name-disp" style="font-size:.76rem;font-weight:700;color:var(--blue)"></div>
              <div id="file-size-disp" style="font-size:.65rem;color:var(--gray400)"></div>
            </div>
          </div>
        </div>
        <input type="file" id="file-input" name="file"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
              style="display:none" onchange="handleFile(this)" required>

        {{-- Page detection result shown after upload --}}
        <div id="page-detection-info" style="display:none;margin-top:8px">
          <div style="background:var(--g100);border-radius:9px;padding:10px 14px;
                      display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <i class="fa-solid fa-file-lines" style="color:var(--g600);font-size:1rem"></i>
            <div>
              <div id="page-count-text"
                  style="font-size:.8rem;font-weight:700;color:var(--g700)"></div>
              <div id="sheet-count-text"
                  style="font-size:.72rem;color:var(--gray600);margin-top:1px"></div>
            </div>
            <div id="page-loading"
                style="display:none;font-size:.75rem;color:var(--gray400);
                        display:flex;align-items:center;gap:6px">
              <i class="fa-solid fa-spinner fa-spin"></i> Detecting pages...
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="detected_pages" id="detected_pages_input" value="">

      <!-- Paper Size -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-expand" style="color:var(--blue)"></i> Paper Size <span style="color:var(--red)">*</span></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">
          @foreach($paperSizes as $ps)
          <label style="cursor:pointer{{ $ps->stock<=0?' opacity:.5;pointer-events:none':'' }}">
            <input type="radio" name="paper_size" value="{{ $ps->value }}" style="display:none"
              {{ old('paper_size')==$ps->value?'checked':'' }}
              {{ $ps->stock<=0?'disabled':'' }} required>
            <div class="size-opt" style="padding:10px 8px">
              <div style="font-size:.82rem;font-weight:700">{{ explode(' ',$ps->name)[0] }}</div>
              <div style="font-size:.63rem;color:var(--gray400);margin-top:2px">{{ Str::after($ps->name,' ') }}</div>
              <div style="margin-top:5px">
                <span class="tag {{ $ps->stock>50?'tag-active':($ps->stock>0?'tag-pend':'tag-rej') }}" style="font-size:.6rem">
                  {{ $ps->stock>0 ? $ps->stock.' left' : 'Out of stock' }}
                </span>
              </div>
            </div>
          </label>
          @endforeach
        </div>
      </div>

      <!-- Print Type & Copies -->
      <div class="g2">
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-palette" style="color:var(--blue)"></i> Print Type <span style="color:var(--red)">*</span></div>
          <div style="display:flex;gap:8px">
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="print_type" value="black_white" style="display:none" required {{ old('print_type','black_white')==='black_white'?'checked':'' }}>
              <div class="type-opt">
                <i class="fa-solid fa-circle-half-stroke" style="font-size:1rem;margin-bottom:4px"></i>
                <div style="font-size:.72rem;font-weight:700">B&W</div>
              </div>
            </label>
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="print_type" value="colored" style="display:none" {{ old('print_type')==='colored'?'checked':'' }}>
              <div class="type-opt">
                <i class="fa-solid fa-droplet" style="font-size:1rem;margin-bottom:4px;color:#e53935"></i>
                <div style="font-size:.72rem;font-weight:700">Colored</div>
              </div>
            </label>
          </div>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-hashtag" style="color:var(--blue)"></i> Number of Copies <span style="color:var(--red)">*</span></div>
          <input type="number" name="copies" class="fc" min="1" max="100" value="{{ old('copies',1) }}" required>
        </div>
      </div>

      <!-- Purpose -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-pen-to-square" style="color:var(--blue)"></i> Purpose <span style="color:var(--red)">*</span></div>
        <textarea name="purpose" class="fc" rows="3" placeholder="State the purpose (e.g. thesis, assignment, report)..." required style="resize:vertical">{{ old('purpose') }}</textarea>
      </div>

      <!-- Terms -->
      <div style="background:var(--gray100);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px">
        <input type="checkbox" id="terms_check" name="terms" value="1" style="width:16px;height:16px;accent-color:var(--blue);cursor:pointer;flex-shrink:0" required {{ old('terms')?'checked':'' }}>
        <label for="terms_check" style="font-size:.76rem;color:var(--gray600);cursor:pointer;line-height:1.4">
          I have read and agree to the
          <a href="#" onclick="openModal('printTerms');return false;" style="color:var(--blue);font-weight:700">Printing Terms & Conditions</a>
        </label>
      </div>

      <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--blue),#1976d2)">
        <i class="fa-solid fa-paper-plane"></i> Submit Printing Request
      </button>
    </form>
  </div>
</div>

@push('styles')
<style>
.size-opt{border:1.5px solid var(--gray200);border-radius:10px;text-align:center;background:var(--white);transition:all .2s}
input[type=radio]:checked+.size-opt{border-color:var(--blue);background:var(--blue-bg)}
.size-opt:hover{border-color:var(--blue)}
.type-opt{border:1.5px solid var(--gray200);border-radius:10px;padding:10px;text-align:center;background:var(--white);transition:all .2s}
input[type=radio]:checked+.type-opt{border-color:var(--blue);background:var(--blue-bg)}
.type-opt:hover{border-color:var(--blue)}
#drop-zone:hover,#drop-zone.drag-over{border-color:var(--blue);background:var(--blue-bg)}
@media(max-width:600px){.g2{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
// Page detection via AJAX
async function detectPages(file) {
  const info    = document.getElementById('page-detection-info');
  const loading = document.getElementById('page-loading');
  const pcText  = document.getElementById('page-count-text');
  const scText  = document.getElementById('sheet-count-text');

  info.style.display    = 'block';
  loading.style.display = 'flex';
  pcText.textContent    = '';
  scText.textContent    = '';

  const formData = new FormData();
  formData.append('file', file);
  formData.append('_token', '{{ csrf_token() }}');

  try {
    const res  = await fetch('{{ route("requests.detect-pages") }}', {
      method: 'POST',
      body:   formData,
    });
    const data = await res.json();
    loading.style.display = 'none';

    if (data.pages && data.pages > 0) {
      document.getElementById('detected_pages_input').value = data.pages;
      pcText.textContent = `📄 Detected ${data.pages} page${data.pages>1?'s':''} in this file`;
      updateSheetCount();
    } else {
      pcText.textContent = '⚠️ Could not detect pages — sheet count will equal copies.';
      pcText.style.color = 'var(--orange)';
    }
  } catch(e) {
    loading.style.display = 'none';
    pcText.textContent = '⚠️ Page detection unavailable.';
    pcText.style.color = 'var(--orange)';
  }
}

function updateSheetCount() {
  const pages  = parseInt(document.getElementById('detected_pages_input').value) || 0;
  const copies = parseInt(document.querySelector('[name=copies]')?.value) || 1;
  const scText = document.getElementById('sheet-count-text');
  if (pages > 0 && scText) {
    const total = pages * copies;
    scText.textContent = `${pages} pages × ${copies} copies = ${total} sheets of paper will be used`;
  }
}

// Update sheet count when copies changes
document.querySelector('[name=copies]')?.addEventListener('input', updateSheetCount);

function handleFile(input) {
  if (!input.files || !input.files[0]) return;
  const f = input.files[0];
  document.getElementById('file-name-disp').textContent = f.name;
  document.getElementById('file-size-disp').textContent = (f.size/1024/1024).toFixed(2)+' MB';
  const fp = document.getElementById('file-preview');
  fp.style.display = 'flex';
  document.getElementById('drop-text').textContent = 'File selected:';
  document.getElementById('drop-icon').innerHTML =
    '<i class="fa-solid fa-file-circle-check" style="color:var(--blue)"></i>';

  // Trigger page detection
  const ext = f.name.split('.').pop().toLowerCase();
  if (['pdf','doc','docx'].includes(ext)) {
    detectPages(f);
  } else {
    // Images = 1 page
    document.getElementById('detected_pages_input').value = 1;
    const info = document.getElementById('page-detection-info');
    info.style.display = 'block';
    document.getElementById('page-count-text').textContent = '📄 Image file — 1 page detected';
    document.getElementById('sheet-count-text').textContent = '';
    updateSheetCount();
  }
}

const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.classList.remove('drag-over');
  if (e.dataTransfer.files.length) {
    document.getElementById('file-input').files = e.dataTransfer.files;
    handleFile(document.getElementById('file-input'));
  }
});

function openModal(id) { document.getElementById(id).classList.add('open') }
function closeModal(id) { document.getElementById(id).classList.remove('open') }
function acceptTerms(m,c) { document.getElementById(c).checked=true; closeModal(m) }
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{
  if(e.target===m) m.classList.remove('open')
}));
</script>
@endpush
@endsection