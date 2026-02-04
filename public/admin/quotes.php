<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

/* -----------------------
   ADD QUOTE
------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_quote'])) {
  $quote = trim($_POST['new_quote']);

  if ($quote !== '') {
    $stmt = $pdo->prepare(
      "INSERT INTO dashboard_quotes (quote_text) VALUES (?)"
    );
    $stmt->execute([$quote]);
  }
  header("Location: quotes.php");
  exit;
}

/* -----------------------
   TOGGLE ACTIVE
------------------------ */
if (isset($_GET['toggle'])) {
  $id = (int)$_GET['toggle'];

  $pdo->query(
    "UPDATE dashboard_quotes
     SET is_active = IF(is_active = 1, 0, 1)
     WHERE quote_id = $id"
  );

  header("Location: quotes.php");
  exit;
}

/* -----------------------
   FETCH QUOTES
------------------------ */
$quotes = $pdo
  ->query("SELECT * FROM dashboard_quotes ORDER BY created_at DESC")
  ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quotes Management</title>

<style>
body {
  margin: 0;
  min-height: 100vh;
  font-family: Poppins, sans-serif;
  background: url("../assets/images/admin.jpg") center/cover no-repeat;
  display: flex;
  justify-content: center;
  padding-top: 90px;
}

/* GLASS CONTAINER */
.container {
  width: 80%;
  background: rgba(255,255,255,0.35);
  backdrop-filter: blur(18px);
  border-radius: 26px;
  padding: 40px;
  box-shadow: 0 30px 90px rgba(0,0,0,0.35);
}

/* HEADER */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.header h2 {
  margin: 0;
  color: #0f172a;
}

.back {
  text-decoration: none;
  background: #0f172a;
  color: #fff;
  padding: 10px 18px;
  border-radius: 12px;
  font-weight: 600;
}

/* ADD QUOTE */
.add-box textarea {
  width: 100%;
  height: 90px;
  border-radius: 14px;
  border: none;
  padding: 14px;
  font-size: 15px;
  resize: none;
}

.add-box button {
  margin-top: 12px;
  padding: 12px 22px;
  border-radius: 14px;
  border: none;
  font-weight: 700;
  background: linear-gradient(90deg,#7c4dff,#47d7d3);
  color: #fff;
  cursor: pointer;
}

/* TABLE */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 30px;
}

th, td {
  padding: 14px;
  text-align: left;
}

th {
  color: #0f172a;
  font-weight: 700;
}

tr {
  background: rgba(255,255,255,0.45);
  border-radius: 14px;
}

tr + tr {
  margin-top: 10px;
}

.status {
  font-weight: 700;
}

.active {
  color: green;
}

.inactive {
  color: crimson;
}

.toggle {
  text-decoration: none;
  padding: 8px 14px;
  border-radius: 10px;
  background: #0f172a;
  color: #fff;
  font-weight: 600;
}
</style>
</head>

<body>

<div class="container">

  <div class="header">
    <h2>Quotes Management</h2>
    <a href="dashboard.php" class="back">← Dashboard</a>
  </div>

  <!-- ADD QUOTE -->
  <form method="POST" class="add-box">
    <textarea
      name="new_quote"
      placeholder="Enter motivational quote (emojis supported 🌿✨)"
      required></textarea>
    <button>Add Quote</button>
  </form>

  <!-- LIST -->
  <table>
    <thead>
      <tr>
        <th>Quote</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($quotes as $q): ?>
      <tr>
        <td><?= htmlspecialchars($q['quote_text']) ?></td>
        <td class="status <?= $q['is_active'] ? 'active' : 'inactive' ?>">
          <?= $q['is_active'] ? 'Active' : 'Inactive' ?>
        </td>
        <td>
          <a class="toggle" href="?toggle=<?= $q['quote_id'] ?>">
            <?= $q['is_active'] ? 'Disable' : 'Enable' ?>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

</div>

</body>
</html>
