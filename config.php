<?php
// =====================================================
// config.php — Configuración de base de datos
// Abbie BEE — XAMPP/MySQL
// =====================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Usuario MySQL de XAMPP
define('DB_PASS', '');           // Contraseña (vacía por defecto en XAMPP)
define('DB_NAME', 'abbie_bee');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'http://localhost/abbie_bee');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ── Conexión PDO ──
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ── Respuesta JSON ──
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Verificar sesión admin ──
function requireAdmin(): void {
    session_start();
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

// ── Verificar sesión API ──
function requireAdminAPI(): void {
    session_start();
    if (empty($_SESSION['admin_id'])) {
        jsonResponse(['error' => 'No autorizado'], 401);
    }
}
