document.addEventListener('DOMContentLoaded', () => {
  const chatList = document.getElementById('chatList');
  const chatInput = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSendBtn');

  if (!chatList || !chatInput || !sendBtn) return;

  let lastMessageId = 0;

  function renderMessage(m) {
    const row = document.createElement('div');
    row.className = 'chat-row';

    row.innerHTML = `
      <img class="chat-avatar"
           src="${m.avatar || 'assets/images/profile-placeholder.png'}">
      <div class="chat-body">
        <div class="chat-meta">
          <strong>${m.username}</strong>
          <span>${new Date(m.created_at).toLocaleTimeString()}</span>
        </div>
        <div class="chat-text"></div>
      </div>
    `;

    row.querySelector('.chat-text').textContent = m.message;

    chatList.appendChild(row);
    chatList.scrollTop = chatList.scrollHeight;
    lastMessageId = Math.max(lastMessageId, Number(m.message_id));
  }

  async function loadMessages() {
    try {
      const res = await fetch(
        `api/fetch_messages.php?room_id=${ROOM_ID}&after_id=${lastMessageId}`
      );
      const data = await res.json();
      if (!data.success) return;

      data.messages.forEach(renderMessage);
    } catch (e) {
      console.error('Chat load error', e);
    }
  }

  async function sendMessage() {
    const text = chatInput.value.trim();
    if (!text) return;

    sendBtn.disabled = true;

    try {
      const fd = new FormData();
      fd.append('room_id', ROOM_ID);
      fd.append('message', text);

      await fetch('api/send_message.php', {
        method: 'POST',
        body: fd
      });

      chatInput.value = '';
      loadMessages(); // 🔥 fetch only NEW message
    } catch (e) {
      console.error('Chat send error', e);
    } finally {
      sendBtn.disabled = false;
    }
  }

  sendBtn.addEventListener('click', sendMessage);
  chatInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      sendMessage();
    }
  });

  loadMessages();
  setInterval(loadMessages, 1500);
});
