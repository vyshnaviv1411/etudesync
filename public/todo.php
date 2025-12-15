<?php
$page_title = 'FocusFlow — To-Do';
require_once __DIR__ . '/../includes/header_public.php';
?>

<div class="page-hero">
  <div class="page-hero-inner">
    <h1>FocusFlow — To-Do</h1>

    <form id="todo-form">
      <input type="text" id="task" name="task" placeholder="Add a task (e.g., Read Ch.3)" required>
      <input type="date" id="due_date" name="due_date">
      <button type="submit" class="btn primary">Add</button>
    </form>

    <h3>Your tasks</h3>
    <ul id="todo-list"></ul>
  </div>
</div>

<script src="assets/js/todo.js" defer></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
