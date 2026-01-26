<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) exit;

$room = (int)($_POST['room_id'] ?? 0);
$user = (int)$_SESSION['user_id'];

$pdo->prepare("
  UPDATE room_participants
  SET last_active = NOW()
  WHERE room_id = ? AND user_id = ?
")->execute([$room, $user]);
