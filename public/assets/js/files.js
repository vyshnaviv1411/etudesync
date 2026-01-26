// etudesync/assets/js/files.js
document.addEventListener('DOMContentLoaded', () => {

  const filesList = document.getElementById('filesList');
  if (!filesList) return;

  // Provided by room.php
  const roomId = typeof ROOM_ID !== 'undefined' ? ROOM_ID : null;
  if (!roomId) return;

  /* =========================
     FETCH ROOM FILES
  ========================== */
  async function fetchFiles() {
    try {
      const res = await fetch(
        `api/fetch_files.php?room_id=${roomId}`,
        { credentials: 'same-origin' }
      );

      if (!res.ok) {
        console.error('Fetch failed:', res.status);
        return;
      }

      const data = await res.json();
      if (!data.success) return;

      renderFiles(data.files || []);
    } catch (err) {
      console.error('Fetch files error:', err);
    }
  }

  /* =========================
     RENDER FILE LIST
  ========================== */
  function renderFiles(files) {
    filesList.innerHTML = '';

    if (!files.length) {
      filesList.innerHTML =
        `<div class="small-muted">No files shared in this room.</div>`;
      return;
    }

    files.forEach(f => {
      const row = document.createElement('div');
      row.className = 'file-row glass-card';
      row.style.display = 'flex';
      row.style.justifyContent = 'space-between';
      row.style.alignItems = 'center';
      row.style.gap = '12px';
      row.style.padding = '10px';

      const fileUrl = assetUrl(f.file_path);

      row.innerHTML = `
        <div>
          <div style="font-weight:600">
            ${escapeHtml(f.file_name)}
          </div>
          <div class="small-muted">
        Shared by ${escapeHtml(f.user_name || 'Unknown')}

          </div>
        </div>

        <div>
          <a href="${fileUrl}"
             target="_blank"
             class="btn small outline">
            Open
          </a>
        </div>
      `;

      filesList.appendChild(row);
    });
  }

  /* =========================
     HELPERS
  ========================= */

  function assetUrl(path) {
    if (!path) return '';
    return '/etudesync/' + String(path).replace(/^\/+/, '');
  }

  // XSS-safe rendering
  function escapeHtml(value) {
    if (!value) return '';
    return String(value).replace(/[&<>"']/g, c => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[c]);
  }

  /* =========================
     INIT
  ========================== */
  fetchFiles();
  setInterval(fetchFiles, 8000);

});
