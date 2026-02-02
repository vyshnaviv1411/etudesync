<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid = (int)$_SESSION['user_id'];
$mindmap_id = (int)($_GET['mindmap_id'] ?? 0);

/* Ownership check */
$stmt = $pdo->prepare("
  SELECT * FROM mindmaps
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$mindmap_id, $uid]);
$mindmap = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mindmap) {
  echo "Invalid mindmap";
  exit;
}

/* Fetch nodes */
$stmt = $pdo->prepare("
  SELECT * FROM mindmap_nodes
  WHERE mindmap_id = ?
");
$stmt->execute([$mindmap_id]);
$nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<script>
  document.body.classList.add('dashboard-page','mindmap-editor-page');
</script>

<!-- BACKGROUND -->
<div class="dashboard-bg" style="
  background-image:url('assets/images/infovault_bg.jpg');
  background-size:cover;
  background-position:center;">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css">

<div class="collab-viewport">
<div class="collab-hero">
<div class="collab-card" style="max-width:1100px;">

  <div class="collab-card-head">
    <h1><?= e($mindmap['title']) ?></h1>
    <p class="lead">Build and organize your ideas visually</p>
  </div>

  <!-- SIMPLE INSTRUCTIONS -->
<div class="mindmap-instructions glass-card"
     style="margin:14px auto;padding:16px 20px;max-width:720px;text-align:left;">

  <h4 style="margin:0 0 10px 0;text-align:center;">
    How to use
  </h4>

  <ul style="
      margin:0;
      padding-left:18px;
      line-height:1.5;
      list-style-position: outside;
    ">
    <li>Click a node to select it</li>
    <li>Select a node → click <b>+ Add Node</b> to create a branch</li>
    <li>Double-click a node to edit text</li>
    <li>Drag nodes to reposition them</li>
    <li>Select a node → click <b>Delete</b> or press <b>Delete</b> key</li>
    <li>Click <b>Save</b> to export the mind map</li>
  </ul>

  <p style="margin:10px 0 0;font-size:0.85rem;opacity:0.8;">
    Tip: Select a parent node before adding new branches.
  </p>
</div>


  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <a href="infovault_mindmaps.php" class="btn primary small">← Back</a>

    <div style="display:flex;gap:10px;">
      <button class="btn small outline" onclick="addNode()">+ Add Node</button>
      <button class="btn small danger" onclick="deleteSelected()">🗑 Delete</button>
      <button class="btn small outline" onclick="saveToLocal()">💾 Save</button>
      <a href="infovault_mindmaps.php" class="btn primary small">✔ Done</a>
    </div>
  </div>

  <!-- ✅ CANVAS (FIXED STRUCTURE) -->
  <div id="mindmap-canvas">
    <div id="mindmap-workspace">
      <svg id="connections"></svg>
    </div>
  </div>

</div>
</div>
</div>

<style>
#mindmap-canvas{
  position:relative;
  height:520px;
  overflow:auto;
  border-radius:18px;
  background:rgba(15,20,30,0.35);
  backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,0.08);
}

/* ✅ REAL WORKSPACE */
#mindmap-workspace{
  position:relative;
  width:3000px;
  height:2000px;
}

/* ✅ SVG follows workspace (NO distortion) */
#connections{
  position:absolute;
  top:0;
  left:0;
  width:3000px;
  height:2000px;
  pointer-events:none;
  z-index:1;
}

.mindmap-node{
  position:absolute;
  min-width:160px;
  padding:16px 20px;
  border-radius:16px;
  background:rgba(25,30,40,0.85);
  color:#fff;
  font-weight:600;
  text-align:center;
  cursor:move;
  user-select:none;
  box-shadow:0 18px 40px rgba(0,0,0,0.35);
  border:1px solid rgba(255,255,255,0.12);
  z-index:2;
}

.mindmap-node.root{
  font-size:1.1rem;
  background:rgba(45,50,65,0.95);
}

.mindmap-node.child{
  font-size:0.95rem;
}

.mindmap-node.selected{
  outline:2px solid #7c4dff;
}

.mindmap-node input{
  background:transparent;
  border:none;
  color:#fff;
  font-size:1rem;
  width:100%;
  text-align:center;
  outline:none;
}
</style>

<script>
const mindmapId = <?= $mindmap_id ?>;
const nodes = <?= json_encode($nodes) ?>;

