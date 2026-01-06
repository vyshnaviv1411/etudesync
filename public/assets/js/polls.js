document.addEventListener('DOMContentLoaded', () => {
  const pollArea = document.getElementById('pollArea');
  const createBtn = document.getElementById('createPollBtn');

  if (!pollArea) return;

  async function loadPolls() {
    const res = await fetch(`api/fetch_polls.php?room_id=${ROOM_ID}`);
    const data = await res.json();
    if (!data.success) return;

    pollArea.innerHTML = '';

    data.polls.forEach(p => {
      const total = p.votes_a + p.votes_b || 1;

      const div = document.createElement('div');
      div.className = 'glass-card';
      div.style.marginBottom = '12px';
      div.innerHTML = `
        <strong>${p.question}</strong>
        <div style="margin-top:8px">
          <button class="btn small outline" data-id="${p.poll_id}" data-opt="A">
            ${p.option_a} (${p.votes_a})
          </button>
          <button class="btn small outline" data-id="${p.poll_id}" data-opt="B">
            ${p.option_b} (${p.votes_b})
          </button>
        </div>
      `;
      pollArea.appendChild(div);
    });
  }

  createBtn?.addEventListener('click', async () => {
    const q = document.getElementById('pollQ').value.trim();
    const o1 = document.getElementById('pollOpt1').value.trim();
    const o2 = document.getElementById('pollOpt2').value.trim();
    if (!q || !o1 || !o2) return alert('Fill all fields');

    await fetch('api/create_poll.php', {
      method:'POST',
      body:new URLSearchParams({
        room_id: ROOM_ID,
        question:q,
        opt1:o1,
        opt2:o2
      })
    });

    document.getElementById('pollQ').value='';
    document.getElementById('pollOpt1').value='';
    document.getElementById('pollOpt2').value='';

    loadPolls();
  });

  pollArea.addEventListener('click', async e => {
    if (!e.target.dataset.id) return;
    await fetch('api/vote_poll.php', {
      method:'POST',
      body:new URLSearchParams({
        poll_id: e.target.dataset.id,
        option: e.target.dataset.opt
      })
    });
    loadPolls();
  });

  loadPolls();
  setInterval(loadPolls, 5000);
});
