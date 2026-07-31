@extends('user.requests._layout')
@php($isEdit = isset($serviceRequest) && $serviceRequest)
@section('title', $isEdit ? 'Edit Printing Request | IT Center' : 'Printing Request | IT Center')
@section('page-title', $isEdit ? 'Edit Printing Request' : 'Printing Service')
@section('page-sub', $isEdit ? 'Update your pending printing request before it is approved' : 'Submit a document or photo printing request')

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

<!-- SUBMISSION CONFIRMATION MODAL -->
<div class="modal-bg" id="printingConfirmModal">
  <div class="modal-box" style="max-width:600px">
    <div class="modal-hd">
      <h3>
        <i class="fa-solid fa-circle-check" style="color:var(--blue);margin-right:7px"></i>
        {{ $isEdit ? 'Confirm Printing Request Changes' : 'Confirm Printing Request' }}
      </h3>
      <button type="button" class="modal-close" onclick="closeModal('printingConfirmModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="padding:18px 22px">
      <div class="abox info" style="margin-bottom:14px">
        <i class="fa-solid fa-circle-info"></i>
        <div>Please review the uploaded file and printing details. Select <strong>Modify Details</strong> if anything is incorrect.</div>
      </div>
      <div id="printing-confirm-summary" class="confirm-grid"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="modal-btn secondary" onclick="closeModal('printingConfirmModal')">
        <i class="fa-solid fa-pen-to-square"></i> Modify Details
      </button>
      <button type="button" class="modal-btn primary" id="confirm-printing-submit" onclick="confirmPrintingSubmission()">
        <i class="fa-solid fa-paper-plane"></i> {{ $isEdit ? 'Save Changes' : 'Confirm & Submit' }}
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

    @if($isEdit)
    <div style="margin:18px 20px 0;background:#fff8e1;border-left:3px solid #f5a623;border-radius:8px;padding:10px 13px;font-size:.76rem;color:#7a5200;display:flex;gap:8px;align-items:flex-start">
      <i class="fa-solid fa-lock-open" style="margin-top:2px"></i>
      <div>This request is still <strong>Pending</strong>, so you may revise its file and details. Once approved or processed, editing will be locked.</div>
    </div>
    @endif

    <form id="printingForm"
      action="{{ $isEdit ? route('requests.printing.update', $serviceRequest) : route('requests.printing.store') }}"
      method="POST" enctype="multipart/form-data" style="padding:20px"
      onsubmit="handlePrintingSubmit(event)">
      @csrf
      @if($isEdit) @method('PUT') @endif
      <input type="hidden" name="submission_confirmed" id="submission_confirmed" value="0">

      <!-- File Upload — replace the existing drop zone section -->
      <div class="fg">
        <div class="flabel">
          <i class="fa-solid fa-file-arrow-up" style="color:var(--blue)"></i>
          {{ $isEdit ? 'Replace Uploaded File' : 'Upload File' }}
          @if(!$isEdit)<span style="color:var(--red)">*</span>@else<span style="font-size:.65rem;color:var(--gray400);font-weight:600">(Optional)</span>@endif
        </div>
        <div id="drop-zone" onclick="document.getElementById('file-input').click()"
          style="border:2px dashed var(--gray300);border-radius:10px;padding:22px 16px;
                text-align:center;cursor:pointer;background:var(--gray100);transition:all .2s">
          <div id="drop-icon" style="font-size:1.8rem;color:var(--gray400);margin-bottom:6px">
            <i class="fa-solid fa-cloud-arrow-up"></i>
          </div>
          <div id="drop-text" style="font-size:.8rem;font-weight:700;color:var(--gray700)">
            {{ $isEdit ? 'Click to replace the current file or drag & drop' : 'Click to browse or drag & drop' }}
          </div>
          <div style="font-size:.68rem;color:var(--gray400);margin-top:3px">
            PDF, DOC, DOCX, JPG, PNG · Max 10MB
          </div>
          <div id="file-preview"
              style="display:{{ $isEdit ? 'flex' : 'none' }};margin-top:10px;padding:8px 12px;
                      background:var(--blue-bg);border-radius:8px;
                      align-items:center;gap:8px">
            <i class="fa-solid fa-file" style="color:var(--blue)"></i>
            <div style="text-align:left">
              <div id="file-name-disp" style="font-size:.76rem;font-weight:700;color:var(--blue)">{{ $isEdit ? $serviceRequest->file_name : '' }}</div>
              <div id="file-size-disp" style="font-size:.65rem;color:var(--gray400)">{{ $isEdit ? 'Current uploaded file — choose another file to replace it' : '' }}</div>
            </div>
          </div>
        </div>
        <input type="file" id="file-input" name="file"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
              style="display:none" onchange="handleFile(this)" {{ $isEdit ? '' : 'required' }}>

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
                style="display:none;font-size:.75rem;color:var(--gray400);align-items:center;gap:6px">
              <i class="fa-solid fa-spinner fa-spin"></i> Detecting pages...
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="detected_pages" id="detected_pages_input" value="{{ old('detected_pages', $isEdit ? $serviceRequest->detected_pages : '') }}">

      <!-- Paper Size -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-expand" style="color:var(--blue)"></i> Paper Size <span style="color:var(--red)">*</span></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">
          @foreach($paperSizes as $ps)
          <label style="cursor:pointer{{ $ps->stock<=0?' opacity:.5;pointer-events:none':'' }}">
            <input type="radio" name="paper_size" value="{{ $ps->value }}" style="display:none"
              {{ old('paper_size', $isEdit ? $serviceRequest->paper_size : null)==$ps->value?'checked':'' }}
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
              <input type="radio" name="print_type" value="black_white" style="display:none" required {{ old('print_type', $isEdit ? $serviceRequest->print_type : 'black_white')==='black_white'?'checked':'' }}>
              <div class="type-opt">
                <i class="fa-solid fa-circle-half-stroke" style="font-size:1rem;margin-bottom:4px"></i>
                <div style="font-size:.72rem;font-weight:700">B&W</div>
              </div>
            </label>
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="print_type" value="colored" style="display:none" {{ old('print_type', $isEdit ? $serviceRequest->print_type : null)==='colored'?'checked':'' }}>
              <div class="type-opt">
                <i class="fa-solid fa-droplet" style="font-size:1rem;margin-bottom:4px;color:#e53935"></i>
                <div style="font-size:.72rem;font-weight:700">Colored</div>
              </div>
            </label>
          </div>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-hashtag" style="color:var(--blue)"></i> Number of Copies <span style="color:var(--red)">*</span></div>
          <input type="number" name="copies" class="fc" min="1" max="100" value="{{ old('copies', $isEdit ? $serviceRequest->copies : 1) }}" required>
        </div>
      </div>

      <!-- Purpose -->
      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-pen-to-square" style="color:var(--blue)"></i> Purpose <span style="color:var(--red)">*</span></div>
        <textarea name="purpose" class="fc" rows="3" placeholder="State the purpose (e.g. thesis, assignment, report)..." required style="resize:vertical">{{ old('purpose', $isEdit ? $serviceRequest->purpose : '') }}</textarea>
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
        <i class="fa-solid fa-clipboard-check"></i> {{ $isEdit ? 'Review Changes' : 'Review Printing Request' }}
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
.confirm-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.confirm-item{background:var(--gray100);border:1px solid var(--gray200);border-radius:9px;padding:10px 12px;min-width:0}
.confirm-item.full{grid-column:1/-1}
.confirm-label{font-size:.62rem;color:var(--gray400);font-weight:800;text-transform:uppercase;letter-spacing:.03em;margin-bottom:3px}
.confirm-value{font-size:.79rem;color:var(--gray800);font-weight:700;line-height:1.45;overflow-wrap:anywhere}
@media(max-width:600px){.g2{grid-template-columns:1fr}}
@media(max-width:520px){.confirm-grid{grid-template-columns:1fr}.confirm-item.full{grid-column:auto}}
</style>
@endpush

