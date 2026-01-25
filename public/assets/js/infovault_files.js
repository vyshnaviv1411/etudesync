document.addEventListener('click', async (e) => {
  if (!e.target.classList.contains('share-btn')) return;

  const fileId = e.target.dataset.fileId;
  const roomId = sessionStorage.getItem('ACTIVE_ROOM_ID');

  if (!roomId) {
    alert('Open a room first');
    return;
  }

  const res = await fetch('api/share_to_room.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `room_id=${roomId}&file_id=${fileId}`
  });

  const data = await res.json();
  if (data.success) {
    alert('File shared to room');
    window.location.href = `room.php?room_id=${roomId}`;
  } else {
    alert(data.error || 'Failed');
  }
});
