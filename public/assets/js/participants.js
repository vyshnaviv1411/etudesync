document.addEventListener('DOMContentLoaded', () => {
  const listEl = document.getElementById('participantsListBox');
  const countEl = document.getElementById('participantCount');
  if (!listEl) return;

  async function loadParticipants() {
    try {
      const res = await fetch(`api/fetch_participants.php?room_id=${ROOM_ID}`);
      const data = await res.json();
      if (!data.success) return;

      listEl.innerHTML = '';

      data.participants.forEach(p => {
        const row = document.createElement('div');
        row.className = 'participant-row';

        row.innerHTML = `
          <img
            src="${p.avatar || 'assets/images/profile-placeholder.png'}"
            class="participant-avatar"
            alt="${p.name}"
          >
          <div class="participant-info">
            <div class="pname">${p.name}</div>
            <div class="small-muted">Joined</div>
          </div>
        `;

        listEl.appendChild(row);
      });

      // ✅ participant count
      if (countEl) {
        countEl.textContent = data.participants.length;
      }

    } catch (e) {
      console.error('Participants error:', e);
    }
  }

  loadParticipants();
  setInterval(loadParticipants, 5000);
});
