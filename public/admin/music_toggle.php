<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['admin_id'])) exit;

$id = $_GET['id'] ?? null;

if ($id) {
  $pdo->prepare(
    "UPDATE background_music SET is_active = 1 - is_active WHERE music_id = ?"
  )->execute([$id]);
}

header("Location: music.php");
