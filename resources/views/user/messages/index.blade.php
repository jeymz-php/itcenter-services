@extends('user.requests._layout')
@section('title','Messages | IT Center')
@section('page-title','Messages')
@section('page-sub','Chat directly with an IT Center administrator')

@section('request-content')

<!-- END CHAT MODAL -->
<div class="modal-bg" id="endChatModal">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-comment-slash" style="color:var(--red);margin-right:6px"></i>End Conversation?</h3>
      <button class="modal-close" onclick="closeModal('endChatModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p>This will clear the current conversation from view for both you and the IT Center admin. Nothing is deleted — sending a new message will simply start a fresh conversation.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="modal-btn secondary" onclick="closeModal('endChatModal')">Cancel</button>
      <button type="button" class="modal-btn danger" id="confirmEndChat"><i class="fa-solid fa-comment-slash"></i> End Conversation</button>
    </div>
  </div>
</div>

<div class="chat-wrap conv-open">
  <div class="chat-main">
    <div class="chat-header">
      <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--g700),var(--g500));display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0">
        <i class="fa-solid fa-headset"></i>
      </div>
      <div style="flex:1">
        <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">IT Center Support</div>
        <div style="font-size:.7rem;color:var(--gray400)">Printing · Photocopy · Research follow-ups</div>
      </div>
      <button id="end-chat-btn" onclick="openModal('endChatModal')" title="End conversation"
        style="background:var(--red-bg);color:var(--red);border:1.5px solid rgba(229,62,62,.25);border-radius:8px;padding:7px 12px;font-size:.72rem;font-weight:700;cursor:pointer;display:{{ $sessionOpen ? 'flex' : 'none' }};align-items:center;gap:5px;white-space:nowrap">
        <i class="fa-solid fa-comment-slash"></i> <span class="end-chat-label">End Chat</span>
      </button>
    </div>

    @if($requests->count())
    <div class="chat-context">
      <i class="fa-solid fa-paperclip" style="color:var(--g600)"></i>
      Attach to:
      <select id="ctx-request">
        <option value="">General question</option>
        @foreach($requests as $r)
          <option value="{{ $r->id }}">{{ $r->request_number }} — {{ ucfirst($r->service_type) }}</option>
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
            Send a message below to start a new conversation.
          </div>
        </div>
      @else
        @forelse($messages as $m)
          <div class="chat-bubble-row {{ $m->sender_type==='user' ? 'me' : 'them' }}">
            <div class="chat-bubble">
              <div class="cb-text">{{ $m->body }}</div>
              <div class="cb-meta">
                {{ $m->sender_type==='user' ? 'You' : ($m->senderAdmin->admin_id ?? 'IT Center Admin') }}
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
              Send a message below to ask about printing, photocopy, or PC lab requests.
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
      <textarea id="chat-input" name="body" rows="1" placeholder="Type your message... (Enter to send, Shift+Enter for new line)" required></textarea>
      <button type="submit" class="chat-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
  </div>
</div>

@push('styles')
<style>
.typing-bubble{display:flex;align-items:center;gap:4px;padding:12px 15px}
.typing-dot{width:6px;height:6px;border-radius:50%;background:var(--gray400);animation:typingBounce 1.2s infinite}
.typing-dot:nth-child(2){animation-delay:.2s}
.typing-dot:nth-child(3){animation-delay:.4s}
@keyframes typingBounce{0%,60%,100%{transform:translateY(0);opacity:.5}30%{transform:translateY(-4px);opacity:1}}
@media(max-width:768px){ #end-chat-btn .end-chat-label{display:none} #end-chat-btn{padding:8px 10px} }
</style>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

(function(){
  let lastId = {{ (int) $lastId }};
  let sessionActive = {{ $sessionOpen ? 'true' : 'false' }};
  const box       = document.getElementById('chat-messages');
  const form      = document.getElementById('chat-form');
  const input     = document.getElementById('chat-input');
  const ctx       = document.getElementById('ctx-request');
  const typingEl  = document.getElementById('typing-indicator');
  const endBtn    = document.getElementById('end-chat-btn');

  function scrollBottom(){ box.scrollTop = box.scrollHeight; }
  scrollBottom();

  function showTyping(show){
    typingEl.style.display = show ? 'flex' : 'none';
    if (show) scrollBottom();
  }

  function bubbleHtml(m){
    const who = m.sender === 'user' ? 'You' : (m.sender_name || 'IT Center Admin');
    const reqTag = m.request_id ? ` · <span style="opacity:.85">Re: request #${m.request_id}</span>` : '';
    return `<div class="chat-bubble-row ${m.sender==='user'?'me':'them'}">
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
        <div style="font-size:.72rem;text-align:center;max-width:220px">Send a message below to start a new conversation.</div>
      </div>`);
    endBtn.style.display = 'none';
    sessionActive = false;
  }

  function removeEndedState(){
    const ended = document.getElementById('ended-state');
    if (ended) ended.remove();
    const empty = document.getElementById('empty-state');
    if (empty) empty.remove();
    endBtn.style.display = 'flex';
    sessionActive = true;
  }

  function sendMessage(){
    const body = input.value.trim();
    if (!body) return;

    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('body', body);
    if (ctx && ctx.value) fd.append('service_request_id', ctx.value);

    input.value = '';
    input.style.height = 'auto';

    fetch('{{ route("user.messages.send") }}', { method: 'POST', body: fd })
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
      fetch('{{ route("user.messages.typing") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      }).catch(()=>{});
    }
    clearTimeout(typingPingTimer);
    typingPingTimer = setTimeout(() => { typingPingTimer = null; }, 3000);
  });

  document.getElementById('confirmEndChat').addEventListener('click', function(){
    fetch('{{ route("user.messages.end-session") }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
      .then(r => r.json())
      .then(() => {
        closeModal('endChatModal');
        clearToEndedState();
      })
      .catch(()=>{});
  });

  function poll(){
    fetch('{{ route("user.messages.poll") }}?last_id=' + lastId)
      .then(r => r.json())
      .then(data => {
        if (data.messages && data.messages.length){
          removeEndedState();
          data.messages.forEach(m => {
            if (m.sender === 'admin') { typingEl.insertAdjacentHTML('beforebegin', bubbleHtml(m)); }
          });
          lastId = Math.max(lastId, data.last_id);
          scrollBottom();
        } else if (data.last_id) {
          lastId = data.last_id;
        }
        showTyping(!!data.admin_typing);

        // The admin ended the chat while we were looking at it
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