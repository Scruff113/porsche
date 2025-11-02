<?php
require __DIR__ . '/_lib.php';

$in    = read_json();
$model = trim($in['model'] ?? '');
$qty   = (int)($in['qty'] ?? 1);
$phone = trim($in['phone'] ?? '');

if ($model === '' || $phone === '' || $qty < 1) {
  json(['ok' => false, 'error' => 'Проверьте поля модели/телефона/кол-ва'], 400);
}

$u   = current_user();
$uid = $u['id'] ?? null;

$pdo = db();
$st  = $pdo->prepare('INSERT INTO orders(user_id, model, qty, phone) VALUES(?,?,?,?)');
$st->execute([$uid, $model, $qty, $phone]);

json(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);