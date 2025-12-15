// todo.js
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('todo-form');
  const list = document.getElementById('todo-list');

  async function loadTodos() {
    try {
      const res = await fetch('/api/todo_list.php');
      if (!res.ok) throw new Error('Network response not ok');
      const items = await res.json();
    list.innerHTML = '';
      if(!Array.isArray(items)) return;
    items.forEach(item => {
      const li = document.createElement('li');
      li.dataset.id = item.id;

      const left = document.createElement('div');
      left.className = 'task-left';

      const chk = document.createElement('input');
      chk.type = 'checkbox';
      chk.checked = item.status === 'done';
      chk.className = 'todo-checkbox';
      chk.dataset.id = item.id;

      const text = document.createElement('span');
      text.innerText = item.task;
      if(item.status === 'done'){
        text.style.textDecoration = 'line-through';
        text.style.opacity = '0.7';
      }

      const due = document.createElement('small');
      due.className = 'task-due';
      due.innerText = item.due_date ? `Due: ${item.due_date}` : '';

      left.appendChild(chk);
      left.appendChild(text);
      left.appendChild(due);

      const right = document.createElement('div');
      const delBtn = document.createElement('button');
      delBtn.className = 'small-btn';
      delBtn.dataset.id = item.id;
      delBtn.innerText = 'Delete';
      delBtn.style.background = '#dc3545';
      delBtn.style.color = '#fff';

      right.appendChild(delBtn);

      li.appendChild(left);
      li.appendChild(right);
      list.appendChild(li);
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    try {
      const res = await fetch('/api/todo_add.php', { method: 'POST', body: fd });
      const j = await res.json();
    if (j.ok) {
      form.reset();
      loadTodos();
    } else {
      alert(j.msg || 'Error adding task');
    }
    } catch (err) {
      console.error(err);
      alert('Network error adding task');
    }
  });

  list.addEventListener('change', async (e) => {
    if (e.target.matches('.todo-checkbox')) {
      const id = e.target.dataset.id;
      const status = e.target.checked ? 'done' : 'pending';
      try {
        const r = await fetch('/api/todo_update.php', { method:'POST', body: new URLSearchParams({ id, status }) });
        if (r.ok) loadTodos();
      } catch (err) { console.error(err); }
    }
  });

  list.addEventListener('click', async (e) => {
    if (e.target.tagName === 'BUTTON' && e.target.dataset.id) {
      const id = e.target.dataset.id;
      if (!confirm('Delete this task?')) return;
      try {
        const r = await fetch('/api/todo_delete.php', { method:'POST', body: new URLSearchParams({ id }) });
        if (r.ok) loadTodos();
      } catch (err) { console.error(err); }
    }
  });

  loadTodos();
});
