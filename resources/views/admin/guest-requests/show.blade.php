@extends('layouts.app')
@section('title','Guest Request Details | Admin')
@section('body-class','dash-page')
@section('content')

<div class="modal-bg" id="rejectModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3>Reject Guest Request</h3>
      <button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.guest-requests.reject', $gr) }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg"><div class="flabel">Reason for Rejection</div>
          <textarea name="admin_note" class="fc" rows="3" required style="resize:vertical"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" class="modal-btn danger">Reject</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Guest Request Details','sub'=>$gr->request_number])
    <div class="content">

      @if(session('success'))
        <div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif

      <div style="display:grid;grid-template-columns:320px 1fr;gap:16px;align-items:start">

        {{-- LEFT --}}
        <div>
          <div class="profile-card">
            <div class="profile-card-hd"
              style="background:{{ $gr->service_type==='printing'?'var(--blue)':($gr->service_type==='photocopy'?'var(--orange)':'var(--g600)') }};color:#fff;border-radius:12px 12px 0 0;margin:-1px -1px 0">
              <i class="fa-solid {{ $gr->service_type==='printing'?'fa-print':($gr->service_type==='photocopy'?'fa-copy':'fa-desktop') }}" style="color:#fff"></i>
              {{ ucfirst($gr->service_type) }} — {{ $gr->request_number }}
            </div>
            <div class="profile-card-body" style="padding:0">
              @php
                $rows = [
                  ['Status',    strtoupper($gr->status),                    'fa-circle-dot'],
                  ['Role',      ucfirst(str_replace('_',' ',$gr->role)),    'fa-user-tag'],
                  ['Name',      $gr->full_name,                             'fa-user'],
                  ['Email',     $gr->email,                                 'fa-envelope'],
                  ['Campus',    config('campuses.'.$gr->campus),            'fa-building-columns'],
                  ['Submitted', $gr->created_at->format('M d, Y g:i A'),   'fa-calendar'],
                ];
                if ($gr->id_number) $rows[] = ['ID Number', $gr->id_number, 'fa-id-card'];
                if ($gr->paper_size) $rows[] = ['Paper Size', strtoupper($gr->paper_size), 'fa-expand'];
                if ($gr->copies)     $rows[] = ['Copies', $gr->copies, 'fa-hashtag'];
                if ($gr->print_type) $rows[] = ['Print Type', ucfirst(str_replace('_',' ',$gr->print_type)), 'fa-palette'];
                if ($gr->duration_minutes) $rows[] = ['Duration', $gr->duration_minutes.' minutes', 'fa-clock'];
                $rows[] = ['Purpose', $gr->purpose, 'fa-pen'];
                if ($gr->admin_note) $rows[] = ['Admin Note', $gr->admin_note, 'fa-note-sticky'];
              @endphp
              @foreach($rows as [$lbl,$val,$ico])
              <div style="padding:10px 18px;border-bottom:1px solid var(--gray100);display:flex;gap:10px;align-items:flex-start">
                <i class="fa-solid {{ $ico }}" style="color:var(--g600);width:14px;font-size:.78rem;margin-top:2px;flex-shrink:0"></i>
                <div>
                  <div style="font-size:.64rem;color:var(--gray400);font-weight:600;text-transform:uppercase">{{ $lbl }}</div>
                  <div style="font-size:.78rem;font-weight:600;color:var(--gray800);margin-top:1px">{{ $val }}</div>
                </div>
              </div>
              @endforeach

              @if($gr->file_path)
              <div style="padding:12px 18px">
                <a href="{{ Storage::url($gr->file_path) }}" target="_blank" class="btn" style="padding:9px;font-size:.78rem">
                  <i class="fa-solid fa-download"></i> Download File
                </a>
              </div>
              @endif
            </div>
          </div>
        </div>

        {{-- RIGHT --}}
        <div>
          <div class="profile-card">
            <div class="profile-card-hd"><i class="fa-solid fa-bolt"></i> Actions</div>
            <div class="profile-card-body" style="display:flex;flex-wrap:wrap;gap:8px">
              @if($gr->status === 'pending')
                <form action="{{ route('admin.guest-requests.approve',$gr) }}" method="POST">
                  @csrf<button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-check"></i> Approve
                  </button>
                </form>
                <button class="btn" style="background:linear-gradient(135deg,var(--red),#c62828);padding:9px 18px;font-size:.8rem;width:auto"
                  onclick="openModal('rejectModal')">
                  <i class="fa-solid fa-xmark"></i> Reject
                </button>
              @endif
              @if($gr->status === 'approved')
                <form action="{{ route('admin.guest-requests.processing',$gr) }}" method="POST">
                  @csrf<button class="btn" style="background:linear-gradient(135deg,var(--blue),#1976d2);padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-gear"></i> Mark Processing
                  </button>
                </form>
              @endif
              @if($gr->status === 'processing')
                <form action="{{ route('admin.guest-requests.complete',$gr) }}" method="POST">
                  @csrf<button class="btn" style="padding:9px 18px;font-size:.8rem;width:auto">
                    <i class="fa-solid fa-check-double"></i> Mark Completed
                  </button>
                </form>
              @endif
            </div>
          </div>

          {{-- Timeline --}}
          <div class="profile-card" style="margin-top:14px">
            <div class="profile-card-hd"><i class="fa-solid fa-timeline"></i> Request Timeline</div>
            <div class="profile-card-body" style="padding:14px 18px">
              @php
                $steps  = [['pending','Submitted',$gr->created_at->format('M d, Y g:i A')],['approved','Approved','—'],['processing','Processing','—'],['completed','Completed','—']];
                $order  = ['pending'=>0,'approved'=>1,'processing'=>2,'completed'=>3,'rejected'=>1];
                $current= $order[$gr->status] ?? 0;
              @endphp
              @foreach($steps as $i=>[$key,$label,$time])
              <div style="display:flex;gap:12px;align-items:flex-start;{{ !$loop->last?'padding-bottom:14px':'' }}">
                <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0">
                  <div style="width:28px;height:28px;border-radius:50%;
                    background:{{ $i<=$current?'var(--g500)':'var(--gray200)' }};
                    display:flex;align-items:center;justify-content:center;
                    color:{{ $i<=$current?'#fff':'var(--gray400)' }};font-size:.72rem">
                    <i class="fa-solid {{ $i<$current?'fa-check':($i===$current?'fa-circle-dot':'fa-circle') }}"></i>
                  </div>
                  @if(!$loop->last)
                  <div style="width:2px;flex:1;min-height:14px;background:{{ $i<$current?'var(--g300)':'var(--gray200)' }};margin:3px 0"></div>
                  @endif
                </div>
                <div style="padding-top:4px">
                  <div style="font-size:.78rem;font-weight:{{ $i===$current?'800':'600' }};color:{{ $i<=$current?'var(--gray800)':'var(--gray400)' }}">{{ $label }}</div>
                  <div style="font-size:.68rem;color:var(--gray400);margin-top:1px">{{ $time }}</div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

@push('scripts')
<script>
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
</script>
@endpush
@endsection