<?php
require __DIR__ . '/_lib.php';
session_destroy();
json(['ok' => true]);