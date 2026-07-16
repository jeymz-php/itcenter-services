@extends('layouts.app')
@section('title','Messages | Admin')
@section('body-class','dash-page')
@section('content')
@php $__admin = session('admin'); @endphp

<!-- NEW CHAT MODAL -->
<div class="modal-bg" id="newChatModal">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-comment-medical" style="color:var(--g600);margin-right:7px"></i>Start a New Conversation</h3>
      <button class="modal-close" onclick="closeModal('newChatModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      @if($__admin->role !== 'super_admin')
      <div class="abox info" style="margin-bottom:12px">
        <i class="fa-solid fa-circle-info"></i>
        <div>You can message students & faculty from <strong>{{ config('campuses.'.$__admin->campus, $__admin->campus) }}</strong> only.</div>
      </div>
      @endif
      <div class="fg" style="margin-bottom:8px">
        <div class="iw">
          <span class="ii"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input type="text" id="user-search-input" class="fc" placeholder="Search by name, ID number, or email..." autocomplete="off">
        </div>
      </div>
      <div id="user-search-results" style="max-height:280px;overflow-y:auto"></div>
      <div id="user-search-loading" style="text-align:center;padding:24px;color:var(--gray400);font-size:.78rem">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
        Loading users...
      </div>
    </div>
  </div>
</div>

<!-- END CHAT MODAL -->
<div class="modal-bg" id="endChatModal">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-comment-slash" style="color:var(--red);margin-right:6px"></i>End Conversation?</h3>
      <button class="modal-close" onclick="closeModal('endChatModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p>This will clear the current conversation from view for both you and <strong id="endChatUserName">this user</strong>. Nothing is deleted — sending a new message will simply start a fresh conversation.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="modal-btn secondary" onclick="closeModal('endChatModal')">Cancel</button>
      <button type="button" class="modal-btn danger" id="confirmEndChat"><i class="fa-solid fa-comment-slash"></i> End Conversation</button>
    </div>
  </div>
</div>

<div class="dash-wrap">
  @include('admin.partials.sidebar')
  <main class="main">
    @include('admin.partials.topbar', ['title'=>'Messages','sub'=> $__admin->role === 'super_admin' ? 'Conversations with students & faculty (all campuses)' : 'Conversations with students & faculty — '.config('campuses.'.$__admin->campus, $__admin->campus)])
    <div class="content">

      <div class="chat-wrap {{ $activeUser ? 'conv-open' : '' }}" id="chat-wrap">

        {{-- CONVERSATION LIST --}}
        <div class="chat-side">
          <div class="chat-side-hd" style="display:flex;align-items:center;justify-content:space-between;gap:8px">
            <span><i class="fa-solid fa-comments" style="color:var(--g600);margin-right:6px"></i>Conversations ({{ $conversations->count() }})</span>
            <button onclick="openModal('newChatModal');loadUserList('')"
              style="background:var(--g100);color:var(--g700);border:1.5px solid var(--g300);border-radius:7px;padding:5px 10px;font-size:.68rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;white-space:nowrap">
              <i class="fa-solid fa-plus"></i> New
            </button>
          </div>
          @forelse($conversations as $c)
          <a href="{{ route('admin.messages.index', ['user'=>$c->id]) }}"
             class="chat-list-item {{ $activeUser && $activeUser->id === $c->id ? 'active' : '' }}"
             data-name="{{ $c->first_name }} {{ $c->last_name }}">
            <div class="chat-list-avatar">{{ strtoupper(substr($c->first_name,0,1)) }}</div>
            <div style="min-width:0;flex:1">
              <div style="display:flex;align-items:center">
                <span class="chat-list-name">{{ $c->first_name }} {{ $c->last_name }}</span>
                @if($c->unread_count > 0)<span class="chat-list-badge">{{ $c->unread_count }}</span>@endif
              </div>
              <div class="chat-list-preview">
                {{ ucfirst(str_replace('_',' ',$c->user_type)) }} · {{ $c->id_number }}
                @if(!$c->last_message_at)<span style="color:var(--g600);font-weight:700"> · New</span>@endif
              </div>
            </div>
            @if($c->last_message_at)
            <div class="chat-list-time">{{ \Illuminate\Support\Carbon::parse($c->last_message_at)->format('M d') }}</div>
            @endif
          </a>
          @empty
          <div style="padding:24px 16px;text-align:center;color:var(--gray400);font-size:.78rem">
            No conversations yet. Tap <strong>New</strong> to message a student or faculty member.
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
              <div style="flex:1">
                <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">{{ $activeUser->first_name }} {{ $activeUser->last_name }}</div>
                <div style="font-size:.7rem;color:var(--gray400)">{{ ucfirst(str_replace('_',' ',$activeUser->user_type)) }} · ID: {{ $activeUser->id_number }} · {{ config('campuses.'.$activeUser->campus, $activeUser->campus) }}</div>
              </div>
              <button id="end-chat-btn" onclick="openModal('endChatModal')" title="End conversation"
                style="background:var(--red-bg);color:var(--red);border:1.5px solid rgba(229,62,62,.25);border-radius:8px;padding:7px 12px;font-size:.72rem;font-weight:700;cursor:pointer;display:{{ $sessionOpen ? 'flex' : 'none' }};align-items:center;gap:5px;white-space:nowrap;flex-shrink:0">
                <i class="fa-solid fa-comment-slash"></i> <span class="end-chat-label">End Chat</span>
              </button>
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
              @if($conversationEnded)
                <div class="chat-empty" id="ended-state">
                  <i class="fa-solid fa-comment-slash" style="font-size:1.8rem"></i>
                  <div style="font-size:.8rem;font-weight:700">Conversation ended</div>
                  <div style="font-size:.72rem;text-align:center;max-width:220px">
                    Send a message below to start a new conversation with {{ $activeUser->first_name }}.
                  </div>
                </div>
              @else
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
                  <div class="chat-empty" id="empty-state">
                    <i class="fa-solid fa-comments" style="font-size:1.8rem"></i>
                    <div style="font-size:.8rem;font-weight:700">No messages yet</div>
                    <div style="font-size:.72rem;text-align:center;max-width:220px">
                      Send the first message to {{ $activeUser->first_name }} below.
                    </div>
                  </div>
                @endforelse
              @endif
              <div id="typing-indicator" class="chat-bubble-row them" style="display:none">
                <div class="chat-bubble typing-bubble">
                  <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>
                </div>
              </div>
            </div>

            <form id="chat-form" class="chat-input-bar">
              @csrf
              <input type="hidden" id="chat-user-id" value="{{ $activeUser->id }}">
              <textarea id="chat-input" name="body" rows="1" placeholder="Type a reply... (Enter to send, Shift+Enter for new line)" required></textarea>
              <button type="submit" class="chat-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
          @else
            <div class="chat-empty">
              <i class="fa-solid fa-inbox" style="font-size:1.8rem"></i>
              <div style="font-size:.8rem;font-weight:700">Select a conversation</div>
              <div style="font-size:.72rem;text-align:center;max-width:220px">or tap "New" to message any student or faculty member</div>
            </div>
          @endif
        </div>
      </div>

    </div>
  </main>
