<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }

$title = $input['title'] ?? '';
$artist = $input['artist'] ?? '';
$release_year = $input['release_year'] ?? '';
$genre = $input['genre'] ?? '';
$style = $input['style'] ?? '';
$cover_url = $input['cover_url'] ?? '';
$variations = $input['variations'] ?? '';
$tracklist = isset($input['tracklist']) ? json_encode($input['tracklist'], JSON_UNESCAPED_UNICODE) : '';

if (!$title || !$artist) { http_response_code(400); echo json_encode(['error'=>'title and artist required']); exit; }

try {
    $stmt = $pdo->prepare('INSERT INTO albums (title, artist, release_year, genre, style, cover_url, variations, tracklist) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$title, $artist, $release_year, $genre, $style, $cover_url, $variations, $tracklist]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'DB error: '.$e->getMessage()]);
}
?>