@push('scripts')
<script>
const printingForm = document.getElementById('printingForm');
const existingFileName = @json($isEdit ? $serviceRequest->file_name : null);
let printingConfirmed = false;
let pageDetectionPromise = Promise.resolve();

// Page detection via AJAX
async function detectPages(file) {
  const info    = document.getElementById('page-detection-info');
  const loading = document.getElementById('page-loading');
  const pcText  = document.getElementById('page-count-text');
  const scText  = document.getElementById('sheet-count-text');

  info.style.display    = 'block';
  loading.style.display = 'flex';
  pcText.style.color    = 'var(--g700)';
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
    if (!res.ok) throw new Error('Page detection failed.');
    const data = await res.json();
    loading.style.display = 'none';

    if (data.pages && data.pages > 0) {
      document.getElementById('detected_pages_input').value = data.pages;
      pcText.textContent = `📄 Detected ${data.pages} page${data.pages>1?'s':''} in this file`;
      updateSheetCount();
    } else {
      document.getElementById('detected_pages_input').value = '';
      pcText.textContent = '⚠️ Could not detect pages — sheet count will equal copies.';
      pcText.style.color = 'var(--orange)';
    }
  } catch(e) {
    document.getElementById('detected_pages_input').value = '';
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
    pageDetectionPromise = detectPages(f);
  } else {
    // Images = 1 page
    pageDetectionPromise = Promise.resolve();
    document.getElementById('detected_pages_input').value = 1;
    const info = document.getElementById('page-detection-info');
    info.style.display = 'block';
    document.getElementById('page-count-text').textContent = '📄 Image file — 1 page detected';
    document.getElementById('sheet-count-text').textContent = '';
    updateSheetCount();
  }
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function confirmationItem(label, value, full = false) {
  return `<div class="confirm-item${full ? ' full' : ''}">
    <div class="confirm-label">${escapeHtml(label)}</div>
    <div class="confirm-value">${escapeHtml(value || '—')}</div>
  </div>`;
}

