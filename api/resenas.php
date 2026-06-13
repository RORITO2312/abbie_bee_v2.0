<?php
// api/resenas.php — API REST de reseñas (JSON)
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $db->query("SELECT * FROM resenas WHERE activo=1 ORDER BY fecha DESC LIMIT 20")->fetchAll();
    echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $nombre   = trim($body['nombre'] ?? '');
    $comentario = trim($body['comentario'] ?? '');
    $estrellas  = intval($body['estrellas'] ?? 5);
    $avatar     = $body['avatar'] ?? '🐝';

    if (!$nombre || !$comentario) {
        echo json_encode(['success' => false, 'error' => 'Nombre y comentario requeridos']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO resenas (nombre, comentario, estrellas, avatar) VALUES (?,?,?,?)");
    $stmt->execute([$nombre, $comentario, $estrellas, $avatar]);
    echo json_encode(['success' => true, 'id' => $db->lastInsertId()], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'PATCH') {
    parse_str(file_get_contents('php://input'), $body);
    $id = intval($body['id'] ?? 0);
    if ($id) {
        $db->prepare("UPDATE resenas SET likes = likes + 1 WHERE id=?")->execute([$id]);
        echo json_encode(['success' => true]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Método no permitido']);
