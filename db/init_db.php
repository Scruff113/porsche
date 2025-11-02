<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$dbFile = __DIR__ . '/../data/app.db';
$needCreate = !file_exists($dbFile);

$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($needCreate) {
  $db->exec('PRAGMA foreign_keys = ON');

  $db->exec('CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    pass_hash TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  )');

  $db->exec('CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NULL,
    model TEXT NOT NULL,
    qty INTEGER NOT NULL DEFAULT 1,
    phone TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
  )');
}

echo "OK: DB ready\n";