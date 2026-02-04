<?php
session_start();
if (empty($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Profile</title>

<style>
* {
  box-sizing: border-box;
  font-family: "Poppins", system-ui, sans-serif;
}

body {
  margin: 0;
  min-height: 100vh;
  background: url("../assets/images/admin.jpg") center/cover no-repeat fixed;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f172a;
}

/* soft overlay to match dashboard */
body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.32);
  backdrop-filter: blur(2px);
  z-index: -1;
}

/* MAIN GLASS CARD */
.profile-card {
  width: 460px;
  padding: 38px 36px;
  border-radius: 26px;
  background: rgba(255,255,255,0.35);
  backdrop-filter: blur(20px) saturate(140%);
  border: 1px solid rgba(255,255,255,0.35);
  box-shadow: 0 40px 90px rgba(0,0,0,0.35);
}

/* HEADER */
.profile-card h2 {
  text-align: center;
  margin-bottom: 28px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.3px;
}

/* PROFILE ROWS */
.profile-row {
  margin-bottom: 18px;
  font-weight: 600;
  color: #1e293b;
}

.profile-row span {
  display: block;
  margin-top: 6px;
  padding: 10px 14px;
  border-radius: 12px;
  background: rgba(255,255,255,0.55);
  color: #0f172a;
  font-weight: 500;
  word-break: break-all;
}

/* ACTION BUTTONS */
.actions {
  margin-top: 32px;
  display: flex;
  justify-content: space-between;
  gap: 14px;
}

.actions a {
  flex: 1;
  text-align: center;
  text-decoration: none;
  padding: 12px 0;
  border-radius: 14px;
  font-weight: 700;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

/* DASHBOARD BUTTON */
.actions .back {
  background: rgba(255,255,255,0.65);
  color: #0f172a;
  box-shadow: 0 10px 30px rgba(15,23,42,0.25);
}

.actions .back:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 45px rgba(15,23,42,0.35);
}

/* LOGOUT BUTTON */
.actions .logout {
  background: linear-gradient(90deg, #ef4444, #b91c1c);
  color: #fff;
  box-shadow: 0 14px 40px rgba(127,29,29,0.45);
}

.actions .logout:hover {
  transform: translateY(-2px);
  box-shadow: 0 22px 60px rgba(127,29,29,0.65);
}

/* MOBILE */
@media (max-width: 520px) {
  .profile-card {
    width: 92%;
    padding: 28px 22px;
  }
}
</style>
</head>

<body>

<div class="profile-card">
  <h2>Admin Profile</h2>

  <div class="profile-row">
    Name
    <span><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
  </div>

  <div class="profile-row">
    Email
    <span><?= htmlspecialchars($_SESSION['admin_email']) ?></span>
  </div>

  <div class="profile-row">
    Admin ID
    <span><?= $_SESSION['admin_id'] ?></span>
  </div>

  <div class="profile-row">
    Role
    <span>System Administrator</span>
  </div>

  <div class="actions">
    <a href="dashboard.php" class="back">← Dashboard</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

</body>
</html>
