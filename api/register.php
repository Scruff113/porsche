<?php
require __DIR__ . '/_lib.php';

$in = read_json();
$name  = trim($in['name']  ?? '');
$email = trim($in['email'] ?? '');
$pass  = trim($in['pass']  ?? '');

if ($name === '' || $email === '' || $pass === '') {
  json(['ok' => false, 'error' => 'Все поля обязательны'], 400);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$pdo  = db();

try {
  $st = $pdo->prepare('INSERT INTO users(name, email, pass_hash) VALUES(?,?,?)');
  $st->execute([$name, $email, $hash]);

  $_SESSION['uid']   = (int)$pdo->lastInsertId();
  $_SESSION['name']  = $name;
  $_SESSION['email'] = $email;

  json(['ok' => true, 'user' => ['name' => $name, 'email' => $email]]);
} catch (PDOException $e) {
  if (str_contains($e->getMessage(), 'UNIQUE')) {
    json(['ok' => false, 'error' => 'Такой e-mail уже зарегистрирован'], 409);
  }
  json(['ok' => false, 'error' => 'DB error'], 500);
}