<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['admin_id'])) exit;

if (!empty($_FILES['music']['name'])) {
  $title = $_POST['title'];
  $filename = time().'_'.basename($_FILES['music']['name']);
  $relativePath = "assets/music/".$filename;
  $target = __DIR__ . '/../'.$relativePath;

  if (move_uploaded_file($_FILES['music']['tmp_name'], $target)) {
    $stmt = $pdo->prepare(
      "INSERT INTO background_music (title, file_path) VALUES (?, ?)"
    );
    $stmt->execute([$title, $relativePath]);
  }
}

header("Location: music.php");
