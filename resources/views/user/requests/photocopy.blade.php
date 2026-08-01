@extends('user.requests._layout')
@php($isEditing = isset($serviceRequest) && $serviceRequest)
@section('title', $isEditing ? 'Edit Photocopy Request | IT Center' : 'Photocopy Request | IT Center')
@section('page-title', $isEditing ? 'Edit Photocopy Request' : 'Photocopy Service')
@section('page-sub', $isEditing ? 'Modify the details of your pending request' : 'Submit a photocopy request for your documents')

@section('request-content')

<div class="modal-bg" id="copyTerms">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-copy" style="color:var(--orange);margin-right:7px"></i>Photocopy — Terms &amp; Conditions</h3>
      <button class="modal-close" type="button" onclick="closeModal('copyTerms')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <h4>1. Document Submission</h4><p>Bring the original document to the IT Center. Any document type is accepted.</p>
      <h4>2. Copy Limits</h4><p>Your requested pages are counted against the configured daily photocopy limit.</p>
      <h4>3. Processing Time</h4><p>Typically completed within 10–20 minutes depending on queue.</p>
      <h4>4. Document Care</h4><p>The IT Center handles all documents with care but is not liable for pre-existing damage.</p>
      <h4>5. Claiming</h4><p>Requests not claimed within 24 hours may be cancelled.</p>
    </div>
    <div class="modal-footer">
      <button class="modal-btn primary" type="button" onclick="acceptTerms('copyTerms','terms_check')"><i class="fa-solid fa-check"></i> I Agree</button>
    </div>
  </div>
</div>

