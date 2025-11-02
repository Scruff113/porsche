<?php
require __DIR__ . '/../db/connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$pass = trim($data['password'] ?? '');

if (!$name || !$email || !$pass) {
  echo json_encode(['ok'=>false, 'error'=>'Заполните все поля']);
  exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$stmt->execute([$name, $email, $hash]);

echo json_encode(['ok'=>true]);