<?php
require __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');
$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

// Auth check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$sellData = listListedPropertiesForMapping();
$buyData = listPurchasePropertyForMapping();

echo json_encode([
    'sellData' => $sellData,
    'buyData' => $buyData
]);
exit();
