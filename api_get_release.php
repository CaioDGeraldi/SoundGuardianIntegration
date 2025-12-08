<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/curl_helper.php';
$token = 'dOUVpsPBcGoYsLTgwVfLCowZGqsJnmTsLePKwnkA';

$release_id = isset($_GET['release_id']) ? (int)$_GET['release_id'] : 0;
if ($release_id <= 0) { echo json_encode(['error' => 'release_id required']); exit; }

$url = "https://api.discogs.com/releases/{$release_id}?token={$token}";
$res = curl_get($url, $token);
if (isset($res['error'])) { http_response_code(500); echo json_encode(['error'=>$res['error']]); exit; }
echo json_encode($res);
?>