function buildPrintingConfirmation() {
  const fileInput = document.getElementById('file-input');
  const selectedFile = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
  const fileName = selectedFile ? selectedFile.name : existingFileName;
  const paperSize = document.querySelector('input[name="paper_size"]:checked')?.value || '';
  const printType = document.querySelector('input[name="print_type"]:checked')?.value || '';
  const copies = parseInt(document.querySelector('[name="copies"]')?.value || '1', 10);
  const pages = parseInt(document.getElementById('detected_pages_input').value || '0', 10);
  const totalSheets = (pages > 0 ? pages : 1) * copies;
  const purpose = document.querySelector('[name="purpose"]')?.value.trim() || '';

  let html = '';
  html += confirmationItem('Uploaded File', fileName, true);
  html += confirmationItem('Paper Size', paperSize.toUpperCase());
  html += confirmationItem('Print Type', printType === 'black_white' ? 'Black & White' : 'Colored');
  html += confirmationItem('Copies', copies);
  html += confirmationItem('Detected Pages', pages > 0 ? pages : 'Not detected');
  html += confirmationItem('Estimated Sheets', totalSheets);
  html += confirmationItem('Purpose', purpose, true);

  document.getElementById('printing-confirm-summary').innerHTML = html;
}

async function handlePrintingSubmit(event) {
  if (printingConfirmed) return true;

  event.preventDefault();
  if (!printingForm.checkValidity()) {
    printingForm.reportValidity();
    return false;
  }

  await pageDetectionPromise;
  buildPrintingConfirmation();
  openModal('printingConfirmModal');
  return false;
}

function confirmPrintingSubmission() {
  const button = document.getElementById('confirm-printing-submit');
  printingConfirmed = true;
  document.getElementById('submission_confirmed').value = '1';
  button.disabled = true;
  button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
  printingForm.requestSubmit();
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

document.addEventListener('DOMContentLoaded', () => {
  const pages = parseInt(document.getElementById('detected_pages_input').value || '0', 10);
  if (pages > 0 && existingFileName) {
    document.getElementById('page-detection-info').style.display = 'block';
    document.getElementById('page-count-text').textContent = `📄 Current file has ${pages} detected page${pages > 1 ? 's' : ''}`;
    updateSheetCount();
  }
});
</script>
@endpush
@endsection