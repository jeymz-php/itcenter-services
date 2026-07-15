@extends('user.requests._layout')
@section('title','Messages | IT Center')
@section('page-title','Messages')
@section('page-sub','Chat directly with an IT Center administrator')

@section('request-content')

<div class="chat-wrap conv-open">
  <div class="chat-main">
    <div class="chat-header">
      <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--g700),var(--g500));display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0">
        <i class="fa-solid fa-headset"></i>
      </div>
      <div>
        <div style="font-size:.85rem;font-weight:800;color:var(--gray800)">IT Center Support</div>
        <div style="font-size:.7rem;color:var(--gray400)">Printing · Photocopy · Research follow-ups</div>
      </div>
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
        <div class="chat-empty">
          <i class="fa-solid fa-comments" style="font-size:1.8rem"></i>
          <div style="font-size:.8rem;font-weight:700">No messages yet</div>
          <div style="font-size:.72rem;text-align:center;max-width:220px">
            Send a message below to ask about printing, photocopy, or PC lab requests.
          </div>
        </div>
      @endforelse
    </div>

    <form id="chat-form" class="chat-input-bar">
      @csrf
      <textarea id="chat-input" name="body" rows="1" placeholder="Type your message..." required></textarea>
      <button type="submit" class="chat-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
  </div>
</div>

@push('scripts')
<script>
(function(){
  let lastId = {{ (int) $lastId }};
  const box   = document.getElementById('chat-messages');
  const form  = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const ctx   = document.getElementById('ctx-request');

  function scrollBottom(){ box.scrollTop = box.scrollHeight; }
  scrollBottom();

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

  form.addEventListener('submit', function(e){
    e.preventDefault();
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

  function poll(){
    fetch('{{ route("user.messages.poll") }}?last_id=' + lastId)
      .then(r => r.json())
      .then(data => {
        if (data.messages && data.messages.length){
          const empty = box.querySelector('.chat-empty');
          if (empty) empty.remove();
          data.messages.forEach(m => {
            if (m.sender === 'admin') { box.insertAdjacentHTML('beforeend', bubbleHtml(m)); }
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