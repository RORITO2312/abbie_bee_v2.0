<?php
// =====================================================
// admin.php — Panel de Administración Abbie BEE
// =====================================================
require_once __DIR__ . '/config.php';
requireAdmin();

$db      = getDB();
$section = $_GET['s'] ?? 'dashboard';
$admin   = $_SESSION['admin_nombre'] ?? 'Administrador';

// ── Manejo de acciones POST ──
$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // === PRODUCTOS ===
    if ($action === 'add_product') {
        $nombre     = trim($_POST['nombre'] ?? '');
        $precio     = floatval($_POST['precio'] ?? 0);
        $categoria  = $_POST['categoria'] ?? 'Accesorios';
        $material   = $_POST['material'] ?? 'Acero Inoxidable';
        $color      = trim($_POST['color'] ?? '');
        $stock      = intval($_POST['stock'] ?? 0);
        $desc       = trim($_POST['descripcion'] ?? '');
        $destacado  = isset($_POST['destacado']) ? 1 : 0;
        $imagen     = '';

        if ($nombre && $precio > 0) {
            // Subir imagen
            if (!empty($_FILES['imagen']['tmp_name'])) {
                $ext  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (in_array($ext, $allowed)) {
                    $fname = 'prod_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
                    move_uploaded_file($_FILES['imagen']['tmp_name'], UPLOAD_DIR . $fname);
                    $imagen = $fname;
                }
            }
            $stmt = $db->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria, material, color, stock, imagen, destacado) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$nombre, $desc, $precio, $categoria, $material, $color, $stock, $imagen, $destacado]);
            $msg = '¡Producto agregado con éxito!';
        } else {
            $msg = 'Completa los campos obligatorios (nombre y precio).';
            $msgType = 'error';
        }
    }

    if ($action === 'edit_product') {
        $id        = intval($_POST['id'] ?? 0);
        $nombre    = trim($_POST['nombre'] ?? '');
        $precio    = floatval($_POST['precio'] ?? 0);
        $categoria = $_POST['categoria'] ?? 'Accesorios';
        $material  = $_POST['material'] ?? 'Acero Inoxidable';
        $color     = trim($_POST['color'] ?? '');
        $stock     = intval($_POST['stock'] ?? 0);
        $desc      = trim($_POST['descripcion'] ?? '');
        $destacado = isset($_POST['destacado']) ? 1 : 0;
        $activo    = isset($_POST['activo']) ? 1 : 0;

        if ($id && $nombre) {
            $imagen_update = '';
            if (!empty($_FILES['imagen']['tmp_name'])) {
                $ext  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (in_array($ext, $allowed)) {
                    $fname = 'prod_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
                    move_uploaded_file($_FILES['imagen']['tmp_name'], UPLOAD_DIR . $fname);
                    $imagen_update = $fname;
                }
            }
            if ($imagen_update) {
                $stmt = $db->prepare("UPDATE productos SET nombre=?,descripcion=?,precio=?,categoria=?,material=?,color=?,stock=?,imagen=?,destacado=?,activo=? WHERE id=?");
                $stmt->execute([$nombre,$desc,$precio,$categoria,$material,$color,$stock,$imagen_update,$destacado,$activo,$id]);
            } else {
                $stmt = $db->prepare("UPDATE productos SET nombre=?,descripcion=?,precio=?,categoria=?,material=?,color=?,stock=?,destacado=?,activo=? WHERE id=?");
                $stmt->execute([$nombre,$desc,$precio,$categoria,$material,$color,$stock,$destacado,$activo,$id]);
            }
            $msg = '¡Producto actualizado!';
        }
    }

    if ($action === 'delete_product') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM productos WHERE id=?")->execute([$id]);
            $msg = 'Producto eliminado.';
        }
    }

    // === PEDIDOS ===
    if ($action === 'update_order_status') {
        $id     = intval($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? 'Pendiente';
        if ($id) {
            $db->prepare("UPDATE pedidos SET estado=? WHERE id=?")->execute([$estado, $id]);
            $msg = 'Estado del pedido actualizado.';
        }
    }

    // === PROMOCIONES ===
    if ($action === 'add_promo') {
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $desc   = trim($_POST['descripcion'] ?? '');
        $tipo   = $_POST['tipo'] ?? 'porcentaje';
        $valor  = floatval($_POST['valor'] ?? 0);
        $limit  = intval($_POST['limite_usos'] ?? 0) ?: null;
        $expiry = $_POST['fecha_expiracion'] ?: null;

        if ($codigo && $valor > 0) {
            $descuento = $tipo === 'porcentaje' ? $valor . '%' : 'S/' . $valor;
            try {
                $stmt = $db->prepare("INSERT INTO promociones (codigo, descuento, tipo, valor, descripcion, limite_usos, fecha_expiracion) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$codigo, $descuento, $tipo, $valor, $desc, $limit, $expiry]);
                $msg = '¡Promoción creada!';
            } catch (PDOException $e) {
                $msg = 'El código ya existe.';
                $msgType = 'error';
            }
        } else {
            $msg = 'Completa código y valor del descuento.';
            $msgType = 'error';
        }
    }

    if ($action === 'toggle_promo') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("UPDATE promociones SET estado = IF(estado='Activo','Inactivo','Activo') WHERE id=?")->execute([$id]);
            $msg = 'Estado de promoción actualizado.';
        }
    }

    if ($action === 'delete_promo') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM promociones WHERE id=?")->execute([$id]);
            $msg = 'Promoción eliminada.';
        }
    }

    header('Location: admin.php?s=' . $section . ($msg ? '&msg=' . urlencode($msg) . '&mt=' . $msgType : ''));
    exit;
}

