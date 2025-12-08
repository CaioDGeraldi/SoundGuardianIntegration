<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/curl_helper.php';
$token = 'dOUVpsPBcGoYsLTgwVfLCowZGqsJnmTsLePKwnkA';

$master_id = isset($_GET['master_id']) ? (int)$_GET['master_id'] : 0;
if ($master_id <= 0) { echo json_encode(['error' => 'master_id required']); exit; }

// fetch versions, handle pagination if needed (get up to 500)
$all = [];
$page = 1;
while ($page <= 5) {
    $url = "https://api.discogs.com/masters/{$master_id}/versions?" . http_build_query(['per_page'=>100,'page'=>$page,'token'=>$token]);
    $res = curl_get($url, $token);
    if (isset($res['error'])) { http_response_code(500); echo json_encode(['error'=>$res['error']]); exit; }
    if (isset($res['versions']) && is_array($res['versions'])) {
        $all = array_merge($all, $res['versions']);
        // check pagination
        if (isset($res['pagination']['pages']) && $page < $res['pagination']['pages']) { $page++; continue; }
        break;
    } else {
        break;
    }
}
echo json_encode(['versions'=>$all]);
?>