<div style="max-width:680px;margin:0 auto">
  @if($errors->any())
    <div class="abox err" style="margin-bottom:16px"><i class="fa-solid fa-triangle-exclamation"></i><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
  @endif

  @if(!$serviceAvailable)
    <div class="abox err" style="margin-bottom:16px"><i class="fa-solid fa-circle-xmark"></i><div><strong>Photocopy service is currently unavailable.</strong><br>Please review the User Manual or Infographics and check again later, or contact the IT Center Services Desk.</div></div>
  @endif

  @if($isEditing)
    <div class="abox warn" style="margin-bottom:16px">
      <i class="fa-solid fa-pen-to-square"></i>
      <div><strong>Editing {{ $serviceRequest->request_number }}</strong><br>You may change this request only while its status is Pending. The daily limit below excludes the request's previous copy count.</div>
    </div>
  @endif

  <div style="background:var(--white);border-radius:16px;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200);overflow:hidden">
    <div style="background:linear-gradient(135deg,var(--orange),#f57c00);padding:18px 22px;display:flex;align-items:center;gap:12px">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;flex-shrink:0"><i class="fa-solid {{ $isEditing ? 'fa-pen-to-square' : 'fa-copy' }}"></i></div>
      <div>
        <div style="font-size:.95rem;font-weight:800;color:#fff">{{ $isEditing ? 'Update Photocopy Request' : 'Photocopy Request' }}</div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.75)">{{ $isEditing ? 'Review and save the corrected request details' : 'Bring your original document to the IT Center' }}</div>
      </div>
    </div>

    <form action="{{ $isEditing ? route('requests.photocopy.update', $serviceRequest) : route('requests.photocopy.store') }}" method="POST" style="padding:20px" data-loading-message="{{ $isEditing ? 'Updating photocopy request...' : 'Submitting photocopy request...' }}">
      @csrf
      @if($isEditing) @method('PUT') @endif

      <div class="abox info" style="margin-bottom:15px">
        <i class="fa-solid fa-chart-simple"></i>
        <div><strong>Daily photocopy usage:</strong> {{ $pagesUsed }} of {{ $pageLimit }} page(s) used. You may request up to <strong>{{ $pagesRemaining }}</strong> page(s) in this form.</div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-expand" style="color:var(--orange)"></i> Paper Size <span style="color:var(--red)">*</span></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">
          @foreach($paperSizes as $ps)
          @php($selectedPaper = old('paper_size', $isEditing ? $serviceRequest->paper_size : null))
          <label style="cursor:pointer{{ $ps->stock<=0 && $selectedPaper !== $ps->value ? ' opacity:.5;pointer-events:none' : '' }}">
            <input type="radio" name="paper_size" value="{{ $ps->value }}" style="display:none"
              {{ $selectedPaper==$ps->value?'checked':'' }}
              {{ $ps->stock<=0 && $selectedPaper !== $ps->value ? 'disabled' : '' }} required>
            <div class="copy-opt" style="border:1.5px solid var(--gray200);border-radius:10px;padding:10px 8px;text-align:center;background:var(--white);transition:all .2s">
              <div style="font-size:.82rem;font-weight:700">{{ explode(' ',$ps->name)[0] }}</div>
              <div style="font-size:.63rem;color:var(--gray400);margin-top:2px">{{ Str::after($ps->name,' ') }}</div>
              <div style="margin-top:5px"><span class="tag {{ $ps->stock>50?'tag-active':($ps->stock>0?'tag-pend':'tag-rej') }}" style="font-size:.6rem">{{ $ps->stock>0 ? $ps->stock.' left' : ($selectedPaper === $ps->value ? 'Current selection' : 'Out of stock') }}</span></div>
            </div>
          </label>
          @endforeach
        </div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-hashtag" style="color:var(--orange)"></i> Number of Copies <span style="color:var(--red)">*</span></div>
        <input type="number" name="copies" class="fc" min="1" max="{{ min(100, max(1,$pagesRemaining)) }}" value="{{ old('copies', $isEditing ? $serviceRequest->copies : 1) }}" required>
        <div style="font-size:.66rem;color:var(--gray400);margin-top:5px">Maximum allowed for this request: {{ min(100,$pagesRemaining) }} page(s).</div>
      </div>

      <div class="fg">
        <div class="flabel"><i class="fa-solid fa-pen-to-square" style="color:var(--orange)"></i> Purpose <span style="color:var(--red)">*</span></div>
        <textarea name="purpose" class="fc" rows="3" placeholder="State the purpose of your photocopy request..." required style="resize:vertical">{{ old('purpose', $isEditing ? $serviceRequest->purpose : '') }}</textarea>
      </div>

      <div class="abox info" style="margin-bottom:14px"><i class="fa-solid fa-circle-info"></i><div>Please bring your <strong>original document</strong> when you visit the IT Center.</div></div>

      <div style="background:var(--gray100);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px">
        <input type="checkbox" id="terms_check" name="terms" value="1" style="width:16px;height:16px;accent-color:var(--orange);cursor:pointer;flex-shrink:0" required {{ old('terms')?'checked':'' }}>
        <label for="terms_check" style="font-size:.76rem;color:var(--gray600);cursor:pointer;line-height:1.4">I have read and agree to the <a href="#" onclick="openModal('copyTerms');return false;" style="color:var(--orange);font-weight:700">Photocopy Terms &amp; Conditions</a></label>
      </div>

      <div style="display:flex;gap:9px;flex-wrap:wrap">
        @if($isEditing)
          <a href="{{ route('requests.history') }}" class="btn-outline" style="flex:1;text-decoration:none;justify-content:center"><i class="fa-solid fa-arrow-left"></i> Cancel Edit</a>
        @endif
        <button type="submit" class="btn" style="flex:2;background:linear-gradient(135deg,var(--orange),#f57c00);{{ !$serviceAvailable ? 'opacity:.55;cursor:not-allowed' : '' }}" {{ !$serviceAvailable ? 'disabled' : '' }}>
          <i class="fa-solid {{ $isEditing ? 'fa-floppy-disk' : 'fa-paper-plane' }}"></i> {{ $isEditing ? 'Save Photocopy Changes' : 'Submit Photocopy Request' }}
        </button>
      </div>
    </form>
  </div>
</div>

@push('styles')
<style>
input[type=radio]:checked+.copy-opt{border-color:var(--orange)!important;background:var(--orange-bg)!important}.copy-opt:hover{border-color:var(--orange)!important}
</style>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id)?.classList.add('open')}
function closeModal(id){document.getElementById(id)?.classList.remove('open')}
function acceptTerms(m,c){document.getElementById(c).checked=true;closeModal(m)}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
</script>
@endpush
@endsection
