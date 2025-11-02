<?php
require __DIR__ . '/_lib.php';

$in = read_json();
$email = trim($in['email'] ?? '');
$pass  = trim($in['pass']  ?? '');

if ($email === '' || $pass === '') {
  json(['ok' => false, 'error' => 'Заполните email и пароль'], 400);
}

$pdo = db();
$st  = $pdo->prepare('SELECT id, name, email, pass_hash FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row || !password_verify($pass, $row['pass_hash'])) {
  json(['ok' => false, 'error' => 'Неверный email или пароль'], 401);
}

$_SESSION['uid']   = (int)$row['id'];
$_SESSION['name']  = $row['name'];
$_SESSION['email'] = $row['email'];

json(['ok' => true, 'user' => ['name' => $row['name'], 'email' => $row['email']]]);