</div>

@push('styles')
<style>
.typing-bubble{display:flex;align-items:center;gap:4px;padding:12px 15px}
.typing-dot{width:6px;height:6px;border-radius:50%;background:var(--gray400);animation:typingBounce 1.2s infinite}
.typing-dot:nth-child(2){animation-delay:.2s}
.typing-dot:nth-child(3){animation-delay:.4s}
@keyframes typingBounce{0%,60%,100%{transform:translateY(0);opacity:.5}30%{transform:translateY(-4px);opacity:1}}
.us-result{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;cursor:pointer;transition:background .15s}
.us-result:hover{background:var(--g50)}
@media(max-width:768px){ #end-chat-btn .end-chat-label{display:none} #end-chat-btn{padding:8px 10px} }
</style>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

(function(){
  const activeUserId = {{ $activeUser ? $activeUser->id : 'null' }};
  const activeUserName = {!! $activeUser ? json_encode($activeUser->first_name.' '.$activeUser->last_name) : 'null' !!};
  let lastId = {{ (int) $lastId }};
  let sessionActive = {{ $sessionOpen ? 'true' : 'false' }};
  const box     = document.getElementById('chat-messages');
  const form    = document.getElementById('chat-form');
  const input   = document.getElementById('chat-input');
  const ctx     = document.getElementById('ctx-request');
  const wrap    = document.getElementById('chat-wrap');
  const typingEl= document.getElementById('typing-indicator');
  const endBtn  = document.getElementById('end-chat-btn');

  // ── New Chat: browse/search users ──
  const searchInput   = document.getElementById('user-search-input');
  const resultsBox    = document.getElementById('user-search-results');
  const loadingBox    = document.getElementById('user-search-loading');
  let searchDebounce   = null;

  window.loadUserList = function(q){
    loadingBox.style.display = 'block';
    resultsBox.innerHTML = '';
    fetch('{{ route("admin.messages.search-users") }}?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        loadingBox.style.display = 'none';
        if (!data.users.length) {
          resultsBox.innerHTML = '<div style="text-align:center;padding:20px;color:var(--gray400);font-size:.78rem">No matching users found.</div>';
          return;
        }
        resultsBox.innerHTML = data.users.map(u => `
          <div class="us-result" onclick="window.location='{{ route('admin.messages.index') }}?user=${u.id}'">
            <div class="chat-list-avatar">${u.name.charAt(0).toUpperCase()}</div>
            <div style="min-width:0;flex:1">
              <div class="chat-list-name">${u.name}</div>
              <div class="chat-list-preview">${u.user_type} · ${u.id_number} · ${u.campus}</div>
            </div>
          </div>`).join('');
      })
      .catch(()=>{ loadingBox.style.display = 'none'; });
  };

  if (searchInput) {
    searchInput.addEventListener('input', function(){
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => loadUserList(searchInput.value.trim()), 300);
    });
  }

  document.querySelectorAll('.chat-list-item').forEach(a=>{
    a.addEventListener('click', function(){ wrap.classList.add('conv-open'); });
  });

  if (!box) return;
  function scrollBottom(){ box.scrollTop = box.scrollHeight; }
  scrollBottom();

  function showTyping(show){
    if (!typingEl) return;
    typingEl.style.display = show ? 'flex' : 'none';
    if (show) scrollBottom();
  }

  function bubbleHtml(m){
    const who = m.sender === 'admin' ? (m.sender_name || 'Admin') : (activeUserName || '');
    const reqTag = m.request_id ? ` · <span style="opacity:.85">Re: request #${m.request_id}</span>` : '';
    return `<div class="chat-bubble-row ${m.sender==='admin'?'me':'them'}">
      <div class="chat-bubble">
        <div class="cb-text">${m.body}</div>
        <div class="cb-meta">${who} · ${m.time}${reqTag}</div>
      </div>
    </div>`;
  }

  function clearToEndedState(){
    box.querySelectorAll('.chat-bubble-row:not(#typing-indicator)').forEach(el => el.remove());
    typingEl.insertAdjacentHTML('beforebegin', `
      <div class="chat-empty" id="ended-state">
        <i class="fa-solid fa-comment-slash" style="font-size:1.8rem"></i>
        <div style="font-size:.8rem;font-weight:700">Conversation ended</div>
        <div style="font-size:.72rem;text-align:center;max-width:220px">Send a message below to start a new conversation${activeUserName ? ' with '+activeUserName : ''}.</div>
      </div>`);
    if (endBtn) endBtn.style.display = 'none';
    sessionActive = false;
  }

  function removeEndedState(){
    const ended = document.getElementById('ended-state');
    if (ended) ended.remove();
    const empty = document.getElementById('empty-state');
    if (empty) empty.remove();
    if (endBtn) endBtn.style.display = 'flex';
    sessionActive = true;
  }

  function sendMessage(){
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
          removeEndedState();
          typingEl.insertAdjacentHTML('beforebegin', bubbleHtml(data.message));
          lastId = Math.max(lastId, data.message.id);
          scrollBottom();
        }
      })
      .catch(()=>{});
  }

  if (form) {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      sendMessage();
    });

    input.addEventListener('keydown', function(e){
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    let typingPingTimer = null;
    input.addEventListener('input', function(){
      input.style.height = 'auto';
      input.style.height = Math.min(input.scrollHeight, 100) + 'px';

      if (!typingPingTimer) {
        const fd = new FormData();
        fd.append('user_id', activeUserId);
        fetch('{{ route("admin.messages.typing") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: fd,
        }).catch(()=>{});
      }
      clearTimeout(typingPingTimer);
      typingPingTimer = setTimeout(() => { typingPingTimer = null; }, 3000);
    });
  }

  if (endBtn) {
    document.getElementById('endChatUserName').textContent = activeUserName || 'this user';
    document.getElementById('confirmEndChat').addEventListener('click', function(){
      const fd = new FormData();
      fd.append('user_id', activeUserId);
      fetch('{{ route("admin.messages.end-session") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: fd,
      })
        .then(r => r.json())
        .then(() => {
          closeModal('endChatModal');
          clearToEndedState();
        })
        .catch(()=>{});
    });
  }

  function poll(){
    if (!activeUserId) return;
    fetch('{{ route("admin.messages.poll") }}?user_id=' + activeUserId + '&last_id=' + lastId)
      .then(r => r.json())
      .then(data => {
        if (data.messages && data.messages.length){
          removeEndedState();
          data.messages.forEach(m => {
            if (m.sender === 'user') { typingEl.insertAdjacentHTML('beforebegin', bubbleHtml(m)); }
          });
          lastId = Math.max(lastId, data.last_id);
          scrollBottom();
        } else if (data.last_id) {
          lastId = data.last_id;
        }
        showTyping(!!data.user_typing);

        if (sessionActive && !data.session_active) {
          clearToEndedState();
        }
      })
      .catch(()=>{});
  }
  setInterval(poll, 3000);
})();
</script>
@endpush
@endsection