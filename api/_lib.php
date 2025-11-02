<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../data/app.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
  }
  return $pdo;
}

function json($data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function read_json(): array {
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function current_user(): ?array {
  if (!empty($_SESSION['uid'])) {
    return [
      'id' => (int)$_SESSION['uid'],
      'name' => $_SESSION['name'] ?? '',
      'email' => $_SESSION['email'] ?? ''
    ];
  }
  return null;
}