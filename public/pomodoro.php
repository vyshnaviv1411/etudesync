<?php
$page_title = 'FocusFlow — Pomodoro';
require_once __DIR__ . '/../includes/header_public.php';
?>

<link rel="stylesheet" href="assets/css/focusflow.css">

<div class="focusflow-container">
    <h2 class="ff-title">Pomodoro Timer</h2>

    <div class="pomodoro-box">
        <h1 id="timer-display">25:00</h1>

        <div class="pomodoro-buttons">
            <button id="startBtn" class="btn primary">Start</button>
            <button id="pauseBtn" class="btn">Pause</button>
            <button id="resetBtn" class="btn">Reset</button>
        </div>

        <p class="ff-note">Your timer will auto-save even if you reload the page.</p>
    </div>
</div>

<script src="assets/js/focusflow/pomodoro.js" defer></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