// Leer mensaje GET
if (isset($_GET['msg'])) {
    $msg     = $_GET['msg'];
    $msgType = $_GET['mt'] ?? 'success';
}

// ── Datos por sección ──
$productos   = [];
$ventas      = [];
$pedidos     = [];
$promociones = [];
$stats       = [];

if ($section === 'dashboard') {
    $stats['total_productos']  = $db->query("SELECT COUNT(*) FROM productos WHERE activo=1")->fetchColumn();
    $stats['total_ventas']     = $db->query("SELECT COALESCE(SUM(total),0) FROM ventas")->fetchColumn();
    $stats['total_pedidos']    = $db->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $stats['pendientes']       = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado='Pendiente'")->fetchColumn();
    $stats['promo_activas']    = $db->query("SELECT COUNT(*) FROM promociones WHERE estado='Activo'")->fetchColumn();
    $ventas_recientes = $db->query("SELECT v.*, p.imagen FROM ventas v LEFT JOIN productos p ON v.producto_id=p.id ORDER BY v.fecha DESC LIMIT 5")->fetchAll();
    $pedidos_recientes= $db->query("SELECT * FROM pedidos ORDER BY fecha DESC LIMIT 5")->fetchAll();
}
if ($section === 'productos') {
    $productos = $db->query("SELECT * FROM productos ORDER BY created_at DESC")->fetchAll();
    $edit_id   = intval($_GET['edit'] ?? 0);
    $edit_prod = $edit_id ? $db->query("SELECT * FROM productos WHERE id=$edit_id")->fetch() : null;
}
if ($section === 'ventas') {
    $ventas      = $db->query("SELECT * FROM ventas ORDER BY fecha DESC")->fetchAll();
    $total_rev   = $db->query("SELECT COALESCE(SUM(total),0) FROM ventas")->fetchColumn();
    $total_items = $db->query("SELECT COALESCE(SUM(cantidad),0) FROM ventas")->fetchColumn();
}
if ($section === 'pedidos') {
    $pedidos = $db->query("SELECT * FROM pedidos ORDER BY fecha DESC")->fetchAll();
}
if ($section === 'promociones') {
    $promociones = $db->query("SELECT * FROM promociones ORDER BY created_at DESC")->fetchAll();
}

// ── Helper imagen ──
function imgTag(string $img, string $alt = '', string $cls = ''): string {
    if ($img) {
        $url = UPLOAD_URL . htmlspecialchars($img);
        return "<img src=\"$url\" alt=\"" . htmlspecialchars($alt) . "\" class=\"$cls\" style=\"object-fit:cover\">";
    }
    return '<span class="no-img">🐝</span>';
}

