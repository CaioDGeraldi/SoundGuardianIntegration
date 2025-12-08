<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/curl_helper.php';

// Discogs token
$token = 'dOUVpsPBcGoYsLTgwVfLCowZGqsJnmTsLePKwnkA';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') { echo json_encode([]); exit; }

$url = 'https://api.discogs.com/database/search?' . http_build_query([
    'q' => $q,
    'type' => 'master',
    'per_page' => 10,
    'page' => 1,
    'token' => $token
]);

$res = curl_get($url, $token);
if (isset($res['error'])) {
    http_response_code(500);
    echo json_encode(['error' => $res['error']]);
    exit;
}
echo json_encode($res);
?>