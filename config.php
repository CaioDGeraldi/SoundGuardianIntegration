<?php
// Configuração do MySQL (root com senha vazia)
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'discogs';

try {
    // Conecta sem DB para criar se necessário
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Criar banco se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Reconnect usando o DB
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Criar tabela albums se não existir
    $pdo->exec("CREATE TABLE IF NOT EXISTS albums (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        artist VARCHAR(255),
        release_year VARCHAR(50),
        genre VARCHAR(255),
        style VARCHAR(255),
        cover_url TEXT,
        variations VARCHAR(255),
        tracklist TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8', true, 500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    exit;
}
?>