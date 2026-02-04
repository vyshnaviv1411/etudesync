<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* =======================
   USERS
======================= */
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$premiumUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_premium = 1")->fetchColumn();
$freeUsers = $totalUsers - $premiumUsers;

/* =======================
   PRODUCTIVITY
======================= */
$pomodoros = $pdo->query("SELECT COUNT(*) FROM pomodoro_sessions WHERE completed = 1")->fetchColumn();
$todosDone = $pdo->query("SELECT COUNT(*) FROM todos WHERE status='completed'")->fetchColumn();

/* =======================
   WELLBEING
======================= */
$journals = $pdo->query("SELECT COUNT(*) FROM journal_entries")->fetchColumn();
$moods = $pdo->query("SELECT COUNT(*) FROM mood_tracker")->fetchColumn();

/* =======================
   COLLAB
======================= */
$rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

/* =======================
   ACCESSARENA
======================= */
$quizzes = $pdo->query("SELECT COUNT(*) FROM accessarena_quizzes")->fetchColumn();
$participants = $pdo->query("SELECT COUNT(*) FROM accessarena_participants")->fetchColumn();

/* =======================
   CONTENT
======================= */
$music = $pdo->query("SELECT COUNT(*) FROM background_music WHERE is_active=1")->fetchColumn();
$quotes = $pdo->query("SELECT COUNT(*) FROM dashboard_quotes WHERE is_active=1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Reports – ÉtudeSync</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
  margin:0;
  min-height:100vh;
  font-family:Poppins,sans-serif;
  background:url("../assets/images/admin.jpg") center/cover no-repeat;
  padding:80px 40px;
}

.glass{
  background:rgba(255,255,255,.38);
  backdrop-filter:blur(18px);
  border-radius:28px;
  padding:40px;
  box-shadow:0 40px 90px rgba(0,0,0,.35);
}

h1{color:#0f172a;margin-bottom:6px;}
.subtitle{color:#334155;margin-bottom:30px;}

.grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:22px;
}

.card{
  background:rgba(255,255,255,.55);
  border-radius:20px;
  padding:22px;
  box-shadow:0 18px 40px rgba(0,0,0,.25);
}

.metric{
  font-size:2rem;
  font-weight:800;
  color:#1e293b;
}

.label{color:#475569;font-size:14px;}

canvas{margin-top:10px;}
</style>
</head>

<body>

<div class="glass">

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
  <div>
    <h1>📊 Platform Reports</h1>
    <div class="subtitle">
      System-wide analytics and usage overview (read-only)
    </div>
  </div>

  <a href="dashboard.php"
     style="
       text-decoration:none;
       padding:10px 18px;
       border-radius:12px;
       background:#0f172a;
       color:#fff;
       font-weight:600;
       box-shadow:0 10px 25px rgba(0,0,0,0.3);
     ">
    ← Dashboard
  </a>
</div>



<div class="grid">

<!-- USERS -->
<div class="card">
  <h3>👤 Users</h3>
  <div class="metric"><?= $totalUsers ?></div>
  <div class="label">Total Users</div>
  <canvas id="usersChart"></canvas>
</div>

<!-- PRODUCTIVITY -->
<div class="card">
  <h3>⏱ Productivity</h3>
  <div class="metric"><?= $pomodoros ?></div>
  <div class="label">Pomodoro Sessions</div>
  <canvas id="productivityChart"></canvas>
</div>

<!-- WELLBEING -->
<div class="card">
  <h3>🧠 Wellbeing</h3>
  <div class="metric"><?= $journals ?></div>
  <div class="label">Journal Entries</div>
  <canvas id="wellbeingChart"></canvas>
</div>

<!-- COLLAB -->
<div class="card">
  <h3>🤝 Collaboration</h3>
  <div class="metric"><?= $rooms ?></div>
  <div class="label">Study Rooms</div>
  <canvas id="collabChart"></canvas>
</div>

<!-- ACCESSARENA -->
<div class="card">
  <h3>🎯 AccessArena</h3>
  <div class="metric"><?= $quizzes ?></div>
  <div class="label">Quizzes</div>
  <canvas id="quizChart"></canvas>
</div>

<!-- CONTENT -->
<div class="card">
  <h3>🎵 Content</h3>
  <div class="metric"><?= $music ?></div>
  <div class="label">Active Music</div>
  <canvas id="contentChart"></canvas>
</div>

</div>
</div>

<script>
new Chart(usersChart,{
  type:'doughnut',
  data:{
    labels:['Premium','Free'],
    datasets:[{data:[<?= $premiumUsers ?>,<?= $freeUsers ?>],
    backgroundColor:['#6366f1','#94a3b8']}]
  }
});

new Chart(productivityChart,{
  type:'bar',
  data:{
    labels:['Pomodoro','Todos'],
    datasets:[{data:[<?= $pomodoros ?>,<?= $todosDone ?>],
    backgroundColor:'#22d3ee'}]
  },
  options:{plugins:{legend:{display:false}}}
});

new Chart(wellbeingChart,{
  type:'bar',
  data:{
    labels:['Journals','Mood Logs'],
    datasets:[{data:[<?= $journals ?>,<?= $moods ?>],
    backgroundColor:'#a855f7'}]
  },
  options:{plugins:{legend:{display:false}}}
});

new Chart(collabChart,{
  type:'bar',
  data:{
    labels:['Rooms','Messages'],
    datasets:[{data:[<?= $rooms ?>,<?= $messages ?>],
    backgroundColor:'#34d399'}]
  },
  options:{plugins:{legend:{display:false}}}
});

new Chart(quizChart,{
  type:'bar',
  data:{
    labels:['Quizzes','Participants'],
    datasets:[{data:[<?= $quizzes ?>,<?= $participants ?>],
    backgroundColor:'#fb7185'}]
  },
  options:{plugins:{legend:{display:false}}}
});

new Chart(contentChart,{
  type:'bar',
  data:{
    labels:['Music','Quotes'],
    datasets:[{data:[<?= $music ?>,<?= $quotes ?>],
    backgroundColor:'#facc15'}]
  },
  options:{plugins:{legend:{display:false}}}
});
</script>

</body>
</html>
