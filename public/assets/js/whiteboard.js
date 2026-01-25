document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('wbCanvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');

  const brushInput = document.getElementById('wbBrushSize');
  const colorInput = document.getElementById('wbColor');
  const undoBtn = document.getElementById('wbUndo');
  const clearBtn = document.getElementById('wbClear');
  const saveBtn = document.getElementById('wbSave');
  const exportBtn = document.getElementById('wbExport');

  let drawing = false;
  let lastX = 0;
  let lastY = 0;
  let brushSize = 3;
  let brushColor = '#ffffff';
  let history = [];
  let lastRemoteData = null;
  

  /* ------------------ CANVAS SETUP ------------------ */

  function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;

    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    redrawHistory();
  }

  window.addEventListener('resize', resizeCanvas);
  resizeCanvas();

  /* ------------------ DRAWING ------------------ */

  function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    return {
      x: e.clientX - rect.left,
      y: e.clientY - rect.top
    };
  }

  function startDraw(e) {
    drawing = true;
    const pos = getPos(e);
    lastX = pos.x;
    lastY = pos.y;
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
  }

  function draw(e) {
    if (!drawing) return;

    const pos = getPos(e);

    ctx.strokeStyle = brushColor;
    ctx.lineWidth = brushSize;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();

    lastX = pos.x;
    lastY = pos.y;
  }

  function endDraw() {
    if (!drawing) return;
    drawing = false;
    ctx.closePath();
    saveState();
    autoSave(); // 🔴 send to server
  }

  /* ------------------ HISTORY ------------------ */

  function saveState() {
    history.push(canvas.toDataURL());
    if (history.length > 50) history.shift();
  }

  function redrawHistory() {
    if (!history.length) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      return;
    }
    const img = new Image();
    img.src = history[history.length - 1];
    img.onload = () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(
        img,
        0,
        0,
        canvas.width / (window.devicePixelRatio || 1),
        canvas.height / (window.devicePixelRatio || 1)
      );
    };
  }

  /* ------------------ SERVER SYNC ------------------ */

  function autoSave() {
    fetch('api/save_whiteboard.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: new URLSearchParams({
        room_id: ROOM_ID,
        data: canvas.toDataURL()
      })
    });
  }

  async function fetchRemoteBoard() {
  if (drawing) return;

  try {
    const res = await fetch(`api/fetch_whiteboard.php?room_id=${ROOM_ID}`, {
      credentials: 'same-origin'
    });
    const j = await res.json();

    if (!j.success || !j.data || j.data === lastRemoteData) return;

    lastRemoteData = j.data;

    const img = new Image();
    img.src = j.data;
    img.onload = () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(
        img,
        0,
        0,
        canvas.width / (window.devicePixelRatio || 1),
        canvas.height / (window.devicePixelRatio || 1)
      );
      history = [j.data];
    };
  } catch (e) {
    console.error('Whiteboard sync error', e);
  }
}


  setInterval(fetchRemoteBoard, 2000); // ⏱ polling

  /* ------------------ EVENTS ------------------ */

  canvas.addEventListener('mousedown', startDraw);
  canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', endDraw);
  canvas.addEventListener('mouseleave', endDraw);

  brushInput.addEventListener('input', () => {
    brushSize = Number(brushInput.value);
  });

  colorInput.addEventListener('input', () => {
    brushColor = colorInput.value;
  });

  undoBtn.addEventListener('click', () => {
    if (history.length > 1) {
      history.pop();
      redrawHistory();
      autoSave();
    } else {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      history = [];
      autoSave();
    }
  });

  clearBtn.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    history = [];
    autoSave();
  });

  exportBtn.addEventListener('click', () => {
    const link = document.createElement('a');
    link.download = 'whiteboard.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  });

  saveBtn.addEventListener('click', saveState);
});
