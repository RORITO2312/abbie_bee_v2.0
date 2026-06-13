<?php
// api/productos.php — API REST de productos (JSON)
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            $cat      = $_GET['categoria'] ?? '';
            $material = $_GET['material'] ?? '';
            $search   = $_GET['q'] ?? '';
            $destacado= $_GET['destacado'] ?? '';

            $where = ['activo = 1'];
            $params = [];
            if ($cat)       { $where[] = 'categoria = ?'; $params[] = $cat; }
            if ($material)  { $where[] = 'material = ?';  $params[] = $material; }
            if ($destacado) { $where[] = 'destacado = 1'; }
            if ($search)    { $where[] = '(nombre LIKE ? OR descripcion LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

            $sql  = "SELECT * FROM productos WHERE " . implode(' AND ', $where) . " ORDER BY destacado DESC, created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            // Añadir URL completa de imagen
            foreach ($rows as &$r) {
                $r['imagen_url'] = $r['imagen'] ? UPLOAD_URL . $r['imagen'] : '';
            }
            echo json_encode(['success' => true, 'data' => $rows, 'total' => count($rows)], JSON_UNESCAPED_UNICODE);
        }
        break;
}
