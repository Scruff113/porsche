<?php
require __DIR__ . '/_lib.php';
$pdo = db();
$rows = $pdo->query('
  SELECT o.id, o.model, o.qty, o.phone, o.created_at,
         u.name as user_name, u.email as user_email
  FROM orders o
  LEFT JOIN users u ON u.id = o.user_id
  ORDER BY o.id DESC
')->fetchAll(PDO::FETCH_ASSOC);
json(['ok' => true, 'items' => $rows]);