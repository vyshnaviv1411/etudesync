// public/assets/js/create_room.js
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('createRoomForm');
  const btn  = document.getElementById('submitCreate');
  const msg  = document.getElementById('crMsg');

  if (!form || !btn || !msg) return;

  function show(text, type = 'info') {
    msg.style.display = 'block';
    msg.textContent = text;
    msg.style.color =
      type === 'error' ? '#ff9a9a' :
      type === 'success' ? '#9affb1' :
      '#ffffff';
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    btn.disabled = true;
    btn.textContent = 'Creating…';
    show('Creating room…');

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: new FormData(form)
      });

      const data = await res.json();

      if (!data.success) {
        show(data.error || 'Room creation failed', 'error');
        btn.disabled = false;
        btn.textContent = 'Create Room';
        return;
      }

      show('Room created. Entering room…', 'success');

      // 🔥 AUTO REDIRECT HOST INTO ROOM
      window.location.href = data.redirect;

    } catch (err) {
      console.error(err);
      show('Network error. Try again.', 'error');
      btn.disabled = false;
      btn.textContent = 'Create Room';
    }
  });
});