function estadoBadge(string $estado): string {
    $map = [
        'Pendiente'   => 'badge-warning',
        'Procesando'  => 'badge-info',
        'Enviado'     => 'badge-primary',
        'Entregado'   => 'badge-success',
        'Cancelado'   => 'badge-danger',
        'Activo'      => 'badge-success',
        'Inactivo'    => 'badge-secondary',
    ];
    $cls = $map[$estado] ?? 'badge-secondary';
    return "<span class=\"badge $cls\">$estado</span>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Abbie BEE — Admin</title>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-XS3LZP6NT2"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-XS3LZP6NT2');
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>
  <style>
    :root {
      --sidebar-bg:    #5C1535;
      --sidebar-hover: #731A44;
      --sidebar-active:#A6215F;
      --primary:       #A6215F;
      --primary-dk:    #731A44;
      --secondary:     #F277B0;
      --pink-light:    #F2DFE8;
      --bg:            #F8F0F4;
      --surface:       #FFFFFF;
      --text:          #2C1A22;
      --text-light:    #7A5A68;
      --border:        #EDD5E2;
      --sidebar-w:     240px;
      --serif:         'Playfair Display', Georgia, serif;
      --sans:          'DM Sans', sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--sans); background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
    a { text-decoration: none; color: inherit; }
    button { cursor: pointer; border: none; background: none; font-family: var(--sans); }
    img { max-width: 100%; }
    h1,h2,h3 { font-family: var(--serif); }

    /* ===== SIDEBAR ===== */
    .sidebar {
      width: var(--sidebar-w);
      background: var(--sidebar-bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      z-index: 100;
      transition: transform 0.3s;
    }
    .sidebar-top {
      padding: 1.75rem 1.5rem 1.25rem;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
    }
    .sidebar-bee {
      width: 52px; height: 52px;
      border-radius: 50%;
      background: rgba(255,255,255,0.12);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem;
      margin-bottom: 0.25rem;
    }
    .sidebar-brand {
      color: white;
      font-family: var(--serif);
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .sidebar-role {
      color: var(--secondary);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .sidebar-nav {
      padding: 1.5rem 0.75rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .nav-label {
      color: rgba(255,255,255,0.35);
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      padding: 0 0.75rem;
      margin: 0.75rem 0 0.4rem;
    }
    .nav-item {
      display: flex;
      align-items: center;
      gap: 0.875rem;
      padding: 0.75rem 1rem;
      border-radius: 0.875rem;
      color: rgba(255,255,255,0.75);
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.18s;
      position: relative;
    }
    .nav-item i { font-size: 1.1rem; flex-shrink: 0; }
    .nav-item:hover { background: var(--sidebar-hover); color: white; }
    .nav-item.active {
      background: var(--sidebar-active);
      color: white;
      font-weight: 700;
      box-shadow: 0 4px 16px rgba(166,33,95,0.4);
    }
    .nav-item.active::before {
      content: '';
      position: absolute;
      left: -0.75rem;
      top: 50%;
      transform: translateY(-50%);
      width: 4px; height: 60%;
      background: var(--secondary);
      border-radius: 0 4px 4px 0;
    }
    .nav-badge {
      margin-left: auto;
      background: var(--secondary);
      color: white;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 0.1rem 0.45rem;
      border-radius: 999px;
    }

    .sidebar-footer {
      padding: 1rem 0.75rem;
      border-top: 1px solid rgba(255,255,255,0.1);
    }
    .sidebar-user {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      border-radius: 0.875rem;
      color: rgba(255,255,255,0.75);
      font-size: 0.875rem;
    }
    .user-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: var(--sidebar-active);
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: white; flex-shrink: 0;
    }
    .logout-btn {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.625rem 1rem;
      border-radius: 0.75rem;
      color: rgba(255,100,100,0.8);
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.18s;
      margin-top: 0.25rem;
    }
    .logout-btn:hover { background: rgba(255,100,100,0.12); color: #ff8080; }

    /* ===== MAIN CONTENT ===== */
    .admin-main {
      margin-left: var(--sidebar-w);
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .topbar {
      background: white;
      border-bottom: 1px solid var(--border);
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      position: sticky; top: 0; z-index: 50;
    }
    .topbar-title { font-size: 1.4rem; font-weight: 700; color: var(--primary-dk); }
    .topbar-right { display: flex; align-items: center; gap: 1rem; }
    .topbar-date { font-size: 0.8rem; color: var(--text-light); }
    .topbar-store {
      display: inline-flex; align-items: center; gap: 0.4rem;
      background: var(--pink-light);
      color: var(--primary);
      padding: 0.5rem 1rem;
      border-radius: 0.75rem;
      font-size: 0.8rem;
      font-weight: 600;
      transition: background 0.2s;
    }
    .topbar-store:hover { background: #e8c5d8; }

    .content {
      padding: 2rem;
      flex: 1;
    }

    /* ===== ALERT ===== */
    .alert {
      padding: 1rem 1.25rem;
      border-radius: 0.875rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 500;
      animation: fadeUp 0.3s ease;
    }
    @keyframes fadeUp { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error   { background: #fee2e2; color: #991b1b; }

    /* ===== STAT CARDS ===== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.25rem;
      margin-bottom: 2rem;
    }
    .stat-card {
      background: white;
      border-radius: 1.25rem;
      padding: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      border-left: 4px solid var(--primary);
    }
    .stat-icon {
      width: 52px; height: 52px;
      border-radius: 1rem;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }
    .stat-icon.pink  { background: rgba(242,119,176,0.15); color: var(--primary); }
    .stat-icon.gold  { background: rgba(245,158,11,0.12);  color: #b45309; }
    .stat-icon.blue  { background: rgba(59,130,246,0.12);  color: #1d4ed8; }
    .stat-icon.green { background: rgba(34,197,94,0.12);   color: #15803d; }
    .stat-icon.purple{ background: rgba(139,92,246,0.12);  color: #6d28d9; }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--text); line-height: 1; }
    .stat-label { font-size: 0.8rem; color: var(--text-light); margin-top: 0.2rem; }

    /* ===== CARDS ===== */
    .card {
      background: white;
      border-radius: 1.25rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      overflow: hidden;
      margin-bottom: 1.5rem;
    }
    .card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }
    .card-header h2 { font-size: 1.1rem; color: var(--primary-dk); }
    .card-body { padding: 1.5rem; }

    /* ===== TABLES ===== */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
    thead th {
      text-align: left;
      padding: 0.875rem 1rem;
      background: var(--pink-light);
      color: var(--primary-dk);
      font-weight: 700;
      font-size: 0.8rem;
      letter-spacing: 0.03em;
      white-space: nowrap;
    }
    tbody td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: rgba(242,223,232,0.3); }

    .badge {
      display: inline-block;
      padding: 0.25rem 0.65rem;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 700;
      white-space: nowrap;
    }
    .badge-success   { background: #d1fae5; color: #065f46; }
    .badge-warning   { background: #fef3c7; color: #92400e; }
    .badge-danger    { background: #fee2e2; color: #991b1b; }
    .badge-info      { background: #dbeafe; color: #1e40af; }
    .badge-primary   { background: #f2dfe8; color: var(--primary); }
    .badge-secondary { background: #f3f4f6; color: #4b5563; }

    /* ===== FORMS ===== */
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; }
    .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .form-group label { font-size: 0.8rem; font-weight: 700; color: var(--text-light); letter-spacing: 0.03em; text-transform: uppercase; }
    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 0.75rem 1rem;
      border: 2px solid var(--border);
      border-radius: 0.75rem;
      font-size: 0.9rem;
      font-family: var(--sans);
      outline: none;
      transition: border-color 0.2s;
      background: #fafafa;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: var(--primary); background: white; }
    .form-group.full { grid-column: 1 / -1; }
    .form-check { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; font-weight: 500; }
    .form-check input[type=checkbox] { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }

    /* ===== BUTTONS ===== */
    .btn {
      display: inline-flex; align-items: center; gap: 0.4rem;
      padding: 0.625rem 1.25rem;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      font-weight: 600;
      font-family: var(--sans);
      transition: all 0.18s;
      cursor: pointer;
      border: none;
    }
    .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(166,33,95,0.25); }
    .btn-primary:hover { background: var(--primary-dk); }
    .btn-secondary { background: var(--pink-light); color: var(--primary); }
    .btn-secondary:hover { background: #e8c5d8; }
    .btn-danger  { background: #fee2e2; color: #991b1b; }
    .btn-danger:hover { background: #fecaca; }
    .btn-success { background: #d1fae5; color: #065f46; }
    .btn-success:hover { background: #a7f3d0; }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8rem; }
    .btn-icon { padding: 0.5rem; border-radius: 0.625rem; }

    /* ===== PRODUCTS GRID ===== */
    .products-table-img {
      width: 52px; height: 52px;
      border-radius: 0.625rem;
      overflow: hidden;
      border: 2px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      background: var(--pink-light);
      font-size: 1.5rem;
      flex-shrink: 0;
    }
    .products-table-img img { width: 100%; height: 100%; object-fit: cover; }
    .product-name { font-weight: 600; font-size: 0.9rem; }
    .product-meta { font-size: 0.75rem; color: var(--text-light); margin-top: 0.15rem; }
    .price-col { font-weight: 700; color: var(--primary); }
    .stock-col { font-weight: 600; }
    .stock-col.low { color: #e11d48; }

    /* ===== IMG UPLOAD ===== */
    .img-upload-area {
      border: 2px dashed var(--border);
      border-radius: 1rem;
      padding: 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      background: #fafafa;
      position: relative;
    }
    .img-upload-area:hover { border-color: var(--primary); background: var(--pink-light); }
    .img-upload-area input[type=file] {
      position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .img-upload-preview { max-height: 160px; border-radius: 0.75rem; margin-top: 0.75rem; }
    .img-upload-text { color: var(--text-light); font-size: 0.875rem; }
    .img-upload-text i { font-size: 2rem; color: var(--primary); display: block; margin-bottom: 0.5rem; }

    /* ===== SELECT STATUS ===== */
    .select-status {
      padding: 0.375rem 0.625rem;
      border: 1px solid var(--border);
      border-radius: 0.5rem;
      font-size: 0.8rem;
      font-family: var(--sans);
      background: white;
      outline: none;
      cursor: pointer;
    }

    /* ===== PROMO CARDS ===== */
    .promo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
    .promo-card {
      background: white;
      border-radius: 1.25rem;
      padding: 1.5rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      border-left: 4px solid var(--primary);
      display: flex; flex-direction: column; gap: 0.75rem;
    }
    .promo-header { display: flex; align-items: center; justify-content: space-between; }
    .promo-code-box {
      background: var(--pink-light);
      color: var(--primary);
      padding: 0.5rem 1rem;
      border-radius: 0.625rem;
      font-weight: 700;
      font-size: 1.1rem;
      letter-spacing: 0.05em;
      font-family: monospace;
    }
    .promo-val { font-size: 1.5rem; font-weight: 700; color: var(--primary-dk); }
    .promo-desc { font-size: 0.8rem; color: var(--text-light); }
    .promo-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: auto; padding-top: 0.75rem; border-top: 1px solid var(--border); }

    /* ===== DASHBOARD RECENT ===== */
    .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media(max-width:900px){ .dash-grid { grid-template-columns: 1fr; } }
    .recent-item {
      display: flex; align-items: center; gap: 0.875rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border);
    }
    .recent-item:last-child { border-bottom: none; }
    .recent-info { flex: 1; min-width: 0; }
    .recent-name { font-weight: 600; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .recent-meta { font-size: 0.75rem; color: var(--text-light); }
    .recent-amount { font-weight: 700; color: var(--primary); font-size: 0.9rem; white-space: nowrap; }

    /* ===== RESPONSIVE ===== */
    @media(max-width:768px){
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .admin-main { margin-left: 0; }
      .content { padding: 1rem; }
      .topbar { padding: 0.875rem 1rem; }
      .topbar h1 { font-size: 1rem; }
      /* Stats grid responsive */
      .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 0.75rem !important; }
      /* Product/sales table: stack rows on mobile */
      .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
      table { min-width: 540px; }
      /* Form grid full width on mobile */
      .form-grid { grid-template-columns: 1fr !important; }
      /* Card bodies */
      .card-body { padding: 1rem; }
      .card-header { padding: 0.875rem 1rem; }
      /* Hide less important table columns on mobile */
      .hide-mobile { display: none !important; }
      /* Section title smaller */
      .section-title { font-size: 1.1rem; }
    }
    @media(max-width:480px){
      .stats-grid { grid-template-columns: 1fr !important; }
    }

    .mobile-toggle {
      display: none;
      background: var(--primary);
      color: white;
      padding: 0.5rem;
      border-radius: 0.625rem;
      font-size: 1.1rem;
    }
    @media(max-width:768px){ .mobile-toggle { display: flex; } }

    .no-img { font-size: 1.5rem; }

    .section-title {
      font-size: 1.4rem;
      color: var(--primary-dk);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .divider { height: 1px; background: var(--border); margin: 1.5rem 0; }
    .text-muted { color: var(--text-light); font-size: 0.875rem; }
    .fw-bold { font-weight: 700; }
    .empty-state { text-align: center; padding: 3rem; color: var(--text-light); }
    .empty-state i { font-size: 3rem; color: var(--border); display: block; margin-bottom: 1rem; }

    .action-row { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }

    /* Edit form modal-like overlay */
    .edit-section {
      background: white;
      border-radius: 1.25rem;
      padding: 1.5rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      border-left: 4px solid var(--secondary);
      margin-bottom: 1.5rem;
      animation: fadeUp 0.3s ease;
    }
  </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-top">
    <div class="sidebar-bee">🐝</div>
    <img src="/uploads/logo.png" alt="Abbie BEE" style="height:48px;object-fit:contain;margin-bottom:0.25rem">
    <div class="sidebar-role">Panel de Control</div>
  </div>

  <nav class="sidebar-nav">
    <span class="nav-label">Menú principal</span>

    <a href="admin.php?s=dashboard" class="nav-item <?= $section==='dashboard'?'active':'' ?>">
      <i class="bi bi-grid-fill"></i> Dashboard
    </a>
    <a href="admin.php?s=productos" class="nav-item <?= $section==='productos'?'active':'' ?>">
      <i class="bi bi-box-seam-fill"></i> Productos
    </a>
    <a href="admin.php?s=ventas" class="nav-item <?= $section==='ventas'?'active':'' ?>">
      <i class="bi bi-cart-fill"></i> Ventas
    </a>
    <a href="admin.php?s=pedidos" class="nav-item <?= $section==='pedidos'?'active':'' ?>">
      <i class="bi bi-list-ul"></i> Pedidos
      <?php
        $pend = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado='Pendiente'")->fetchColumn();
        if ($pend > 0) echo "<span class=\"nav-badge\">$pend</span>";
      ?>
    </a>
    <a href="admin.php?s=promociones" class="nav-item <?= $section==='promociones'?'active':'' ?>">
      <i class="bi bi-tag-fill"></i> Promociones
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><i class="bi bi-person-fill"></i></div>
      <div>
        <div style="color:white;font-weight:600;font-size:0.85rem"><?= htmlspecialchars($admin) ?></div>
        <div style="color:rgba(255,255,255,0.5);font-size:0.72rem"><?= htmlspecialchars($_SESSION['admin_user'] ?? '') ?></div>
      </div>
    </div>
    <a href="logout.php" class="logout-btn">
      <i class="bi bi-box-arrow-left"></i> Cerrar sesión
    </a>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<div class="admin-main">
  <!-- Topbar -->
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:1rem">
      <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
        <i class="bi bi-list"></i>
      </button>
      <span class="topbar-title">
        <?php
          $titles = ['dashboard'=>'Dashboard','productos'=>'Gestión de Productos','ventas'=>'Ventas','pedidos'=>'Pedidos','promociones'=>'Promociones'];
          echo $titles[$section] ?? 'Admin';
        ?>
      </span>
    </div>
    <div class="topbar-right">
      <span class="topbar-date"><?= date('d M Y, H:i') ?></span>
      <a href="index.php" target="_blank" class="topbar-store">
        <i class="bi bi-shop"></i> Ver Tienda
      </a>
    </div>
  </div>

  <!-- Content -->
  <div class="content">

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>">
        <i class="bi bi-<?= $msgType === 'error' ? 'x-circle-fill' : 'check-circle-fill' ?>"></i>
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <?php /* ===================== DASHBOARD ===================== */ ?>
    <?php if ($section === 'dashboard'): ?>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon pink"><i class="bi bi-box-seam-fill"></i></div>
          <div><div class="stat-value"><?= $stats['total_productos'] ?></div><div class="stat-label">Productos activos</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#16a34a">
          <div class="stat-icon green"><i class="bi bi-cash-coin"></i></div>
          <div><div class="stat-value">S/<?= number_format($stats['total_ventas'],2) ?></div><div class="stat-label">Ingresos totales</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#2563eb">
          <div class="stat-icon blue"><i class="bi bi-bag-check-fill"></i></div>
          <div><div class="stat-value"><?= $stats['total_pedidos'] ?></div><div class="stat-label">Pedidos totales</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#f59e0b">
          <div class="stat-icon gold"><i class="bi bi-clock-fill"></i></div>
          <div><div class="stat-value"><?= $stats['pendientes'] ?></div><div class="stat-label">Pedidos pendientes</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#7c3aed">
          <div class="stat-icon purple"><i class="bi bi-tag-fill"></i></div>
          <div><div class="stat-value"><?= $stats['promo_activas'] ?></div><div class="stat-label">Promociones activas</div></div>
        </div>
      </div>

      <div class="dash-grid">
        <div class="card">
          <div class="card-header"><h2><i class="bi bi-cart-fill" style="color:var(--primary)"></i> Ventas recientes</h2></div>
          <div class="card-body">
            <?php if (empty($ventas_recientes)): ?>
              <div class="empty-state"><i class="bi bi-cart-x"></i>Sin ventas aún</div>
            <?php else: foreach ($ventas_recientes as $v): ?>
              <div class="recent-item">
                <div class="products-table-img"><?= imgTag($v['imagen'] ?? '', $v['producto_nombre']) ?></div>
                <div class="recent-info">
                  <div class="recent-name"><?= htmlspecialchars($v['producto_nombre']) ?></div>
                  <div class="recent-meta"><?= htmlspecialchars($v['cliente_nombre']) ?> · <?= date('d/m/Y', strtotime($v['fecha'])) ?></div>
                </div>
                <div class="recent-amount">S/<?= number_format($v['total'],2) ?></div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2><i class="bi bi-list-ul" style="color:var(--primary)"></i> Pedidos recientes</h2></div>
          <div class="card-body">
            <?php if (empty($pedidos_recientes)): ?>
              <div class="empty-state"><i class="bi bi-inbox"></i>Sin pedidos</div>
            <?php else: foreach ($pedidos_recientes as $p): ?>
              <div class="recent-item">
                <div class="recent-info">
                  <div class="recent-name"><?= htmlspecialchars($p['codigo']) ?> · <?= htmlspecialchars($p['cliente_nombre']) ?></div>
                  <div class="recent-meta"><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></div>
                </div>
                <?= estadoBadge($p['estado']) ?>
                <div class="recent-amount">S/<?= number_format($p['total'],2) ?></div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

    <?php /* ===================== PRODUCTOS ===================== */ ?>
    <?php elseif ($section === 'productos'): ?>

      <?php if (isset($edit_prod) && $edit_prod): ?>
        <!-- Formulario Editar -->
        <div class="edit-section">
          <h2 class="section-title"><i class="bi bi-pencil-fill"></i> Editar producto</h2>
          <form method="POST" action="admin.php?s=productos" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_product">
            <input type="hidden" name="id" value="<?= $edit_prod['id'] ?>">
            <div class="form-grid">
              <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($edit_prod['nombre']) ?>" required>
              </div>
              <div class="form-group">
                <label>Precio (S/) *</label>
                <input type="number" name="precio" step="0.01" value="<?= $edit_prod['precio'] ?>" required>
              </div>
              <div class="form-group">
                <label>Categoría</label>
                <select name="categoria">
                  <?php foreach(['Anillos','Collares','Pulseras','Aretes','Accesorios'] as $cat): ?>
                    <option <?= $edit_prod['categoria']===$cat?'selected':'' ?>><?= $cat ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Material</label>
                <select name="material">
                  <?php foreach(['Acero Inoxidable','Enchapado Oro Rosa 18K','Enchapado Rodio','Plata'] as $mat): ?>
                    <option <?= $edit_prod['material']===$mat?'selected':'' ?>><?= $mat ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Color</label>
                <input type="text" name="color" value="<?= htmlspecialchars($edit_prod['color']) ?>">
              </div>
              <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" value="<?= $edit_prod['stock'] ?>">
              </div>
              <div class="form-group full">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3"><?= htmlspecialchars($edit_prod['descripcion']) ?></textarea>
              </div>
              <div class="form-group">
                <label>Nueva imagen (opcional)</label>
                <div class="img-upload-area">
                  <input type="file" name="imagen" accept="image/*" onchange="previewImg(this,'edit_prev')">
                  <i class="bi bi-cloud-arrow-up"></i>
                  <div class="img-upload-text">Haz clic para subir imagen</div>
                  <?php if ($edit_prod['imagen']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($edit_prod['imagen']) ?>" class="img-upload-preview" id="edit_prev">
                  <?php else: ?>
                    <img class="img-upload-preview" id="edit_prev" style="display:none">
                  <?php endif; ?>
                </div>
              </div>
              <div class="form-group" style="justify-content:flex-end;gap:1rem">
                <label class="form-check"><input type="checkbox" name="destacado" <?= $edit_prod['destacado']?'checked':'' ?>> Destacado</label>
                <label class="form-check"><input type="checkbox" name="activo" <?= $edit_prod['activo']?'checked':'' ?>> Activo</label>
              </div>
            </div>
            <div class="divider"></div>
            <div class="action-row">
              <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Guardar cambios</button>
              <a href="admin.php?s=productos" class="btn btn-secondary"><i class="bi bi-x"></i> Cancelar</a>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <!-- Formulario Agregar -->
      <div class="card">
        <div class="card-header">
          <h2><i class="bi bi-plus-circle-fill" style="color:var(--primary)"></i> Agregar producto</h2>
        </div>
        <div class="card-body">
          <form method="POST" action="admin.php?s=productos" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_product">
            <div class="form-grid">
              <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" placeholder="Ej: Anillo Estrella Rosa" required>
              </div>
              <div class="form-group">
                <label>Precio (S/) *</label>
                <input type="number" name="precio" step="0.01" min="0" placeholder="0.00" required>
              </div>
              <div class="form-group">
                <label>Categoría</label>
                <select name="categoria">
                  <option>Anillos</option><option>Collares</option><option>Pulseras</option><option>Aretes</option><option>Accesorios</option>
                </select>
              </div>
              <div class="form-group">
                <label>Material</label>
                <select name="material">
                  <option>Acero Inoxidable</option><option>Enchapado Oro Rosa 18K</option><option>Enchapado Rodio</option><option>Plata</option>
                </select>
              </div>
              <div class="form-group">
                <label>Color</label>
                <input type="text" name="color" placeholder="Ej: Dorado Rosa">
              </div>
              <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" min="0" value="0">
              </div>
              <div class="form-group full">
                <label>Descripción</label>
                <textarea name="descripcion" rows="2" placeholder="Descripción del producto..."></textarea>
              </div>
              <div class="form-group">
                <label>Imagen</label>
                <div class="img-upload-area">
                  <input type="file" name="imagen" accept="image/*" onchange="previewImg(this,'add_prev')">
                  <i class="bi bi-image" style="font-size:2rem;color:var(--primary);display:block;margin-bottom:0.5rem"></i>
                  <div class="img-upload-text">Haz clic para subir imagen<br><small>JPG, PNG, WEBP · máx. 5MB</small></div>
                  <img class="img-upload-preview" id="add_prev" style="display:none">
                </div>
              </div>
              <div class="form-group" style="justify-content:flex-end;gap:1rem">
                <label class="form-check"><input type="checkbox" name="destacado"> Producto destacado</label>
              </div>
            </div>
            <div class="divider"></div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Agregar producto</button>
          </form>
        </div>
      </div>

      <!-- Lista de productos -->
      <div class="card">
        <div class="card-header">
          <h2><i class="bi bi-box-seam-fill" style="color:var(--primary)"></i> Inventario (<?= count($productos) ?>)</h2>
          <input type="text" id="prodSearch" placeholder="Buscar producto..." oninput="filterProds(this.value)"
                 style="padding:0.5rem 1rem;border:2px solid var(--border);border-radius:0.75rem;font-size:0.875rem;outline:none;font-family:var(--sans)">
        </div>
        <div class="table-wrap">
          <?php if (empty($productos)): ?>
            <div class="empty-state"><i class="bi bi-box"></i>No hay productos aún. ¡Agrega el primero!</div>
          <?php else: ?>
          <table id="prodTable">
            <thead>
              <tr>
                <th>Imagen</th><th>Producto</th><th class="hide-mobile">Categoría</th><th class="hide-mobile">Material</th>
                <th>Stock</th><th>Precio</th><th class="hide-mobile">Estado</th><th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p): ?>
              <tr>
                <td>
                  <div class="products-table-img">
                    <?= imgTag($p['imagen'], $p['nombre']) ?>
                  </div>
                </td>
                <td>
                  <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
                  <div class="product-meta"><?= htmlspecialchars(substr($p['descripcion'],0,60)) ?>...</div>
                  <?php if ($p['destacado']): ?><span class="badge badge-primary" style="margin-top:0.25rem"><i class="bi bi-star-fill"></i> Destacado</span><?php endif; ?>
                </td>
                <td class="hide-mobile"><?= htmlspecialchars($p['categoria']) ?></td>
                <td class="hide-mobile"><span class="text-muted"><?= htmlspecialchars($p['material']) ?></span></td>
                <td>
                  <span class="stock-col <?= $p['stock'] < 5 ? 'low' : '' ?>">
                    <?= $p['stock'] ?> <?= $p['stock'] < 5 ? '⚠️' : '' ?>
                  </span>
                </td>
                <td class="price-col">S/<?= number_format($p['precio'],2) ?></td>
                <td class="hide-mobile"><?= estadoBadge($p['activo'] ? 'Activo' : 'Inactivo') ?></td>
                <td>
                  <div class="action-row">
                    <a href="admin.php?s=productos&edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-icon" title="Editar">
                      <i class="bi bi-pencil-fill"></i>
                    </a>
                    <form method="POST" action="admin.php?s=productos" onsubmit="return confirm('¿Eliminar este producto?')">
                      <input type="hidden" name="action" value="delete_product">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar">
                        <i class="bi bi-trash3-fill"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

    <?php /* ===================== VENTAS ===================== */ ?>
    <?php elseif ($section === 'ventas'): ?>
      <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stat-card">
          <div class="stat-icon green"><i class="bi bi-cash-coin"></i></div>
          <div><div class="stat-value">S/<?= number_format($total_rev,2) ?></div><div class="stat-label">Ingresos totales</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#2563eb">
          <div class="stat-icon blue"><i class="bi bi-bag-fill"></i></div>
          <div><div class="stat-value"><?= count($ventas) ?></div><div class="stat-label">Transacciones</div></div>
        </div>
        <div class="stat-card" style="border-left-color:#7c3aed">
          <div class="stat-icon purple"><i class="bi bi-boxes"></i></div>
          <div><div class="stat-value"><?= $total_items ?></div><div class="stat-label">Unidades vendidas</div></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2><i class="bi bi-cart-fill" style="color:var(--primary)"></i> Registro de ventas</h2>
          <button onclick="exportCSV()" class="btn btn-secondary btn-sm"><i class="bi bi-download"></i> Exportar CSV</button>
        </div>
        <div class="table-wrap">
          <?php if (empty($ventas)): ?>
            <div class="empty-state"><i class="bi bi-cart-x"></i>No hay ventas registradas.</div>
          <?php else: ?>
          <table id="salesTable">
            <thead><tr><th>#</th><th>Producto</th><th>Cliente</th><th>Cantidad</th><th>Precio unit.</th><th>Total</th><th>Fecha</th></tr></thead>
            <tbody>
              <?php foreach ($ventas as $i => $v): ?>
              <tr>
                <td class="text-muted"><?= $i+1 ?></td>
                <td class="fw-bold"><?= htmlspecialchars($v['producto_nombre']) ?></td>
                <td><?= htmlspecialchars($v['cliente_nombre']) ?></td>
                <td><?= $v['cantidad'] ?></td>
                <td>S/<?= number_format($v['precio_unitario'],2) ?></td>
                <td class="price-col">S/<?= number_format($v['total'],2) ?></td>
                <td class="text-muted"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

    <?php /* ===================== PEDIDOS ===================== */ ?>
    <?php elseif ($section === 'pedidos'): ?>
      <div class="card">
        <div class="card-header">
          <h2><i class="bi bi-list-ul" style="color:var(--primary)"></i> Gestión de pedidos (<?= count($pedidos) ?>)</h2>
          <div class="action-row">
            <a href="admin.php?s=pedidos" class="btn btn-secondary btn-sm">Todos</a>
            <a href="admin.php?s=pedidos&filtro=Pendiente" class="btn btn-sm" style="background:#fef3c7;color:#92400e">Pendientes</a>
            <a href="admin.php?s=pedidos&filtro=Enviado" class="btn btn-sm" style="background:#f2dfe8;color:var(--primary)">Enviados</a>
          </div>
        </div>
        <div class="table-wrap">
          <?php if (empty($pedidos)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i>No hay pedidos aún.</div>
          <?php else: ?>
          <table>
            <thead><tr><th>Código</th><th>Cliente</th><th>Email</th><th>Total</th><th>Método</th><th>Fecha</th><th>Estado</th><th>Cambiar estado</th></tr></thead>
            <tbody>
              <?php foreach ($pedidos as $p): ?>
              <tr>
                <td class="fw-bold"><?= htmlspecialchars($p['codigo']) ?></td>
                <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($p['cliente_email']) ?></td>
                <td class="price-col">S/<?= number_format($p['total'],2) ?></td>
                <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                <td class="text-muted"><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                <td><?= estadoBadge($p['estado']) ?></td>
                <td>
                  <form method="POST" action="admin.php?s=pedidos">
                    <input type="hidden" name="action" value="update_order_status">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <select name="estado" class="select-status" onchange="this.form.submit()">
                      <?php foreach(['Pendiente','Procesando','Enviado','Entregado','Cancelado'] as $est): ?>
                        <option <?= $p['estado']===$est?'selected':'' ?>><?= $est ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

    <?php /* ===================== PROMOCIONES ===================== */ ?>
    <?php elseif ($section === 'promociones'): ?>

      <!-- Formulario nueva promo -->
      <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><h2><i class="bi bi-plus-circle-fill" style="color:var(--primary)"></i> Nueva promoción</h2></div>
        <div class="card-body">
          <form method="POST" action="admin.php?s=promociones">
            <input type="hidden" name="action" value="add_promo">
            <div class="form-grid">
              <div class="form-group">
                <label>Código *</label>
                <input type="text" name="codigo" placeholder="Ej: ABBIE20" required style="text-transform:uppercase">
              </div>
              <div class="form-group">
                <label>Tipo</label>
                <select name="tipo">
                  <option value="porcentaje">Porcentaje (%)</option>
                  <option value="monto">Monto fijo (S/)</option>
                </select>
              </div>
              <div class="form-group">
                <label>Valor *</label>
                <input type="number" name="valor" step="0.01" min="0" placeholder="Ej: 20" required>
              </div>
              <div class="form-group">
                <label>Límite de usos</label>
                <input type="number" name="limite_usos" min="0" placeholder="Dejar vacío = ilimitado">
              </div>
              <div class="form-group">
                <label>Fecha expiración</label>
                <input type="date" name="fecha_expiracion">
              </div>
              <div class="form-group full">
                <label>Descripción</label>
                <input type="text" name="descripcion" placeholder="Ej: Descuento San Valentín">
              </div>
            </div>
            <div class="divider"></div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-tag-fill"></i> Crear promoción</button>
          </form>
        </div>
      </div>

      <!-- Lista promociones -->
      <div class="promo-grid">
        <?php if (empty($promociones)): ?>
          <div class="empty-state" style="grid-column:1/-1"><i class="bi bi-tag"></i>No hay promociones aún.</div>
        <?php else: foreach ($promociones as $p): ?>
        <div class="promo-card">
          <div class="promo-header">
            <div class="promo-code-box"><?= htmlspecialchars($p['codigo']) ?></div>
            <?= estadoBadge($p['estado']) ?>
          </div>
          <div class="promo-val"><?= htmlspecialchars($p['descuento']) ?></div>
          <?php if ($p['descripcion']): ?>
            <div class="promo-desc"><?= htmlspecialchars($p['descripcion']) ?></div>
          <?php endif; ?>
          <div class="promo-desc">
            Usos: <?= $p['usos'] ?><?= $p['limite_usos'] ? '/' . $p['limite_usos'] : '' ?>
            <?php if ($p['fecha_expiracion']): ?> · Expira: <?= date('d/m/Y', strtotime($p['fecha_expiracion'])) ?><?php endif; ?>
          </div>
          <div class="promo-actions">
            <form method="POST" action="admin.php?s=promociones">
              <input type="hidden" name="action" value="toggle_promo">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-sm <?= $p['estado']==='Activo' ? 'btn-danger' : 'btn-success' ?>">
                <i class="bi bi-<?= $p['estado']==='Activo' ? 'x-circle' : 'check-circle' ?>"></i>
                <?= $p['estado']==='Activo' ? 'Desactivar' : 'Activar' ?>
              </button>
            </form>
            <form method="POST" action="admin.php?s=promociones" onsubmit="return confirm('¿Eliminar promoción?')">
              <input type="hidden" name="action" value="delete_promo">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash3-fill"></i> Eliminar</button>
            </form>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

    <?php endif; ?>
  </div><!-- /content -->
</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
// Preview imagen upload
function previewImg(input, previewId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Filtrar tabla de productos
function filterProds(q) {
  const rows = document.querySelectorAll('#prodTable tbody tr');
  q = q.toLowerCase();
  rows.forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// Exportar CSV de ventas
function exportCSV() {
  const table = document.getElementById('salesTable');
  if (!table) return;
  let csv = [];
  for (const row of table.rows) {
    const cols = Array.from(row.cells).map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"');
    csv.push(cols.join(','));
  }
  const blob = new Blob([csv.join('\n')], {type:'text/csv'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'ventas_abbie_bee.csv';
  a.click();
}

// Cerrar sidebar al hacer clic fuera (mobile)
document.addEventListener('click', e => {
  const sb = document.getElementById('sidebar');
  if (sb.classList.contains('open') && !sb.contains(e.target) && !e.target.closest('.mobile-toggle')) {
    sb.classList.remove('open');
  }
});
</script>
</body>
</html>