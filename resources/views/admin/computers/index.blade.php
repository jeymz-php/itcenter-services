@extends('layouts.app')
@section('title','Computers | Admin')
@section('body-class','dash-page')
@section('content')

{{-- ADD COMPUTER MODAL --}}
<div class="modal-bg" id="addPcModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-computer" style="color:var(--g600);margin-right:6px"></i>Add New Computer</h3>
      <button class="modal-close" onclick="closeModal('addPcModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.computers.store') }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-computer"></i> PC Name / Label</div>
          <input type="text" name="name" class="fc" placeholder="e.g. PC-11" required>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-microchip"></i> Specifications (optional)</div>
          <input type="text" name="specs" class="fc" placeholder="e.g. Intel Core i5, 8GB RAM, 256GB SSD">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('addPcModal')">Cancel</button>
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-plus"></i> Add Computer</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT COMPUTER MODAL --}}
<div class="modal-bg" id="editPcModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-pen" style="color:var(--g600);margin-right:6px"></i>Edit Computer</h3>
      <button class="modal-close" onclick="closeModal('editPcModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="editPcForm" method="POST">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-computer"></i> PC Name / Label</div>
          <input type="text" name="name" id="edit-pc-name" class="fc" required>
        </div>
        <div class="fg">
          <div class="flabel"><i class="fa-solid fa-microchip"></i> Specifications</div>
          <input type="text" name="specs" id="edit-pc-specs" class="fc" placeholder="e.g. Intel Core i5, 8GB RAM">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('editPcModal')">Cancel</button>
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- DEACTIVATE COMPUTER MODAL --}}
<div class="modal-bg" id="deactPcModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-ban" style="color:var(--red);margin-right:6px"></i>Deactivate Computer</h3>
      <button class="modal-close" onclick="closeModal('deactPcModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="deactPcForm" method="POST">
      @csrf
      <div class="modal-body">
        <div class="abox warn" style="margin-bottom:14px">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>This computer will be marked as deactivated and unavailable for use.</div>
        </div>
        <div class="fg">
          <div class="flabel">Reason for Deactivation <span style="color:var(--red)">*</span></div>
          <textarea name="note" class="fc" rows="3"
            placeholder="e.g. Under maintenance, hardware issue, needs repair..."
            required style="resize:vertical"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('deactPcModal')">Cancel</button>
        <button type="submit" class="modal-btn danger"><i class="fa-solid fa-ban"></i> Deactivate</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', [
      'title' => 'Computer Management',
      'sub'   => 'Manage lab PCs — availability, status, and details',
    ])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:16px">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
      @endif
      @if($errors->any())
        <div class="abox err" style="margin-bottom:16px">
          <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
        </div>
      @endif

      {{-- STATS ROW --}}
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px">
        <div class="stat-card" style="border-color:var(--g200)">
          <div class="stat-ico" style="background:var(--g100);color:var(--g700)">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <div>
            <div class="stat-lbl">Available</div>
            <div class="stat-val">{{ $computers->where('status','available')->count() }}</div>
          </div>
        </div>
        <div class="stat-card" style="border-color:var(--orange-bg)">
          <div class="stat-ico" style="background:var(--orange-bg);color:var(--orange)">
            <i class="fa-solid fa-spinner"></i>
          </div>
          <div>
            <div class="stat-lbl">In Use</div>
            <div class="stat-val">{{ $computers->where('status','in_use')->count() }}</div>
          </div>
        </div>
        <div class="stat-card" style="border-color:var(--red-bg)">
          <div class="stat-ico" style="background:var(--red-bg);color:var(--red)">
            <i class="fa-solid fa-ban"></i>
          </div>
          <div>
            <div class="stat-lbl">Deactivated</div>
            <div class="stat-val">{{ $computers->where('status','deactivated')->count() }}</div>
          </div>
        </div>
      </div>

      {{-- ADD BUTTON --}}
      <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
        <button onclick="openModal('addPcModal')"
          style="background:linear-gradient(135deg,var(--g700),var(--g500));color:#fff;border:none;border-radius:var(--rs);padding:9px 20px;font-size:.8rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:var(--shadow-sm)">
          <i class="fa-solid fa-plus"></i> Add Computer
        </button>
      </div>

      {{-- PC GRID --}}
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px">
        @forelse($computers as $pc)
        <div style="background:var(--white);border-radius:14px;
          border:2px solid {{ $pc->status==='available'?'var(--g300)':($pc->status==='in_use'?'var(--orange)':'var(--red)') }};
          box-shadow:var(--shadow-sm);overflow:hidden;transition:transform .2s,box-shadow .2s"
          onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(10,51,35,.14)'"
          onmouseout="this.style.transform='';this.style.boxShadow=''">

          {{-- Status strip --}}
          <div style="height:5px;background:{{ $pc->status==='available'?'var(--g400)':($pc->status==='in_use'?'var(--orange)':'var(--red)') }}"></div>

          <div style="padding:16px">
            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:42px;height:42px;border-radius:11px;flex-shrink:0;
                  background:{{ $pc->status==='available'?'var(--g100)':($pc->status==='in_use'?'var(--orange-bg)':'var(--red-bg)') }};
                  display:flex;align-items:center;justify-content:center;
                  color:{{ $pc->status==='available'?'var(--g700)':($pc->status==='in_use'?'var(--orange)':'var(--red)') }};
                  font-size:1.1rem">
                  <i class="fa-solid fa-computer"></i>
                </div>
                <div>
                  <div style="font-size:.95rem;font-weight:800;color:var(--gray800)">{{ $pc->name }}</div>
                  <span class="tag {{ $pc->status==='available'?'tag-active':($pc->status==='in_use'?'tag-pend':'tag-rej') }}" style="font-size:.62rem">
                    {{ strtoupper(str_replace('_',' ',$pc->status)) }}
                  </span>
                </div>
              </div>
            </div>

            {{-- Specs --}}
            <div style="font-size:.7rem;color:var(--gray400);margin-bottom:10px;line-height:1.5;min-height:32px">
              <i class="fa-solid fa-microchip" style="color:var(--g600);margin-right:4px"></i>
              {{ $pc->specs ?? 'No specs listed' }}
            </div>

            {{-- Active session info --}}
            @if($pc->status === 'in_use' && $pc->activeSession)
            <div style="background:var(--orange-bg);border-radius:8px;padding:9px 11px;margin-bottom:10px;font-size:.71rem">
              <div style="font-weight:700;color:var(--orange);margin-bottom:3px">
                <i class="fa-solid fa-user"></i> {{ $pc->activeSession->user->full_name }}
              </div>
              <div style="color:var(--gray600)">
                <i class="fa-solid fa-clock"></i>
                Ends: <strong>{{ $pc->activeSession->ends_at?->format('g:i A') }}</strong>
                ({{ $pc->activeSession->duration_minutes + $pc->activeSession->extended_minutes }}min total)
              </div>
            </div>
            @endif

            {{-- Deactivation note --}}
            @if($pc->status === 'deactivated' && $pc->deactivation_note)
            <div style="background:var(--red-bg);border-radius:8px;padding:9px 11px;margin-bottom:10px;font-size:.71rem">
              <div style="font-weight:700;color:var(--red);margin-bottom:2px">
                <i class="fa-solid fa-triangle-exclamation"></i> Deactivation Note
              </div>
              <div style="color:var(--gray600);line-height:1.4">{{ $pc->deactivation_note }}</div>
            </div>
            @endif

            {{-- Action Buttons --}}
            <div style="display:flex;gap:6px;flex-wrap:wrap;padding-top:10px;border-top:1px solid var(--gray100)">

              {{-- Edit --}}
              <button onclick="openEdit('{{ $pc->id }}','{{ addslashes($pc->name) }}','{{ addslashes($pc->specs??'') }}','{{ route('admin.computers.update',$pc) }}')"
                style="flex:1;min-width:60px;padding:7px 8px;border-radius:7px;border:1.5px solid var(--gray200);background:var(--white);color:var(--gray700);font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;transition:all .2s"
                onmouseover="this.style.borderColor='var(--g400)';this.style.color='var(--g700)'"
                onmouseout="this.style.borderColor='var(--gray200)';this.style.color='var(--gray700)'">
                <i class="fa-solid fa-pen"></i> Edit
              </button>

              @if($pc->status === 'deactivated')
                {{-- Activate --}}
                <form action="{{ route('admin.computers.activate',$pc) }}" method="POST" style="flex:1;min-width:60px">
                  @csrf
                  <button type="submit"
                    style="width:100%;padding:7px 8px;border-radius:7px;border:none;background:var(--g500);color:#fff;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px">
                    <i class="fa-solid fa-circle-check"></i> Activate
                  </button>
                </form>
              @elseif($pc->status !== 'in_use')
                {{-- Deactivate --}}
                <button onclick="openDeact('{{ route('admin.computers.deactivate',$pc) }}')"
                  style="flex:1;min-width:60px;padding:7px 8px;border-radius:7px;border:none;background:var(--red-bg);color:var(--red);font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px">
                  <i class="fa-solid fa-ban"></i> Deactivate
                </button>
              @endif

              @if($pc->status !== 'in_use')
                {{-- Delete --}}
                <form action="{{ route('admin.computers.destroy',$pc) }}" method="POST"
                  onsubmit="return confirm('Permanently delete {{ $pc->name }}?')" style="display:inline">
                  @csrf @method('DELETE')
                  <button type="submit"
                    style="width:32px;height:32px;border-radius:7px;border:none;background:var(--red-bg);color:var(--red);font-size:.75rem;cursor:pointer;display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              @else
                <div style="font-size:.65rem;color:var(--gray400);display:flex;align-items:center;padding:0 4px">
                  <i class="fa-solid fa-lock" style="margin-right:3px"></i> In use
                </div>
              @endif

            </div>
          </div>
        </div>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--gray400)">
          <i class="fa-solid fa-computer" style="font-size:2rem;display:block;margin-bottom:10px"></i>
          No computers added yet. Click "Add Computer" to get started.
        </div>
        @endforelse
      </div>

    </div>
  </main>
</div>

@push('scripts')
<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-bg').forEach(m=>
  m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open') })
);

function openDeact(url){
  document.getElementById('deactPcForm').setAttribute('action', url);
  openModal('deactPcModal');
}

function openEdit(id, name, specs, url){
  document.getElementById('edit-pc-name').value  = name;
  document.getElementById('edit-pc-specs').value = specs;
  document.getElementById('editPcForm').setAttribute('action', url);
  openModal('editPcModal');
}
</script>
@endpush
@endsection