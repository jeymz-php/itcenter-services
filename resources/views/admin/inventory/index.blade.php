@extends('layouts.app')
@section('title','Inventory | Admin')
@section('body-class','dash-page')
@section('content')

<!-- ADD ITEM MODAL -->
<div class="modal-bg" id="addItemModal">
  <div class="modal-box">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-plus" style="color:var(--g600);margin-right:6px"></i>Add Inventory Item</h3>
      <button class="modal-close" onclick="closeModal('addItemModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form action="{{ route('admin.inventory.store') }}" method="POST">
      @csrf
      <div class="modal-body">
        <div class="fg">
          <div class="flabel">Category</div>
          <div class="sw"><select name="category" class="fs" required>
            <option value="paper_size">Paper Size</option>
            <option value="pc_duration">PC Duration</option>
          </select></div>
        </div>
        <div class="g2">
          <div class="fg"><div class="flabel">Display Name</div><input type="text" name="name" class="fc" placeholder="e.g. A4 (210 × 297 mm)" required></div>
          <div class="fg"><div class="flabel">Value / Key</div><input type="text" name="value" class="fc" placeholder="e.g. a4" required></div>
        </div>
        <div class="fg"><div class="flabel">Stock / Quantity</div><input type="number" name="stock" class="fc" min="0" value="0" required></div>
        <label class="cb-row"><input type="checkbox" name="is_active" value="1" checked> Active (visible to users)</label>
      </div>
      <div class="modal-footer">
        <button type="button" class="modal-btn secondary" onclick="closeModal('addItemModal')">Cancel</button>
        <button type="submit" class="modal-btn primary"><i class="fa-solid fa-plus"></i> Add Item</button>
      </div>
    </form>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Inventory Management','sub'=>'Paper stock and PC duration management'])
    <div class="content">

      @if(session('success'))<div class="abox ok" style="margin-bottom:14px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif

      <div style="display:flex;justify-content:flex-end;margin-bottom:14px">
        <button class="filter-bar btn-sm" style="padding:9px 18px" onclick="openModal('addItemModal')">
          <i class="fa-solid fa-plus"></i> Add Item
        </button>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- PAPER SIZES --}}
        <div>
          <div class="section-hd"><h3><i class="fa-solid fa-expand" style="color:var(--g600)"></i> Paper Sizes</h3></div>
          <div class="tbl-wrap">
            <table>
              <thead><tr><th>NAME</th><th>VALUE</th><th>STOCK</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
              <tbody>
                @forelse($papers as $item)
                <tr>
                  <td style="font-weight:600;font-size:.78rem">{{ $item->name }}</td>
                  <td style="font-family:monospace;font-size:.74rem">{{ $item->value }}</td>
                  <td>
                    <div style="display:flex;align-items:center;gap:8px">
                      <span style="font-weight:800;color:{{ $item->stock<50?'var(--red)':($item->stock<200?'var(--orange)':'var(--g700)') }}">
                        {{ $item->stock }}
                      </span>
                      <span style="font-size:.65rem;color:var(--gray400)">sheets</span>
                    </div>
                  </td>
                  <td><span class="tag {{ $item->is_active?'tag-active':'tag-deact' }}">{{ $item->is_active?'ACTIVE':'INACTIVE' }}</span></td>
                  <td>
                    <div style="display:flex;gap:4px">
                      {{-- Add stock --}}
                      <form action="{{ route('admin.inventory.stock',$item) }}" method="POST" style="display:flex;gap:4px">
                        @csrf
                        <input type="number" name="qty" class="fc" min="1" value="50" style="width:60px;padding:5px 7px;font-size:.72rem">
                        <button type="submit" class="act-btn act-appr" title="Add Stock" style="width:auto;padding:0 8px;font-size:.68rem">+Add</button>
                      </form>
                      {{-- Toggle --}}
                      <form action="{{ route('admin.inventory.update',$item) }}" method="POST" style="display:inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="name"  value="{{ $item->name }}">
                        <input type="hidden" name="stock" value="{{ $item->stock }}">
                        <input type="hidden" name="is_active" value="{{ $item->is_active?'0':'1' }}">
                        <button type="submit" class="act-btn {{ $item->is_active?'act-deact':'act-actv' }}" title="Toggle">
                          <i class="fa-solid fa-{{ $item->is_active?'eye-slash':'eye' }}"></i>
                        </button>
                      </form>
                      <form action="{{ route('admin.inventory.destroy',$item) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this item?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="act-btn act-del"><i class="fa-solid fa-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--gray400)">No paper sizes yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- PC DURATIONS --}}
        <div>
          <div class="section-hd"><h3><i class="fa-solid fa-clock" style="color:var(--g600)"></i> PC Durations</h3></div>
          <div class="tbl-wrap">
            <table>
              <thead><tr><th>NAME</th><th>MINUTES</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
              <tbody>
                @forelse($durations as $item)
                <tr>
                  <td style="font-weight:600;font-size:.78rem">{{ $item->name }}</td>
                  <td style="font-family:monospace;font-weight:800;color:var(--g700)">{{ $item->value }}m</td>
                  <td><span class="tag {{ $item->is_active?'tag-active':'tag-deact' }}">{{ $item->is_active?'ACTIVE':'INACTIVE' }}</span></td>
                  <td>
                    <div style="display:flex;gap:4px">
                      <form action="{{ route('admin.inventory.update',$item) }}" method="POST" style="display:inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="name"  value="{{ $item->name }}">
                        <input type="hidden" name="stock" value="{{ $item->stock }}">
                        <input type="hidden" name="is_active" value="{{ $item->is_active?'0':'1' }}">
                        <button type="submit" class="act-btn {{ $item->is_active?'act-deact':'act-actv' }}" title="Toggle">
                          <i class="fa-solid fa-{{ $item->is_active?'eye-slash':'eye' }}"></i>
                        </button>
                      </form>
                      <form action="{{ route('admin.inventory.destroy',$item) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="act-btn act-del"><i class="fa-solid fa-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--gray400)">No durations yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
@endsection
@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
</script>
@endpush