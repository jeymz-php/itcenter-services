@extends('layouts.app')
@section('title','Messages | Admin')
@section('body-class','dash-page')
@section('content')

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Messages','sub'=>'Conversations with students & faculty'])
    <div class="content">

      <div class="chat-wrap {{ $activeUser ? 'conv-open' : '' }}" id="chat-wrap">

        {{-- CONVERSATION LIST --}}
        <div class="chat-side">
          <div class="chat-side-hd">
            <i class="fa-solid fa-comments" style="color:var(--g600);margin-right:6px"></i>
            Conversations ({{ $conversations->count() }})
          </div>
          @forelse($conversations as $c)
          <a href="{{ route('admin.messages.index', ['user'=>$c->id]) }}"
             class="chat-list-item {{ $activeUser && $activeUser->id === $c->id ? 'active' : '' }}">
            <div class="chat-list-avatar">{{ strtoupper(substr($c->first_name,0,1)) }}</div>
            <div style="min-width:0;flex:1">
              <div style="display:flex;align-items:center">
                <span class="chat-list-name">{{ $c->first_name }} {{ $c->last_name }}</span>
                @if($c->unread_count > 0)<span class="chat-list-badge">{{ $c->unread_count }}</span>@endif
              </div>
              <div class="chat-list-preview">{{ ucfirst(str_replace('_',' ',$c->user_type)) }} · {{ $c->id_number }}</div>
            </div>
            <div class="chat-list-time">{{ \Illuminate\Support\Carbon::parse($c->last_message_at)->format('M d') }}</div>
          </a>
          @empty
          <div style="padding:24px 16px;text-align:center;color:var(--gray400);font-size:.78rem">
            No conversations yet.
          </div>
          @endforelse
        </div>

        {{-- CHAT PANEL --}}
        <div class="chat-main">
          @if($activeUser)
            <div class="chat-header">
              <button class="chat-back" onclick="document.getElementById('chat-wrap').classList.remove('conv-open')">
                <i class="fa-solid fa-arrow-left"></i>
              </button>
              <div class="chat-list-avatar">{{ strtoupper(substr($activeUser->first_name,0,1)) }}</div>
              <div>
                <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">{{ $activeUser->first_name }} {{ $activeUser->last_name }}</div>
                <div style="font-size:.7rem;color:var(--gray400)">{{ ucfirst(str_replace('_',' ',$activeUser->user_type)) }} · ID: {{ $activeUser->id_number }}</div>
              </div>
            </div>

            @if($requests->count())
            <div class="chat-context">
              <i class="fa-solid fa-paperclip" style="color:var(--g600)"></i>
              Attach to:
              <select id="ctx-request">
                <option value="">General reply</option>
                @foreach($requests as $r)
                  <option value="{{ $r->id }}">{{ $r->request_number }} — {{ ucfirst($r->service_type) }} ({{ $r->status }})</option>
                @endforeach
              </select>
            </div>
            @endif

            <div class="chat-messages" id="chat-messages">
              @forelse($messages as $m)
                <div class="chat-bubble-row {{ $m->sender_type==='admin' ? 'me' : 'them' }}">
                  <div class="chat-bubble">
                    <div class="cb-text">{{ $m->body }}</div>
                    <div class="cb-meta">
                      {{ $m->sender_type==='admin' ? ($m->senderAdmin->admin_id ?? 'Admin') : $activeUser->first_name }}
                      · {{ $m->created_at->format('M d, g:i A') }}
                      @if($m->service_request_id)
                        · <span style="opacity:.85">Re: {{ $m->serviceRequest?->request_number }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              @empty
                <div class="chat-empty">
                  <i class="fa-solid fa-comments" style="font-size:1.8rem"></i>
                  <div style="font-size:.8rem;font-weight:700">No messages yet</div>
                </div>
              @endforelse
            </div>

            <form id="chat-form" class="chat-input-bar">
              @csrf
              <input type="hidden" id="chat-user-id" value="{{ $activeUser->id }}">
              <textarea id="chat-input" name="body" rows="1" placeholder="Type a reply..." required></textarea>
              <button type="submit" class="chat-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
          @else
            <div class="chat-empty">
              <i class="fa-solid fa-inbox" style="font-size:1.8rem"></i>
              <div style="font-size:.8rem;font-weight:700">Select a conversation</div>
            </div>
          @endif
        </div>
      </div>

    </div>
  </main>
</div>

@push('scripts')
<script>
(function(){
  const activeUserId = {{ $activeUser ? $activeUser->id : 'null' }};
  let lastId = {{ (int) $lastId }};
  const box   = document.getElementById('chat-messages');
  const form  = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const ctx   = document.getElementById('ctx-request');
  const wrap  = document.getElementById('chat-wrap');

  // On mobile, tapping a conversation link should open the full-screen chat panel
  document.querySelectorAll('.chat-list-item').forEach(a=>{
    a.addEventListener('click', function(){ wrap.classList.add('conv-open'); });
  });

  if (!box) return;
  function scrollBottom(){ box.scrollTop = box.scrollHeight; }
  scrollBottom();

  function bubbleHtml(m){
    const who = m.sender === 'admin' ? (m.sender_name || 'Admin') : '{{ $activeUser->first_name ?? "" }}';
    const reqTag = m.request_id ? ` · <span style="opacity:.85">Re: request #${m.request_id}</span>` : '';
    return `<div class="chat-bubble-row ${m.sender==='admin'?'me':'them'}">
      <div class="chat-bubble">
        <div class="cb-text">${m.body}</div>
        <div class="cb-meta">${who} · ${m.time}${reqTag}</div>
      </div>
    </div>`;
  }

  if (form) {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const body = input.value.trim();
      if (!body) return;

      const fd = new FormData();
      fd.append('_token', '{{ csrf_token() }}');
      fd.append('user_id', activeUserId);
      fd.append('body', body);
      if (ctx && ctx.value) fd.append('service_request_id', ctx.value);

      input.value = '';
      input.style.height = 'auto';

      fetch('{{ route("admin.messages.send") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.ok) {
            const empty = box.querySelector('.chat-empty');
            if (empty) empty.remove();
            box.insertAdjacentHTML('beforeend', bubbleHtml(data.message));
            lastId = Math.max(lastId, data.message.id);
            scrollBottom();
          }
        })
        .catch(()=>{});
    });

    input.addEventListener('input', function(){
      input.style.height = 'auto';
      input.style.height = Math.min(input.scrollHeight, 100) + 'px';
    });
  }

  function poll(){
    if (!activeUserId) return;
    fetch('{{ route("admin.messages.poll") }}?user_id=' + activeUserId + '&last_id=' + lastId)
      .then(r => r.json())
      .then(data => {
        if (data.messages && data.messages.length){
          const empty = box.querySelector('.chat-empty');
          if (empty) empty.remove();
          data.messages.forEach(m => {
            if (m.sender === 'user') { box.insertAdjacentHTML('beforeend', bubbleHtml(m)); }
          });
          lastId = Math.max(lastId, data.last_id);
          scrollBottom();
        } else if (data.last_id) {
          lastId = data.last_id;
        }
      })
      .catch(()=>{});
  }
  setInterval(poll, 3000);
})();
</script>
@endpush
@endsection