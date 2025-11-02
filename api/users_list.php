<?php
// /api/users_list.php
declare(strict_types=1);

require __DIR__ . '/_lib.php';         // та же библиотека, что и в orders_list.php
header('Content-Type: application/json; charset=utf-8');

try {
  $pdo = db();
  // В таблице users у нас поля: id, name, email, pass_hash, created_at
  $rows = $pdo->query('
    SELECT id, name, email, created_at
    FROM users
    ORDER BY id DESC
  ')->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok' => true, 'items' => $rows], JSON_UNESCAPED_UNICODE);
  exit;
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}