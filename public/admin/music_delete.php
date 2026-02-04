<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['admin_id'])) exit;

$id = $_GET['id'] ?? null;

if ($id) {
  $stmt = $pdo->prepare("SELECT file_path FROM background_music WHERE music_id=?");
  $stmt->execute([$id]);
  $track = $stmt->fetch();

  if ($track) {
    @unlink(__DIR__ . '/../'.$track['file_path']);
    $pdo->prepare("DELETE FROM background_music WHERE music_id=?")->execute([$id]);
  }
}

header("Location: music.php");
