document.addEventListener('DOMContentLoaded', () => {
  const listEl  = document.getElementById('participantsListBox');
  const countEl = document.getElementById('participantCount');

  if (!listEl) return;

  let accessRevoked = false;
  let pingTimer = null;

  /* --------------------------------------------------
     🔒 ACCESS CHECK (GET — MUST MATCH BACKEND)
  -------------------------------------------------- */
  async function enforceAccess() {
    try {
      const res = await fetch(
        `api/check_room_access.php?room_id=${ROOM_ID}`,
        { cache: 'no-store' }
      );

      const data = await res.json();

      if (!data.allowed) {
        accessRevoked = true;

        if (pingTimer) clearInterval(pingTimer);

        alert('You were removed from the room.');
        window.location.href = 'collabsphere.php';
        return false;
      }

      return true;
    } catch (e) {
      console.error('Access check failed', e);
      return false;
    }
  }

  /* --------------------------------------------------
     👥 LOAD PARTICIPANTS
  -------------------------------------------------- */
  async function loadParticipants() {
    if (accessRevoked) return;

    const allowed = await enforceAccess();
    if (!allowed) return;

    try {
      const res = await fetch(
        `api/fetch_participants.php?room_id=${ROOM_ID}`,
        { cache: 'no-store' }
      );
      const data = await res.json();

      if (!data.success) return;

      listEl.innerHTML = '';

      data.participants.forEach(p => {
        const row = document.createElement('div');
        row.className = 'participant-row';
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.alignItems = 'center';
        row.style.padding = '6px 0';

        // LEFT SIDE
        const left = document.createElement('div');
        left.style.display = 'flex';
        left.style.gap = '8px';
        left.style.alignItems = 'center';

        left.innerHTML = `
          <img
            src="${p.avatar || 'assets/images/profile-placeholder.png'}"
            style="width:36px;height:36px;border-radius:50%;object-fit:cover"
          />
          <div>
            <div style="font-weight:600;display:flex;gap:6px;align-items:center">
              ${p.username}
              ${p.is_host ? `
                <span style="
                  background:#7c4dff;
                  color:white;
                  font-size:0.65rem;
                  padding:2px 6px;
                  border-radius:6px;
                  font-weight:700;
                ">HOST</span>
              ` : ''}
            </div>
            <div class="small-muted">Joined</div>
          </div>
        `;

        row.appendChild(left);

        // REMOVE BUTTON (HOST ONLY)
        if (
          CAN_MANAGE &&
          !p.is_host &&
          p.user_id !== USER_ID
        ) {
          const btn = document.createElement('button');
          btn.className = 'btn small danger';
          btn.textContent = 'Remove';
          btn.onclick = () => removeParticipant(p.user_id);
          row.appendChild(btn);
        }

        listEl.appendChild(row);
      });

      // COUNT
      if (countEl) {
        countEl.textContent = `${data.participants.length} joined`;
      }

    } catch (e) {
      console.error('Participants error:', e);
    }
  }

  loadParticipants();
  setInterval(loadParticipants, 4000);

  /* --------------------------------------------------
     🔄 PRESENCE PING (STOPS WHEN REMOVED)
  -------------------------------------------------- */
  pingTimer = setInterval(() => {
    if (accessRevoked) return;

    fetch('api/ping_room.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'room_id=' + ROOM_ID
    });
  }, 15000);
});