const canvas = document.getElementById('mindmap-canvas');
const workspace = document.getElementById('mindmap-workspace');
const svg = document.getElementById('connections');

const nodeMap = {};
let selectedNode = null;
let isDeleting = false;

/* Render existing nodes */
nodes.forEach(n => {
  createNode(n.id, n.text, n.x, n.y, n.parent_id);
});

window.onload = drawLines;
window.onresize = drawLines;

function createNode(id, text, x, y, parent){
  const el = document.createElement('div');
  el.className = 'mindmap-node ' + (parent ? 'child' : 'root');
  el.style.left = x + 'px';
  el.style.top  = y + 'px';
  el.dataset.id = id;
  el.dataset.parent = parent ?? '';
  el.innerHTML = `<input value="${text}">`;

  workspace.appendChild(el);
  nodeMap[id] = el;

  el.onclick = e => {
    e.stopPropagation();
    selectNode(el);
  };

  el.addEventListener('mousedown', e => {
    if (e.target.tagName === 'INPUT') return;

    const startX = e.clientX;
    const startY = e.clientY;
    const rect = el.getBoundingClientRect();
    const canvasRect = workspace.getBoundingClientRect();

    function onMove(ev){
      el.style.left = (rect.left + ev.clientX - startX - canvasRect.left) + 'px';
      el.style.top  = (rect.top  + ev.clientY - startY - canvasRect.top) + 'px';
      drawLines();
    }

    function onUp(){
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', onUp);
      saveNode(el);
    }

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp);
  });

  el.querySelector('input').onblur = () => saveNode(el);
}

function selectNode(el){
  Object.values(nodeMap).forEach(n => n.classList.remove('selected'));
  el.classList.add('selected');
  selectedNode = el;
}

function drawLines(){
  svg.innerHTML = '';
  const rect = workspace.getBoundingClientRect();

  Object.values(nodeMap).forEach(el => {
    const pid = el.dataset.parent;
    if (!pid || !nodeMap[pid]) return;

    const p = nodeMap[pid].getBoundingClientRect();
    const c = el.getBoundingClientRect();

    const line = document.createElementNS("http://www.w3.org/2000/svg","line");
    line.setAttribute('x1', p.left + p.width/2 - rect.left);
    line.setAttribute('y1', p.top + p.height/2 - rect.top);
    line.setAttribute('x2', c.left + c.width/2 - rect.left);
    line.setAttribute('y2', c.top + c.height/2 - rect.top);
    line.setAttribute('stroke','rgba(255,255,255,0.4)');
    line.setAttribute('stroke-width','1.8');
    line.setAttribute('stroke-linecap','round');
    svg.appendChild(line);
  });
}

function saveNode(el){
  if (isDeleting) return;

  fetch('api/update_mindmap_node.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({
      id: el.dataset.id,
      x: el.offsetLeft,
      y: el.offsetTop,
      text: el.querySelector('input').value.trim()
    })
  });
}

function addNode(){
  const parentId = selectedNode ? selectedNode.dataset.id : null;

  fetch('api/add_mindmap_node.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({ mindmap_id: mindmapId, parent_id: parentId })
  })
  .then(r=>r.json())
  .then(n=>{
    let x = n.x;
    let y = n.y;

    if (parentId && nodeMap[parentId]) {
      const siblings = Object.values(nodeMap)
        .filter(el => el.dataset.parent === parentId);

      const parent = nodeMap[parentId];
      x = parent.offsetLeft + 260;
      y = parent.offsetTop + (siblings.length * 90);
    }

    createNode(n.id, n.text, x, y, n.parent_id);
    drawLines();
  });
}

function deleteSelected(){
  if (!selectedNode) return alert('Select a node first');
  if (!confirm('Delete this node and its branches?')) return;

  isDeleting = true;
  fetch('api/delete_mindmap_node.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({ id: selectedNode.dataset.id })
  }).then(()=>location.reload());
}

document.addEventListener('keydown', e => {
  if (e.key === 'Delete' && selectedNode) deleteSelected();
});

function saveToLocal(){
  html2canvas(workspace,{backgroundColor:'#0f1420'}).then(c=>{
    const link = document.createElement('a');
    link.download = 'mindmap.png';
    link.href = c.toDataURL();
    link.click();
  });
}

canvas.onclick = () => {
  Object.values(nodeMap).forEach(n => n.classList.remove('selected'));
  selectedNode = null;
};
</script>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
