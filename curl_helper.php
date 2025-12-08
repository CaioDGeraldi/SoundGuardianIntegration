<?php
function curl_get($url, $token) {
    $ch = curl_init($url);
    $headers = [
        'User-Agent: DiscogsApp/1.0',
        'Accept: application/json'
    ];
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($res === false || $code >= 400) {
        return ['error' => 'HTTP error ' . $code . ($err ? ' - ' . $err : '')];
    }
    $decoded = json_decode($res, true);
    if ($decoded === null) return ['error' => 'Invalid JSON from remote'];
    return $decoded;
}
?>