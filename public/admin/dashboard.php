<?php
session_start();
if (empty($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

$adminName = $_SESSION['admin_name'];
$initial = strtoupper($adminName[0]);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<style>
body {
  margin: 0;
  min-height: 100vh;
  font-family: "Poppins", sans-serif;
  background: url("../assets/images/admin.jpg") center/cover no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Top bar */
.topbar {
  position: absolute;
  top: 24px;
  left: 32px;
  right: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #0f172a;
}

.brand {
  font-size: 1.6rem;
  font-weight: 800;
}

/* Profile */
.profile-box {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg,#6366f1,#22d3ee);
  color: #fff;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-actions a {
  text-decoration: none;
  padding: 8px 14px;
  border-radius: 10px;
  font-weight: 600;
}

.profile-btn {
  background: rgba(255,255,255,0.55);
  color: #0f172a;
}

.logout-btn {
  background: #0f172a;
  color: #fff;
}

/* Glass container */
.dashboard {
  width: 78%;
  padding: 40px;
  border-radius: 26px;
  background: rgba(255,255,255,0.35);
  backdrop-filter: blur(18px);
  box-shadow: 0 30px 90px rgba(0,0,0,0.35);
}

.dashboard h2 {
  margin-top: 0;
  color: #0f172a;
}

.dashboard p {
  color: #334155;
  margin-bottom: 30px;
}

/* Cards */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
  gap: 22px;
}

.card {
  padding: 24px;
  border-radius: 18px;
  background: rgba(255,255,255,0.45);
  text-align: center;
  color: #0f172a;
  font-weight: 600;
  box-shadow: 0 18px 40px rgba(0,0,0,0.25);
  transition: transform .2s;
}

.card:hover {
  transform: translateY(-6px);
}
.card {
  text-decoration: none;
  cursor: pointer;
}

.card:visited {
  color: #0f172a;
}

</style>
</head>

<body>

<!-- Top bar -->
<div class="topbar">
  <div class="brand">ÉtudeSync Admin</div>

  <div class="profile-box">
    <div class="avatar"><?= $initial ?></div>
    <div class="profile-actions">
      <a href="admin-profile.php" class="profile-btn"><?= htmlspecialchars($adminName) ?></a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>
</div>

<!-- Dashboard -->
<div class="dashboard">
  <h2>Admin Dashboard</h2>
  <p>Manage platform content, pricing, and analytics from one place.</p>

  <div class="card-grid">
  <a href="music.php" class="card">
    🎵 Music Management
  </a>

  <a href="quotes.php" class="card">
    💬 Quotes Management
  </a>

  <a href="reports.php" class="card">
    📊 Reports
  </a>
</div>


</body>
</html>
