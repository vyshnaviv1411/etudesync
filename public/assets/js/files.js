// public/assets/js/files.js
document.addEventListener('DOMContentLoaded', () => {

  const uploadForm = document.getElementById('fileUploadForm');
  const fileInput  = document.getElementById('fileInput');
  const filesList  = document.getElementById('filesList');

  if (!uploadForm || !fileInput || !filesList) return;

  const roomId = uploadForm.dataset.room;
  if (!roomId) return;

  /* =========================
     FETCH FILES
  ========================== */
  async function fetchFiles() {
    try {
      const res = await fetch(
        `api/fetch_files.php?room_id=${roomId}`,
        { credentials: 'same-origin' }
      );

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
        `<div style="color:var(--muted)">No files uploaded yet.</div>`;
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

      row.innerHTML = `
        <div class="file-meta">
          <div class="file-name" style="font-weight:600">
            ${escapeHtml(f.file_name)}
          </div>
          <div class="file-sub small-muted">
            ${escapeHtml(f.user_name || 'Unknown')}
            • ${f.size_readable}
            • ${new Date(f.uploaded_at).toLocaleString()}
          </div>
        </div>

        <div class="file-actions">
          <a class="btn small outline"
   href="/${f.file_path}"
   target="_blank">
   Open
</a>

        </div>
      `;

      filesList.appendChild(row);
    });
  }

  /* =========================
     UPLOAD FILE
  ========================== */
  uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!fileInput.files.length) {
      alert('Please choose a file');
      return;
    }

    const fd = new FormData();
    fd.append('room_id', roomId);
    fd.append('file', fileInput.files[0]);

    const btn = uploadForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Uploading…';

    try {
      const res = await fetch('api/upload_file.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });

      const data = await res.json();

      if (data.success) {
        fileInput.value = '';
        fetchFiles();
      } else {
        alert('Upload failed: ' + (data.error || 'Unknown error'));
      }

    } catch (err) {
      console.error('Upload error:', err);
      alert('Upload failed. Check console.');
    }

    btn.disabled = false;
    btn.textContent = 'Upload';
  });

  /* =========================
     UTILITY
  ========================== */
  function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, (m) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    }[m]));
  }

  /* =========================
     INIT
  ========================== */
  fetchFiles();
  setInterval(fetchFiles, 8000); // refresh every 8s

});
