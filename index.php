<?php
// =====================================================
// index.php — Tienda Abbie BEE
// =====================================================
require_once __DIR__ . '/config.php';
$db = getDB();

// Cargar productos activos
$productos = $db->query("SELECT * FROM productos WHERE activo=1 ORDER BY destacado DESC, created_at DESC")->fetchAll();
$destacados = array_filter($productos, fn($p) => $p['destacado']);

// Cargar reseñas
$resenas = $db->query("SELECT * FROM resenas WHERE activo=1 ORDER BY fecha DESC LIMIT 6")->fetchAll();

function pImgUrl(array $p): string {
    return $p['imagen'] ? UPLOAD_URL . htmlspecialchars($p['imagen']) : '';
}
function pImgOrSvg(array $p, string $cls = ''): string {
    $url = pImgUrl($p);
    if ($url) return "<img src=\"$url\" alt=\"" . htmlspecialchars($p['nombre']) . "\" class=\"$cls\" style=\"width:100%;height:100%;object-fit:cover\">";
    return '<div class="img-placeholder"><i class="bi bi-gem" style="font-size:2rem;color:#ccc"></i></div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Abbie BEE — Joyería exclusiva</title>
  <meta name="description" content="Abbie BEE – Joyas de edición limitada, resistentes al agua e hipoalergénicas. Diseños exclusivos para mujeres modernas."/>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&family=Kavoon&display=swap" rel="stylesheet"/>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-XS3LZP6NT2"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-XS3LZP6NT2');
  </script>
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>

  <style>
    :root {
      --primary:       #A6215F;
      --primary-dk:    #731A44;
      --primary-light: #C04070;
      --secondary:     #F277B0;
      --pink-soft:     #F2A7CA;
      --pink-pale:     #F2DFE8;
      --bg:            #FBF4F7;
      --surface:       #FFFFFF;
      --text:          #2C1A22;
      --text-light:    #7A5A68;
      --border:        #EDD5E2;
      --serif:         'Playfair Display', Georgia, serif;
      --sans:          'DM Sans', sans-serif;
      --kavoon:        'Kavoon', cursive;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--sans); background: var(--bg); color: var(--text); min-height: 100vh; }
    h1,h2,h3,h4 { font-family: var(--serif); }
    img { max-width: 100%; }
    a { text-decoration: none; color: inherit; }
    button { cursor: pointer; border: none; background: none; font-family: var(--sans); }
    input, textarea, select { font-family: var(--sans); }

    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: var(--pink-soft); border-radius: 3px; }

    /* ===== HEADER ===== */
    .sf-header {
      background: var(--primary-dk);
      color: white;
      padding: 0 2rem;
      height: 72px;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 100;
      gap: 1rem;
      box-shadow: 0 4px 24px rgba(115,26,68,0.25);
    }
    .sf-nav { display: flex; align-items: center; gap: 1.75rem; font-size: 0.875rem; font-weight: 500; flex-shrink: 0; }
    .sf-nav a { color: rgba(255,255,255,0.85); transition: color 0.2s; letter-spacing: 0.02em; }
    .sf-nav a:hover, .sf-nav a.active { color: var(--secondary); }
    .sf-logo-wrap { flex: 1; display: flex; justify-content: center; }
    .sf-logo-text {
      font-family: var(--kavoon);
      color: #FDF0F5;
      font-size: 1.9rem;
      letter-spacing: 0.04em;
      display: flex; align-items: center; gap: 0.3rem;
    }
    .sf-logo-text .bee { font-size: 1.3rem; }
    .sf-actions { display: flex; align-items: center; gap: 1.25rem; flex-shrink: 0; }
    .search-wrap { position: relative; }
    .search-wrap input {
      background: rgba(255,255,255,0.15);
      color: white;
      border: 1px solid rgba(255,255,255,0.25);
      border-radius: 999px;
      padding: 0.4rem 2.5rem 0.4rem 1rem;
      font-size: 0.85rem;
      width: 200px;
      outline: none;
      transition: background 0.2s, width 0.3s;
    }
    .search-wrap input::placeholder { color: rgba(255,255,255,0.55); }
    .search-wrap input:focus { background: rgba(255,255,255,0.22); width: 240px; }
    .search-wrap button { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.7); font-size: 0.95rem; }
    .icon-btn { color: rgba(255,255,255,0.9); display: flex; align-items: center; transition: color 0.2s; font-size: 1.3rem; position: relative; }
    .icon-btn:hover { color: var(--secondary); }
    .cart-badge {
      position: absolute; top: -6px; right: -6px;
      background: var(--secondary); color: white;
      font-size: 10px; font-weight: 700;
      width: 18px; height: 18px; border-radius: 50%;
      display: none; align-items: center; justify-content: center;
    }
    .cart-badge.visible { display: flex; }
    .admin-btn {
      background: rgba(255,255,255,0.12);
      color: rgba(255,255,255,0.9);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 0.625rem;
      padding: 0.375rem 0.875rem;
      font-size: 0.8rem;
      font-weight: 600;
      display: flex; align-items: center; gap: 0.4rem;
      transition: background 0.2s;
    }
    .admin-btn:hover { background: rgba(255,255,255,0.2); }
    @media(max-width:768px) { .sf-nav { display:none; } .search-wrap { display:none; } }

    /* ===== HERO ===== */
    .hero {
      position: relative;
      min-height: 560px;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
      background: var(--primary-dk);
      background-image: url('/uploads/hero-bg.jpg');
      background-size: cover;
      background-position: center;
    }
    .hero-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(115,26,68,0.92) 0%, rgba(166,33,95,0.78) 60%, rgba(242,119,176,0.4) 100%);
    }
    /* Decorative honeycomb pattern */
    .hero-pattern {
      position: absolute; inset: 0; opacity: 0.06;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='100'%3E%3Cpath d='M28 66L0 50V16l28-16 28 16v34L28 66zM0 50l28 16 28-16' fill='none' stroke='white' stroke-width='1'/%3E%3C/svg%3E");
    }
    .hero-content {
      position: relative; z-index: 2;
      text-align: center; color: white;
      padding: 5rem 2rem; max-width: 860px; margin: 0 auto;
    }
    .hero-eyebrow {
      display: inline-flex; align-items: center; gap: 0.5rem;
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.2);
      color: var(--pink-soft);
      font-size: 0.8rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
      padding: 0.375rem 1rem; border-radius: 999px;
      margin-bottom: 1.5rem;
    }
    .hero-title {
      font-family: var(--kavoon);
      font-size: clamp(2.4rem, 6vw, 4.5rem);
      font-weight: 400;
      line-height: 1.1;
      margin-bottom: 1.25rem;
      text-shadow: 0 2px 20px rgba(0,0,0,0.3);
      color: #FEF0F5;
    }
    .hero-title span { color: var(--secondary); }
    .hero-subtitle {
      font-size: clamp(0.95rem, 2vw, 1.15rem);
      color: rgba(255,255,255,0.82);
      line-height: 1.75;
      max-width: 540px; margin: 0 auto 2.5rem;
    }
    .hero-actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .btn-hero {
      display: inline-flex; align-items: center; gap: 0.6rem;
      font-weight: 700; font-size: 0.95rem;
      padding: 0.875rem 2rem; border-radius: 999px;
      transition: all 0.2s; cursor: pointer; border: none;
    }
    .btn-hero-primary { background: var(--secondary); color: white; box-shadow: 0 6px 24px rgba(242,119,176,0.4); }
    .btn-hero-primary:hover { background: #e05a9a; transform: translateY(-2px); }
    .btn-hero-outline { background: transparent; color: white; border: 2px solid rgba(255,255,255,0.45); }
    .btn-hero-outline:hover { background: rgba(255,255,255,0.12); }
    .hero-badges { display: flex; justify-content: center; gap: 2rem; margin-top: 2.5rem; flex-wrap: wrap; }
    .hero-badge { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: rgba(255,255,255,0.7); }
    .hero-badge i { color: var(--secondary); font-size: 1rem; }
    @media(max-width:640px){
      .hero-content { padding: 3.5rem 1.25rem; }
      .hero-badges { gap: 1.25rem; }
      .hero-actions { flex-direction: column; align-items: center; }
      .btn-hero { width: 100%; max-width: 280px; justify-content: center; }
    }

    /* ===== SECTION UTILS ===== */
    .section { padding: 5rem 2rem; }
    @media(max-width:640px){ .section { padding: 3rem 1.25rem; } }
    .section-inner { max-width: 1200px; margin: 0 auto; }
    .section-header { text-align: center; margin-bottom: 3rem; }
    .section-label {
      display: inline-block;
      color: var(--primary);
      font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
      background: var(--pink-pale);
      padding: 0.25rem 1rem; border-radius: 999px;
      margin-bottom: 0.75rem;
    }
    .section-title { font-size: clamp(1.8rem, 4vw, 2.8rem); color: var(--primary-dk); line-height: 1.2; }
    .section-sub { color: var(--text-light); font-size: 1rem; max-width: 520px; margin: 0.75rem auto 0; line-height: 1.7; }

    /* ===== CATEGORIES ===== */
    .categories-grid { display: flex; justify-content: center; gap: 2.5rem; flex-wrap: wrap; }
    .category-item { display: flex; flex-direction: column; align-items: center; gap: 0.875rem; cursor: pointer; }
    .category-circle {
      width: 110px; height: 110px; border-radius: 50%;
      background: white;
      border: 3px solid var(--border);
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.5rem;
      transition: all 0.3s;
    }
    .category-item:hover .category-circle {
      border-color: var(--secondary);
      box-shadow: 0 8px 32px rgba(242,119,176,0.25);
      transform: translateY(-4px);
    }
    .category-item span { font-family: var(--serif); font-weight: 700; font-size: 0.95rem; color: var(--primary-dk); }

    /* ===== PRODUCT CARDS ===== */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.75rem; }
    @media(max-width:640px){ .products-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }
    @media(max-width:360px){ .products-grid { grid-template-columns: 1fr; } }
    .product-card {
      background: white;
      border-radius: 1.25rem;
      overflow: hidden;
      box-shadow: 0 2px 16px rgba(0,0,0,0.06);
      transition: all 0.3s;
      cursor: pointer;
      position: relative;
    }
    .product-card:hover { box-shadow: 0 12px 40px rgba(115,26,68,0.15); transform: translateY(-4px); }
    .prod-img-wrap {
      width: 100%; aspect-ratio: 1;
      overflow: hidden;
      background: var(--pink-pale);
      display: flex; align-items: center; justify-content: center;
      position: relative;
    }
    .prod-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
    .product-card:hover .prod-img-wrap img { transform: scale(1.07); }
    .prod-badge {
      position: absolute; top: 0.75rem; left: 0.75rem;
      background: var(--primary);
      color: white; font-size: 0.7rem; font-weight: 700;
      padding: 0.2rem 0.625rem; border-radius: 999px;
    }
    .prod-wishlist {
      position: absolute; top: 0.75rem; right: 0.75rem;
      width: 32px; height: 32px; border-radius: 50%;
      background: rgba(255,255,255,0.9);
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: var(--primary);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: all 0.2s;
    }
    .prod-wishlist:hover { background: var(--primary); color: white; }
    .prod-info { padding: 1.25rem; }
    @media(max-width:640px){ .prod-info { padding: 0.875rem; } }
    .prod-cat { font-size: 0.72rem; color: var(--text-light); font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 0.3rem; }
    .prod-name { font-family: var(--serif); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.3rem; color: var(--text); }
    @media(max-width:640px){ .prod-name { font-size: 0.95rem; } }
    .prod-mat { font-size: 0.8rem; color: var(--text-light); margin-bottom: 0.875rem; display: flex; align-items: center; gap: 0.3rem; }
    @media(max-width:640px){ .prod-mat { font-size: 0.72rem; margin-bottom: 0.5rem; } }
    .prod-footer { display: flex; align-items: center; justify-content: space-between; }
    .prod-price { font-size: 1.35rem; font-weight: 700; color: var(--primary); }
    @media(max-width:640px){ .prod-price { font-size: 1.05rem; } }
    .prod-add {
      background: var(--primary); color: white;
      width: 40px; height: 40px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem;
      transition: all 0.2s;
      box-shadow: 0 4px 12px rgba(166,33,95,0.3);
    }
    .prod-add:hover { background: var(--primary-dk); transform: scale(1.1); }
    .img-placeholder { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; color: #ccc; }

    /* ===== ABOUT STRIP ===== */
    .about-strip { background: var(--primary-dk); color: white; }
    .about-strip .section-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
    @media(max-width:768px){ .about-strip .section-inner { grid-template-columns: 1fr; } }
    @media(max-width:768px){ .responsive-grid { grid-template-columns: 1fr !important; gap: 2rem !important; } }
    @media(max-width:768px){ .responsive-3 { grid-template-columns: 1fr !important; } }
    @media(min-width:481px) and (max-width:768px){ .responsive-3 { grid-template-columns: repeat(2, 1fr) !important; } }
    .about-values { display: flex; flex-direction: column; gap: 1.5rem; }
    .value-item { display: flex; align-items: flex-start; gap: 1rem; }
    .value-icon { width: 48px; height: 48px; border-radius: 0.875rem; background: rgba(242,119,176,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: var(--secondary); flex-shrink: 0; }
    .value-text h4 { font-family: var(--serif); font-size: 1rem; margin-bottom: 0.25rem; }
    .value-text p { font-size: 0.85rem; color: rgba(255,255,255,0.65); line-height: 1.6; }
    .about-right h2 { font-size: 2.5rem; margin-bottom: 1rem; }
    .about-right p { color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 1.5rem; }

    /* ===== REVIEWS ===== */
    .reviews-section { background: linear-gradient(135deg, var(--primary-dk) 0%, var(--primary) 100%); }
    .reviews-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
    .review-card { background: white; border-radius: 1.25rem; padding: 1.5rem; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
    .review-header { display: flex; align-items: center; gap: 0.875rem; margin-bottom: 1rem; }
    .review-avatar { width: 52px; height: 52px; border-radius: 50%; background: var(--pink-pale); border: 2px solid var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; }
    .review-name { font-family: var(--serif); font-weight: 700; font-size: 1rem; }
    .review-date { font-size: 0.75rem; color: var(--text-light); }
    .stars { color: #F59E0B; font-size: 0.9rem; margin-bottom: 0.75rem; }
    .review-text { font-size: 0.875rem; color: var(--text-light); line-height: 1.7; }
    .review-footer { display: flex; justify-content: flex-end; margin-top: 1rem; }
    .like-btn { display: flex; align-items: center; gap: 0.35rem; color: var(--primary); font-size: 0.8rem; font-weight: 600; transition: color 0.2s; }
    .like-btn:hover { color: var(--secondary); }

    /* Review form */
    .review-form-card { background: white; border-radius: 1.25rem; padding: 2rem; max-width: 560px; margin: 0 auto; box-shadow: 0 8px 32px rgba(0,0,0,0.15); }
    .review-form-card h3 { font-size: 1.4rem; text-align: center; margin-bottom: 1.5rem; color: var(--primary-dk); }
    .review-form-card input, .review-form-card textarea {
      width: 100%; background: var(--pink-pale); border: 2px solid transparent;
      border-radius: 0.75rem; padding: 0.875rem 1rem; font-size: 0.9rem; outline: none;
      transition: border-color 0.2s; margin-bottom: 1rem;
    }
    .review-form-card input:focus, .review-form-card textarea:focus { border-color: var(--primary); background: white; }
    .star-row { display: flex; gap: 0.35rem; justify-content: center; margin-bottom: 1rem; }
    .star-btn { font-size: 1.8rem; color: #ddd; transition: color 0.15s; }
    .star-btn.lit { color: #F59E0B; }
    .avatar-grid { display: grid; grid-template-columns: repeat(6,1fr); gap: 0.5rem; margin-bottom: 1rem; }
    .av-opt { padding: 0.5rem; border-radius: 0.625rem; border: 2px solid transparent; cursor: pointer; font-size: 1.4rem; text-align: center; transition: all 0.2s; background: var(--pink-pale); }
    .av-opt:hover { background: var(--pink-soft); }
    .av-opt.sel { border-color: var(--primary); background: rgba(166,33,95,0.1); }

    /* ===== NEWSLETTER ===== */
    .newsletter-section { background: var(--pink-pale); }
    .newsletter-inner { display: flex; gap: 3rem; align-items: center; flex-wrap: wrap; }
    .newsletter-text { flex: 1; min-width: 260px; }
    .newsletter-text h2 { font-size: 2rem; color: var(--primary-dk); margin-bottom: 0.75rem; }
    .newsletter-text p { color: var(--text-light); line-height: 1.7; }
    .newsletter-form { flex: 1; min-width: 280px; }
    .newsletter-input-row { display: flex; gap: 0.75rem; }
    .newsletter-input-row input { flex: 1; padding: 0.875rem 1.25rem; border: 2px solid var(--border); border-radius: 999px; outline: none; font-size: 0.9rem; background: white; }
    .newsletter-input-row input:focus { border-color: var(--primary); }
    .newsletter-perks { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem; }
    .newsletter-perk { display: flex; align-items: center; gap: 0.625rem; font-size: 0.875rem; color: var(--text-light); }
    .newsletter-perk i { color: var(--primary); }

    /* ===== BUTTONS ===== */
    .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.75rem 1.5rem; border-radius: 0.875rem; font-size: 0.9rem; font-weight: 600; font-family: var(--sans); transition: all 0.2s; cursor: pointer; border: none; }
    .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 16px rgba(166,33,95,0.25); }
    .btn-primary:hover { background: var(--primary-dk); transform: translateY(-1px); }
    .btn-outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); }
    .btn-outline:hover { background: var(--pink-pale); }
    .btn-white { background: white; color: var(--primary); font-weight: 700; }
    .btn-white:hover { background: var(--pink-pale); }

    /* ===== CART DRAWER ===== */
    .cart-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 200; display: none; animation: fadeIn 0.2s; }
    .cart-overlay.open { display: block; }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    .cart-drawer {
      position: fixed; top: 0; right: -420px; width: 420px; max-width: 100vw;
      height: 100vh; background: white; z-index: 201;
      display: flex; flex-direction: column;
      box-shadow: -8px 0 40px rgba(0,0,0,0.12);
      transition: right 0.3s ease;
    }
    .cart-drawer.open { right: 0; }
    .cart-drawer-header {
      background: var(--primary-dk); color: white;
      padding: 1.25rem 1.5rem;
      display: flex; align-items: center; justify-content: space-between;
    }
    .cart-drawer-header h2 { font-size: 1.3rem; }
    .cart-drawer-body { flex: 1; overflow-y: auto; padding: 1.25rem; }
    .cart-drawer-footer { padding: 1.25rem; border-top: 1px solid var(--border); }
    .cart-empty { text-align: center; padding: 3rem 1rem; }
    .cart-empty i { font-size: 3rem; color: var(--border); display: block; margin-bottom: 1rem; }
    .cart-item { display: flex; gap: 1rem; align-items: center; padding: 0.875rem 0; border-bottom: 1px solid var(--border); }
    .cart-item:last-child { border-bottom: none; }
    .ci-img { width: 64px; height: 64px; border-radius: 0.625rem; overflow: hidden; background: var(--pink-pale); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .ci-img img { width: 100%; height: 100%; object-fit: cover; }
    .ci-info { flex: 1; min-width: 0; }
    .ci-name { font-weight: 700; font-size: 0.9rem; }
    .ci-sub { font-size: 0.75rem; color: var(--text-light); }
    .ci-qty { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.375rem; }
    .ci-qty button { width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: all 0.2s; }
    .ci-qty button:hover { background: var(--primary); color: white; border-color: var(--primary); }
    .ci-price { font-weight: 700; color: var(--primary); font-size: 1rem; flex-shrink: 0; }
    .ci-remove { color: #dc2626; font-size: 0.85rem; margin-top: 0.25rem; }
    .ci-remove:hover { text-decoration: underline; }
    .cart-total { display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; }
    .promo-row { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
    .promo-row input { flex: 1; padding: 0.625rem 1rem; border: 2px solid var(--border); border-radius: 0.75rem; outline: none; font-size: 0.875rem; }
    .promo-row input:focus { border-color: var(--primary); }
    .btn-checkout { width: 100%; background: var(--primary); color: white; font-weight: 700; padding: 1rem; border-radius: 1rem; font-size: 1rem; box-shadow: 0 6px 20px rgba(166,33,95,0.3); }
    .btn-checkout:hover { background: var(--primary-dk); }
    .cart-trust { display: flex; justify-content: center; gap: 1.5rem; margin-top: 0.875rem; }
    .cart-trust span { font-size: 0.72rem; color: var(--text-light); display: flex; align-items: center; gap: 0.3rem; }
    .cart-trust i { color: var(--primary); }

    /* ===== CHAT ===== */
    .chat-widget { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 150; display: flex; flex-direction: column; align-items: flex-end; }
    .chat-toggle { width: 56px; height: 56px; border-radius: 50%; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 6px 20px rgba(242,119,176,0.4); transition: all 0.2s; }
    .chat-toggle:hover { background: var(--primary-light); transform: scale(1.05); }
    .chat-box { background: white; border-radius: 1.25rem; width: 320px; margin-bottom: 1rem; box-shadow: 0 12px 48px rgba(0,0,0,0.15); overflow: hidden; display: none; border: 1px solid var(--border); }
    .chat-box.open { display: block; animation: fadeUp 0.25s ease; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    .chat-header { background: var(--primary-dk); color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
    .chat-header-info { display: flex; align-items: center; gap: 0.625rem; font-weight: 700; }
    .chat-avatar { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .chat-body { padding: 1rem; background: rgba(242,119,176,0.05); min-height: 160px; }
    .chat-bubble { background: var(--pink-pale); padding: 0.75rem; border-radius: 0 0.75rem 0.75rem 0.75rem; font-size: 0.85rem; margin-bottom: 0.75rem; }
    .chat-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
    .chat-chip { background: white; border: 1px solid var(--secondary); color: var(--primary); font-size: 0.72rem; padding: 0.3rem 0.625rem; border-radius: 999px; transition: background 0.2s; }
    .chat-chip:hover { background: var(--pink-pale); }
    .chat-footer { padding: 0.75rem; border-top: 1px solid var(--border); display: flex; gap: 0.5rem; }
    .chat-footer input { flex:1; background:#f5f5f5; border:none; border-radius:999px; padding:0.5rem 0.875rem; font-size:0.85rem; outline:none; }
    .chat-send { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
    .chat-send:hover { background: var(--primary-dk); }

    /* ===== FOOTER ===== */
    .sf-footer { background: var(--primary-dk); color: white; padding: 4rem 2rem 2rem; }
    .footer-social-bar { background: rgba(255,255,255,0.1); border-radius: 1.25rem; padding: 1.25rem 2rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 3rem; flex-wrap: wrap; gap: 1rem; }
    .footer-social-bar span { font-family: var(--serif); font-style: italic; font-size: 1rem; color: rgba(255,255,255,0.85); }
    .social-icons { display: flex; gap: 0.625rem; }
    .social-icon { width: 42px; height: 42px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; transition: all 0.2s; }
    .social-icon:hover { background: var(--secondary); border-color: var(--secondary); }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
    @media(max-width:768px){ .footer-grid { grid-template-columns: 1fr; gap: 2rem; } }
    .footer-brand h3 { font-family: var(--kavoon); font-size: 1.6rem; margin-bottom: 0.75rem; }
    .footer-brand p { color: rgba(255,255,255,0.6); font-size: 0.875rem; line-height: 1.7; max-width: 280px; }
    .footer-col h4 { font-weight: 700; font-size: 0.875rem; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 1.25rem; color: var(--secondary); }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; }
    .footer-col ul li a, .footer-col ul li button { color: rgba(255,255,255,0.7); font-size: 0.875rem; transition: color 0.2s; display: flex; align-items: center; gap: 0.5rem; }
    .footer-col ul li a:hover, .footer-col ul li button:hover { color: white; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.12); padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .footer-bottom p { color: rgba(255,255,255,0.45); font-size: 0.8rem; }

    /* ===== PRODUCTS PAGE ===== */
    #page-products { display: none; }
    .page-layout { max-width: 1200px; margin: 0 auto; padding: 3rem 2rem; }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 2.5rem; color: var(--primary-dk); margin-bottom: 0.5rem; }
    .filter-bar { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem; align-items: center; }
    .filter-chip {
      padding: 0.5rem 1.25rem; border-radius: 999px;
      border: 2px solid var(--border); background: white;
      font-size: 0.875rem; font-weight: 600; cursor: pointer;
      transition: all 0.2s; color: var(--text-light);
    }
    .filter-chip:hover, .filter-chip.active { background: var(--primary); color: white; border-color: var(--primary); }
    .products-count { color: var(--text-light); font-size: 0.875rem; margin-left: auto; }

    /* ===== ABOUT PAGE ===== */
    #page-about { display: none; }

    /* ===== CONTACT PAGE ===== */
    #page-contact { display: none; }
    .contact-form-card { background: white; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }
    .contact-form-card input, .contact-form-card textarea, .contact-form-card select {
      width: 100%; padding: 0.875rem 1rem;
      border: 2px solid var(--border); border-radius: 0.75rem;
      font-size: 0.9rem; outline: none; margin-bottom: 1rem;
      transition: border-color 0.2s; background: #fafafa;
    }
    .contact-form-card input:focus, .contact-form-card textarea:focus { border-color: var(--primary); background: white; }

    /* SweetAlert custom */
    .swal2-confirm { background-color: var(--primary) !important; }
    .swal2-confirm:hover { background-color: var(--primary-dk) !important; }

    /* ===== ACTIVE PAGE ===== */
    .page { }
  </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="sf-header">
  <nav class="sf-nav">
    <a href="#" onclick="showPage('home')" class="active" id="nav-home">Inicio</a>
    <a href="#" onclick="showPage('products')" id="nav-products">Productos</a>
    <a href="#" onclick="showPage('about')" id="nav-about">Nosotros</a>
    <a href="#" onclick="showPage('contact')" id="nav-contact">Contacto</a>
  </nav>
  <div class="sf-logo-wrap">
    <img src="/uploads/logo.png" alt="Abbie BEE" style="height:56px;object-fit:contain;">
  </div>
  <div class="sf-actions">
    <div class="search-wrap">
      <input type="text" id="searchInput" placeholder="Buscar joyas…" onkeydown="if(event.key==='Enter')doSearch()"/>
      <button onclick="doSearch()"><i class="bi bi-search"></i></button>
    </div>
    <button class="icon-btn" onclick="toggleCart()" title="Carrito">
      <i class="bi bi-bag"></i>
      <span class="cart-badge" id="cartBadge">0</span>
    </button>
    <a href="login.php" class="admin-btn"><i class="bi bi-person-fill"></i> Admin</a>
  </div>
</header>

<!-- ===== HOME PAGE ===== -->
<div id="page-home">

  <!-- Hero -->
  <section class="hero">
    <div class="hero-pattern"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <div class="hero-eyebrow"><i class="bi bi-gem"></i> Joyería de edición limitada</div>
      <h1 class="hero-title">Brilla con piezas que<br><span>te definen</span></h1>
      <p class="hero-subtitle">Joyas exclusivas, resistentes al agua e hipoalergénicas. Diseñadas para mujeres que buscan distinguirse con elegancia y autenticidad.</p>
      <div class="hero-actions">
        <button class="btn-hero btn-hero-primary" onclick="showPage('products')">
          <i class="bi bi-gem"></i> Ver colección
        </button>
        <button class="btn-hero btn-hero-outline" onclick="showPage('about')">
          <i class="bi bi-play-circle"></i> Conoce la marca
        </button>
      </div>
      <div class="hero-badges">
        <span class="hero-badge"><i class="bi bi-droplet-fill"></i> Resistente al agua</span>
        <span class="hero-badge"><i class="bi bi-heart-fill"></i> Hipoalergénica</span>
        <span class="hero-badge"><i class="bi bi-star-fill"></i> Edición limitada</span>
        <span class="hero-badge"><i class="bi bi-truck"></i> Envío a todo el Perú</span>
      </div>
    </div>
  </section>

  <!-- Categorías -->
  <section class="section">
    <div class="section-inner">
      <div class="section-header">
        <span class="section-label">Explora</span>
        <h2 class="section-title">Nuestras colecciones</h2>
        <p class="section-sub">Descubre joyas únicas clasificadas por tipo y material. Cada pieza es de edición limitada.</p>
      </div>
      <div class="categories-grid">
        <div class="category-item" onclick="filterCat('Anillos')">
          <div class="category-circle">💍</div>
          <span>Anillos</span>
        </div>
        <div class="category-item" onclick="filterCat('Collares')">
          <div class="category-circle">📿</div>
          <span>Collares</span>
        </div>
        <div class="category-item" onclick="filterCat('Pulseras')">
          <div class="category-circle">✨</div>
          <span>Pulseras</span>
        </div>
        <div class="category-item" onclick="filterCat('Aretes')">
          <div class="category-circle">🪙</div>
          <span>Aretes</span>
        </div>
        <div class="category-item" onclick="filterCat('')">
          <div class="category-circle">🐝</div>
          <span>Todo</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Productos destacados -->
  <section class="section" style="background:rgba(242,223,232,0.35);padding-top:2rem">
    <div class="section-inner">
      <div class="section-header">
        <span class="section-label">⭐ Destacados</span>
        <h2 class="section-title">Piezas más populares</h2>
      </div>
      <div class="products-grid" id="featuredGrid">
        <?php foreach (array_slice(array_values($destacados), 0, 6) as $p): ?>
        <div class="product-card" onclick="openProduct(<?= $p['id'] ?>)">
          <div class="prod-img-wrap">
            <?php if ($p['imagen']): ?>
              <img src="<?= UPLOAD_URL . htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <?php else: ?>
              <div class="img-placeholder"><i class="bi bi-gem" style="font-size:3rem;color:var(--pink-soft)"></i></div>
            <?php endif; ?>
            <span class="prod-badge"><i class="bi bi-star-fill"></i> Destacado</span>
            <button class="prod-wishlist" onclick="event.stopPropagation();toggleWish(<?= $p['id'] ?>)" title="Favorito">
              <i class="bi bi-heart" id="wish-<?= $p['id'] ?>"></i>
            </button>
          </div>
          <div class="prod-info">
            <div class="prod-cat"><?= htmlspecialchars($p['categoria']) ?></div>
            <div class="prod-name"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="prod-mat"><i class="bi bi-gem"></i><?= htmlspecialchars($p['material']) ?></div>
            <div class="prod-footer">
              <span class="prod-price">S/<?= number_format($p['precio'],2) ?></span>
              <button class="prod-add" onclick="event.stopPropagation();addToCart(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', <?= $p['precio'] ?>, '<?= htmlspecialchars(addslashes($p['imagen'])) ?>', '<?= htmlspecialchars(addslashes($p['categoria'])) ?>')" title="Agregar al carrito">
                <i class="bi bi-bag-plus-fill"></i>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($destacados)): ?>
          <?php foreach (array_slice($productos, 0, 3) as $p): ?>
          <div class="product-card" onclick="openProduct(<?= $p['id'] ?>)">
            <div class="prod-img-wrap">
              <?php if ($p['imagen']): ?>
                <img src="<?= UPLOAD_URL . htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
              <?php else: ?>
                <div class="img-placeholder"><i class="bi bi-gem" style="font-size:3rem;color:var(--pink-soft)"></i></div>
              <?php endif; ?>
            </div>
            <div class="prod-info">
              <div class="prod-cat"><?= htmlspecialchars($p['categoria']) ?></div>
              <div class="prod-name"><?= htmlspecialchars($p['nombre']) ?></div>
              <div class="prod-mat"><i class="bi bi-gem"></i><?= htmlspecialchars($p['material']) ?></div>
              <div class="prod-footer">
                <span class="prod-price">S/<?= number_format($p['precio'],2) ?></span>
                <button class="prod-add" onclick="event.stopPropagation();addToCart(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', <?= $p['precio'] ?>, '<?= htmlspecialchars(addslashes($p['imagen'])) ?>', '<?= htmlspecialchars(addslashes($p['categoria'])) ?>')" title="Agregar al carrito">
                  <i class="bi bi-bag-plus-fill"></i>
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div style="text-align:center;margin-top:2.5rem">
        <button class="btn btn-primary" onclick="showPage('products')">
          <i class="bi bi-grid-3x3-gap-fill"></i> Ver toda la colección
        </button>
      </div>
    </div>
  </section>

  <!-- About strip -->
  <section class="section about-strip">
    <div class="section-inner">
      <div class="about-values">
        <div style="margin-bottom:1rem">
          <span class="section-label" style="background:rgba(242,119,176,0.2);color:var(--secondary)">La marca</span>
          <h2 style="font-size:2rem;margin-top:0.75rem;color:white">¿Por qué elegir<br>Abbie BEE?</h2>
        </div>
        <div class="value-item">
          <div class="value-icon"><i class="bi bi-droplet-fill"></i></div>
          <div class="value-text"><h4>Resistente al agua</h4><p>Todas nuestras piezas soportan el contacto con agua sin perder su brillo ni color.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon"><i class="bi bi-heart-fill"></i></div>
          <div class="value-text"><h4>Hipoalergénicas</h4><p>Materiales seleccionados aptos para pieles sensibles. Sin níquel, sin reacciones.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon"><i class="bi bi-gem"></i></div>
          <div class="value-text"><h4>Edición limitada</h4><p>Cada diseño es exclusivo y no se repite, garantizando que tu joya sea única.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon"><i class="bi bi-truck"></i></div>
          <div class="value-text"><h4>Envío a todo el Perú</h4><p>Empaque seguro y elegante. Recibe tu joya en la puerta de tu casa.</p></div>
        </div>
      </div>
      <div class="about-right">
        <h2 style="color:white">Joyería con alma, <em style="color:var(--secondary)">creada con pasión</em></h2>
        <p>Abbie BEE nació de la visión de Abigail Quintanilla, egresada de Administración de la Universidad Andina del Cusco. En poco tiempo, la marca ha crecido de ferias locales a una tienda en línea que llega a mujeres en todo el Perú.</p>
        <p>Cada pieza es seleccionada con cuidado extremo: desde el diseño hasta el acabado, pasando por la resistencia y la exclusividad. Una joya Abbie BEE no es solo un accesorio, es una declaración.</p>
        <button class="btn btn-white" onclick="showPage('about')">
          <i class="bi bi-arrow-right"></i> Conoce nuestra historia
        </button>
      </div>
    </div>
  </section>

  <!-- Reseñas -->
  <section class="section reviews-section">
    <div class="section-inner">
      <div class="section-header">
        <span class="section-label" style="background:rgba(255,255,255,0.15);color:white">⭐ Opiniones</span>
        <h2 class="section-title" style="color:white">Lo que dicen nuestras clientas</h2>
      </div>
      <div class="reviews-grid" id="reviewsGrid">
        <?php foreach ($resenas as $r): ?>
        <div class="review-card">
          <div class="review-header">
            <div class="review-avatar"><?= htmlspecialchars($r['avatar']) ?></div>
            <div>
              <div class="review-name"><?= htmlspecialchars($r['nombre']) ?></div>
              <div class="review-date"><?= date('d M Y', strtotime($r['fecha'])) ?></div>
            </div>
          </div>
          <div class="stars"><?= str_repeat('★', $r['estrellas']) . str_repeat('☆', 5-$r['estrellas']) ?></div>
          <div class="review-text"><?= htmlspecialchars($r['comentario']) ?></div>
          <div class="review-footer">
            <button class="like-btn" onclick="likeReview(<?= $r['id'] ?>, this)">
              <i class="bi bi-hand-thumbs-up"></i>
              <span><?= $r['likes'] ?></span>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Formulario reseña -->
      <div class="review-form-card">
        <h3>💬 Deja tu reseña</h3>
        <div class="star-row" id="starRow">
          <?php for ($i=1;$i<=5;$i++): ?>
            <button class="star-btn" data-star="<?= $i ?>" onclick="setStar(<?= $i ?>)">★</button>
          <?php endfor; ?>
        </div>
        <div class="avatar-grid" id="avatarGrid">
          <?php foreach (['🐝','🌸','💎','⭐','🦋','💖'] as $av): ?>
            <div class="av-opt" onclick="selectAv(this)" data-av="<?= $av ?>"><?= $av ?></div>
          <?php endforeach; ?>
        </div>
        <input type="text" id="reviewName" placeholder="Tu nombre *">
        <textarea id="reviewText" rows="3" placeholder="Cuéntanos tu experiencia con Abbie BEE…"></textarea>
        <button class="btn btn-primary" style="width:100%" onclick="submitReview()">
          <i class="bi bi-send-fill"></i> Publicar reseña
        </button>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section class="section newsletter-section">
    <div class="section-inner">
      <div class="newsletter-inner">
        <div class="newsletter-text">
          <span class="section-label">Newsletter</span>
          <h2>¡Sé la primera en enterarte!</h2>
          <p>Suscríbete y recibe notificaciones de nuevos lanzamientos de edición limitada, promociones exclusivas y tips de estilo.</p>
          <div class="newsletter-perks">
            <div class="newsletter-perk"><i class="bi bi-gift-fill"></i> 10% de descuento en tu primera compra</div>
            <div class="newsletter-perk"><i class="bi bi-star-fill"></i> Acceso anticipado a colecciones</div>
            <div class="newsletter-perk"><i class="bi bi-bell-fill"></i> Alertas de stock limitado</div>
          </div>
        </div>
        <div class="newsletter-form">
          <p style="font-weight:600;margin-bottom:1rem;color:var(--primary-dk)">Ingresa tu correo electrónico</p>
          <div class="newsletter-input-row">
            <input type="email" id="newsletterEmail" placeholder="tu@correo.com">
            <button class="btn btn-primary" onclick="subscribeNewsletter()">
              <i class="bi bi-send-fill"></i> Suscribirme
            </button>
          </div>
          <p style="font-size:0.75rem;color:var(--text-light);margin-top:0.75rem">
            <i class="bi bi-shield-check"></i> No spam. Puedes darte de baja en cualquier momento.
          </p>
        </div>
      </div>
    </div>
  </section>

</div><!-- /home -->

<!-- ===== PRODUCTS PAGE ===== -->
<div id="page-products" style="display:none">
  <div class="page-layout">
    <div class="page-header">
      <h1>Colección completa</h1>
      <p style="color:var(--text-light)">Todas nuestras joyas de edición limitada</p>
    </div>
    <div class="filter-bar">
      <button class="filter-chip active" onclick="applyFilter('',this)">Todas</button>
      <button class="filter-chip" onclick="applyFilter('Anillos',this)">💍 Anillos</button>
      <button class="filter-chip" onclick="applyFilter('Collares',this)">📿 Collares</button>
      <button class="filter-chip" onclick="applyFilter('Pulseras',this)">✨ Pulseras</button>
      <button class="filter-chip" onclick="applyFilter('Aretes',this)">🪙 Aretes</button>
      <button class="filter-chip" onclick="applyFilter('Accesorios',this)">🌟 Accesorios</button>
      <span class="products-count" id="prodCount"></span>
    </div>
    <div class="products-grid" id="allProductsGrid"></div>
  </div>
</div>

<!-- ===== ABOUT PAGE ===== -->
<div id="page-about" style="display:none">
  <div class="page-layout" style="max-width:960px">
    <div style="text-align:center;margin-bottom:4rem">
      <span class="section-label">Nuestra historia</span>
      <h1 style="font-size:3rem;color:var(--primary-dk);margin:0.75rem 0">Sobre Abbie BEE</h1>
      <p style="color:var(--text-light);max-width:500px;margin:0 auto;line-height:1.8">Una marca nacida de la pasión por la joyería exclusiva y el deseo de hacer brillar a cada mujer peruana.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;margin-bottom:4rem;align-items:center" class="responsive-grid">
      <div style="background:var(--pink-pale);border-radius:2rem;padding:3rem;text-align:center;font-size:5rem">🐝</div>
      <div>
        <h2 style="font-size:1.8rem;color:var(--primary-dk);margin-bottom:1rem">La fundadora</h2>
        <p style="color:var(--text-light);line-height:1.8;margin-bottom:1rem">Abbie BEE fue fundada por <strong style="color:var(--primary)">Abigail Quintanilla</strong>, egresada de la carrera de Administración de la Universidad Andina del Cusco. Con un ojo para el detalle y una pasión profunda por la joyería contemporánea, creó la marca hace un año desde Cusco, Perú.</p>
        <p style="color:var(--text-light);line-height:1.8">Lo que comenzó como un proyecto personal se convirtió en una marca que hoy vende a mujeres en todo el país, con una filosofía clara: joyas que duran, diseños que no se repiten.</p>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:4rem" class="responsive-3">
      <div style="background:white;border-radius:1.25rem;padding:2rem;text-align:center;box-shadow:0 2px 16px rgba(0,0,0,0.06)">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">💎</div>
        <h3 style="font-size:1.1rem;margin-bottom:0.5rem;color:var(--primary-dk)">Calidad premium</h3>
        <p style="font-size:0.875rem;color:var(--text-light);line-height:1.6">Materiales seleccionados: acero inoxidable, enchapado en oro rosa 18K y rodio de alta pureza.</p>
      </div>
      <div style="background:white;border-radius:1.25rem;padding:2rem;text-align:center;box-shadow:0 2px 16px rgba(0,0,0,0.06)">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">🌊</div>
        <h3 style="font-size:1.1rem;margin-bottom:0.5rem;color:var(--primary-dk)">Resistente al agua</h3>
        <p style="font-size:0.875rem;color:var(--text-light);line-height:1.6">Úsala en la ducha, en la piscina o en el mar. Nuestras joyas no cambian su color ni se deterioran.</p>
      </div>
      <div style="background:white;border-radius:1.25rem;padding:2rem;text-align:center;box-shadow:0 2px 16px rgba(0,0,0,0.06)">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">✨</div>
        <h3 style="font-size:1.1rem;margin-bottom:0.5rem;color:var(--primary-dk)">Edición limitada</h3>
        <p style="font-size:0.875rem;color:var(--text-light);line-height:1.6">Cada diseño es único. Cuando se acaba el stock, ese modelo no vuelve. Tu joya es verdaderamente exclusiva.</p>
      </div>
    </div>
    <div style="background:var(--pink-pale);border-radius:2rem;padding:3rem;text-align:center">
      <h2 style="color:var(--primary-dk);margin-bottom:1rem">¿Tienes alguna pregunta?</h2>
      <p style="color:var(--text-light);margin-bottom:1.5rem">Contáctanos y te responderemos a la brevedad. ¡Estamos aquí para ayudarte!</p>
      <button class="btn btn-primary" onclick="showPage('contact')"><i class="bi bi-envelope-fill"></i> Contáctanos</button>
    </div>
  </div>
</div>

<!-- ===== CONTACT PAGE ===== -->
<div id="page-contact" style="display:none">
  <div class="page-layout" style="max-width:960px">
    <div style="text-align:center;margin-bottom:3rem">
      <span class="section-label">Contacto</span>
      <h1 style="font-size:2.8rem;color:var(--primary-dk);margin:0.75rem 0">¿Cómo podemos ayudarte?</h1>
      <p style="color:var(--text-light)">Escríbenos y te responderemos en menos de 24 horas.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:3rem" class="responsive-grid">
      <div>
        <div style="background:white;border-radius:1.25rem;padding:2rem;box-shadow:0 2px 16px rgba(0,0,0,0.06);margin-bottom:1.25rem">
          <h3 style="margin-bottom:1.25rem;color:var(--primary-dk)">Información de contacto</h3>
          <div style="display:flex;flex-direction:column;gap:1.25rem">
            <div style="display:flex;align-items:flex-start;gap:1rem">
              <div style="width:44px;height:44px;border-radius:50%;background:var(--pink-pale);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0"><i class="bi bi-telephone-fill"></i></div>
              <div><strong>Teléfono</strong><br><span style="color:var(--text-light);font-size:0.875rem">997 933 929 (WhatsApp)</span></div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:1rem">
              <div style="width:44px;height:44px;border-radius:50%;background:var(--pink-pale);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0"><i class="bi bi-instagram"></i></div>
              <div><strong>Instagram</strong><br><a href="https://www.instagram.com/abbiebee.joyas" target="_blank" style="color:var(--primary);font-size:0.875rem">@abbiebee.joyas</a></div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:1rem">
              <div style="width:44px;height:44px;border-radius:50%;background:var(--pink-pale);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0"><i class="bi bi-geo-alt-fill"></i></div>
              <div><strong>Ubicación</strong><br><span style="color:var(--text-light);font-size:0.875rem">Cusco, Perú</span></div>
            </div>
          </div>
        </div>
        <div style="background:var(--pink-pale);border-radius:1.25rem;padding:1.5rem">
          <h4 style="color:var(--primary-dk);margin-bottom:0.75rem">Síguenos en redes</h4>
          <div style="display:flex;gap:0.75rem">
            <a href="https://www.instagram.com/abbiebee.joyas" target="_blank" class="social-icon" style="border-color:var(--border);color:var(--primary)"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon" style="border-color:var(--border);color:var(--primary)"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon" style="border-color:var(--border);color:var(--primary)"><i class="bi bi-tiktok"></i></a>
            <a href="https://wa.me/51997933929" target="_blank" class="social-icon" style="border-color:var(--border);color:var(--primary)"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>
      </div>
      <div class="contact-form-card">
        <h3 style="margin-bottom:1.5rem;color:var(--primary-dk)">Envíanos un mensaje</h3>
        <input type="text" id="cName" placeholder="Tu nombre completo *">
        <input type="email" id="cEmail" placeholder="Tu correo electrónico *">
        <select id="cSubject">
          <option value="">-- Selecciona el asunto --</option>
          <option>Consulta sobre producto</option>
          <option>Estado de mi pedido</option>
          <option>Devolución o cambio</option>
          <option>Colaboración o prensa</option>
          <option>Otro</option>
        </select>
        <textarea id="cMsg" rows="4" placeholder="Escribe tu mensaje aquí…"></textarea>
        <button class="btn btn-primary" style="width:100%" onclick="submitContact()">
          <i class="bi bi-send-fill"></i> Enviar mensaje
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===== FOOTER ===== -->
<footer class="sf-footer">
  <div style="max-width:1200px;margin:0 auto">
    <div class="footer-social-bar">
      <span>✨ Síguenos y forma parte de la comunidad Abbie BEE</span>
      <div class="social-icons">
        <a href="https://www.instagram.com/abbiebee.joyas" target="_blank" class="social-icon" title="Instagram"><i class="bi bi-instagram"></i></a>
        <a href="#" class="social-icon" title="Facebook"><i class="bi bi-facebook"></i></a>
        <a href="#" class="social-icon" title="TikTok"><i class="bi bi-tiktok"></i></a>
        <a href="https://wa.me/51997933929" target="_blank" class="social-icon" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
      </div>
    </div>
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="/uploads/logo.png" alt="Abbie BEE" style="height:64px;object-fit:contain;margin-bottom:0.75rem">
        <p>Joyería de edición limitada. Piezas únicas, hipoalergénicas y resistentes al agua, diseñadas para mujeres que brillan con personalidad.</p>
        <p style="margin-top:1rem;color:rgba(255,255,255,0.45);font-size:0.8rem">Cusco, Perú · Envíos nacionales</p>
      </div>
      <div class="footer-col">
        <h4>Navegación</h4>
        <ul>
          <li><a onclick="showPage('home')"><i class="bi bi-house-fill"></i> Inicio</a></li>
          <li><a onclick="showPage('products')"><i class="bi bi-gem"></i> Productos</a></li>
          <li><a onclick="showPage('about')"><i class="bi bi-info-circle-fill"></i> Nosotros</a></li>
          <li><a onclick="showPage('contact')"><i class="bi bi-envelope-fill"></i> Contacto</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Información</h4>
        <ul>
          <li><button onclick="Swal.fire('Términos','Todos los pedidos están sujetos a disponibilidad de stock. Las piezas de edición limitada no se reponen.','info')"><i class="bi bi-file-text"></i> Términos y condiciones</button></li>
          <li><button onclick="Swal.fire('Privacidad','Tus datos personales son confidenciales y no son compartidos con terceros.','info')"><i class="bi bi-shield-lock-fill"></i> Privacidad</button></li>
          <li><button onclick="Swal.fire('Envíos','Realizamos envíos a todo el Perú vía Olva Courier o Serpost. El tiempo de entrega es de 3-7 días hábiles.','info')"><i class="bi bi-truck-fill"></i> Políticas de envío</button></li>
          <li><button onclick="Swal.fire('Devoluciones','Aceptamos cambios dentro de los 7 días de recibido el producto, siempre que esté en perfectas condiciones.','info')"><i class="bi bi-arrow-repeat"></i> Cambios y devoluciones</button></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> Abbie BEE Joyería. Todos los derechos reservados.</p>
      <p>Hecho con 💗 en Cusco, Perú</p>
    </div>
  </div>
</footer>

<!-- ===== CART DRAWER ===== -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-drawer-header">
    <h2><i class="bi bi-bag-fill"></i> Mi carrito</h2>
    <button onclick="toggleCart()" style="color:white;font-size:1.2rem"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="cart-drawer-body" id="cartBody"></div>
  <div class="cart-drawer-footer" id="cartFooter" style="display:none">
    <div class="promo-row">
      <input type="text" id="promoInput" placeholder="Código de descuento">
      <button class="btn btn-outline btn-sm" onclick="applyPromo()">Aplicar</button>
    </div>
    <div id="cartDiscountRows"></div>
    <div class="cart-total">
      <span>Total</span>
      <span id="cartTotalAmt">S/0.00</span>
    </div>
    <button class="btn-checkout" onclick="checkout()">
      <i class="bi bi-credit-card-fill"></i> Proceder al pago
    </button>
    <div class="cart-trust">
      <span><i class="bi bi-shield-lock-fill"></i> Pago seguro</span>
      <span><i class="bi bi-truck-fill"></i> Envío a todo Perú</span>
      <span><i class="bi bi-arrow-repeat"></i> Cambios gratis</span>
    </div>
  </div>
</div>

<!-- ===== CHAT ===== -->
<div class="chat-widget">
  <div class="chat-box" id="chatBox">
    <div class="chat-header">
      <div class="chat-header-info">
        <div class="chat-avatar">🐝</div>
        <span>Asistente Abbie</span>
      </div>
      <button onclick="toggleChat()" style="color:white;font-size:1.1rem"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="chat-body">
      <div class="chat-bubble">¡Hola! 🐝 Soy Abbie, tu asistente personal. ¿En qué te puedo ayudar hoy?</div>
      <div class="chat-chips">
        <button class="chat-chip" onclick="chatReply('Puedes ver nuestros productos en la sección Colección completa. ¡Hay piezas hermosas esperándote!')">Ver productos</button>
        <button class="chat-chip" onclick="chatReply('Realizamos envíos a todo el Perú vía Olva Courier o Serpost en 3-7 días hábiles.')">Info de envíos</button>
        <button class="chat-chip" onclick="chatReply('Escríbenos al WhatsApp: 997 933 929 y rastreamos tu pedido en seguida.')">Rastrear pedido</button>
        <button class="chat-chip" onclick="chatReply('¡Tenemos promociones activas! Usa el código ABBIE10 para un 10% de descuento en tu primera compra.')">Promociones</button>
      </div>
    </div>
    <div class="chat-footer">
      <input type="text" id="chatInput" placeholder="Escribe tu mensaje…" onkeydown="if(event.key==='Enter')sendChat()">
      <button class="chat-send" onclick="sendChat()"><i class="bi bi-send-fill"></i></button>
    </div>
  </div>
  <button class="chat-toggle" onclick="toggleChat()"><i class="bi bi-chat-dots-fill"></i></button>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
// ===== DATA FROM PHP =====
const ALL_PRODUCTS = <?= json_encode(array_values($productos), JSON_UNESCAPED_UNICODE) ?>;
const UPLOAD_URL   = '<?= UPLOAD_URL ?>';

// ===== CART =====
let cart = JSON.parse(localStorage.getItem('abbie_cart') || '[]');

function saveCart() {
  localStorage.setItem('abbie_cart', JSON.stringify(cart));
  updateCartBadge();
}

function updateCartBadge() {
  const total = cart.reduce((a, c) => a + c.qty, 0);
  const badge = document.getElementById('cartBadge');
  badge.textContent = total;
  badge.classList.toggle('visible', total > 0);
}

function addToCart(id, name, price, img, cat) {
  const existing = cart.find(c => c.id === id);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ id, name, price: parseFloat(price), img, cat, qty: 1 });
  }
  saveCart();
  Swal.fire({ icon:'success', title:'¡Agregado!', text:`${name} fue añadido al carrito.`, timer:1500, showConfirmButton:false, position:'top-end', toast:true });
}

function renderCart() {
  const body   = document.getElementById('cartBody');
  const footer = document.getElementById('cartFooter');
  if (cart.length === 0) {
    body.innerHTML = `<div class="cart-empty"><i class="bi bi-bag-x"></i><h3 style="font-family:var(--serif);color:var(--primary-dk)">Tu carrito está vacío</h3><p style="color:var(--text-light);margin-top:0.5rem">Agrega algunas joyas para continuar.</p><button class="btn btn-primary" style="margin-top:1.5rem" onclick="toggleCart();showPage('products')"><i class="bi bi-gem"></i> Ver colección</button></div>`;
    footer.style.display = 'none';
    return;
  }
  footer.style.display = 'block';
  let total = 0;
  body.innerHTML = cart.map(item => {
    total += item.price * item.qty;
    const imgHtml = item.img ? `<img src="${UPLOAD_URL}${item.img}" alt="${item.name}">` : '🐝';
    return `<div class="cart-item">
      <div class="ci-img">${imgHtml}</div>
      <div class="ci-info">
        <div class="ci-name">${item.name}</div>
        <div class="ci-sub">${item.cat || ''}</div>
        <div class="ci-qty">
          <button onclick="changeQty(${item.id},-1)">−</button>
          <span>${item.qty}</span>
          <button onclick="changeQty(${item.id},1)">+</button>
        </div>
        <button class="ci-remove" onclick="removeItem(${item.id})"><i class="bi bi-trash3"></i> Quitar</button>
      </div>
      <div class="ci-price">S/${(item.price * item.qty).toFixed(2)}</div>
    </div>`;
  }).join('');
  const subtotal = total;
  let discount = 0;
  let totalHtml = '';
  if (appliedPromo) {
    discount = subtotal * (appliedPromo.pct / 100);
    const finalTotal = subtotal - discount;
    totalHtml = `
      <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:var(--text-light);margin-bottom:0.3rem">
        <span>Subtotal</span><span>S/${subtotal.toFixed(2)}</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:#16a34a;margin-bottom:0.5rem">
        <span><i class="bi bi-tag-fill"></i> Descuento (${appliedPromo.code} −${appliedPromo.pct}%)</span>
        <span>−S/${discount.toFixed(2)}</span>
      </div>`;
    document.getElementById('cartTotalAmt').textContent = 'S/' + finalTotal.toFixed(2);
  } else {
    document.getElementById('cartTotalAmt').textContent = 'S/' + subtotal.toFixed(2);
  }
  const discountRow = document.getElementById('cartDiscountRows');
  if (discountRow) discountRow.innerHTML = totalHtml;
}

function changeQty(id, delta) {
  const item = cart.find(c => c.id === id);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) cart = cart.filter(c => c.id !== id);
  saveCart(); renderCart();
}
function removeItem(id) { cart = cart.filter(c => c.id !== id); saveCart(); renderCart(); }

function toggleCart() {
  const drawer  = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartOverlay');
  const isOpen  = drawer.classList.contains('open');
  drawer.classList.toggle('open', !isOpen);
  overlay.classList.toggle('open', !isOpen);
  if (!isOpen) renderCart();
}

// ===== PROMO / DISCOUNT =====
const PROMO_CODES = {
  'ABBIE10': { pct: 10, label: '10% de descuento' },
  'ABBIE15': { pct: 15, label: '15% de descuento' },
  'LANITA20': { pct: 20, label: '20% de descuento' },
};
let appliedPromo = null;

function applyPromo() {
  const code = document.getElementById('promoInput').value.trim().toUpperCase();
  if (PROMO_CODES[code]) {
    appliedPromo = { code, ...PROMO_CODES[code] };
    Swal.fire({ icon:'success', title:'¡Código aplicado!', text: appliedPromo.label + ' aplicado a tu carrito.', timer:2000, showConfirmButton:false });
    renderCart();
  } else {
    appliedPromo = null;
    Swal.fire({ icon:'error', title:'Código inválido', text:'El código ingresado no es válido o ya expiró.', timer:2000, showConfirmButton:false });
    renderCart();
  }
}

function checkout() {
  if (cart.length === 0) return;
  Swal.fire({
    title: '¡Gracias por tu pedido! 💖',
    html: `Tu pedido ha sido recibido. Te contactaremos via WhatsApp al <strong>997 933 929</strong> para coordinar el pago y envío.<br><br><a href="https://wa.me/51997933929" target="_blank" style="color:var(--primary);font-weight:700"><i class="bi bi-whatsapp"></i> Contactar ahora</a>`,
    icon: 'success',
    confirmButtonText: 'Entendido'
  }).then(() => { cart = []; appliedPromo = null; saveCart(); renderCart(); toggleCart(); });
}

// ===== PAGES =====
function showPage(page) {
  ['home','products','about','contact'].forEach(p => {
    document.getElementById('page-' + p).style.display = 'none';
    const nav = document.getElementById('nav-' + p);
    if (nav) nav.classList.remove('active');
  });
  document.getElementById('page-' + page).style.display = 'block';
  const nav = document.getElementById('nav-' + page);
  if (nav) nav.classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  if (page === 'products') renderAllProducts('');
}

// ===== PRODUCTS =====
let currentFilter = '';

function renderAllProducts(cat) {
  currentFilter = cat;
  const grid = document.getElementById('allProductsGrid');
  const count = document.getElementById('prodCount');
  let filtered = cat ? ALL_PRODUCTS.filter(p => p.categoria === cat) : ALL_PRODUCTS;
  count.textContent = `${filtered.length} producto${filtered.length !== 1 ? 's' : ''}`;
  if (!filtered.length) {
    grid.innerHTML = '<div style="text-align:center;padding:3rem;color:var(--text-light);grid-column:1/-1"><i class="bi bi-gem" style="font-size:3rem;display:block;margin-bottom:1rem;color:var(--border)"></i>No hay productos en esta categoría aún.</div>';
    return;
  }
  grid.innerHTML = filtered.map(p => {
    const imgHtml = p.imagen ? `<img src="${UPLOAD_URL}${p.imagen}" alt="${p.nombre}">` : `<div class="img-placeholder"><i class="bi bi-gem" style="font-size:3rem;color:var(--pink-soft)"></i></div>`;
    const badge   = p.destacado ? '<span class="prod-badge"><i class="bi bi-star-fill"></i> Destacado</span>' : '';
    return `<div class="product-card" onclick="openProduct(${p.id})">
      <div class="prod-img-wrap">
        ${imgHtml}
        ${badge}
        <button class="prod-wishlist" onclick="event.stopPropagation();toggleWish(${p.id})" title="Favorito">
          <i class="bi bi-heart" id="wish-${p.id}"></i>
        </button>
      </div>
      <div class="prod-info">
        <div class="prod-cat">${p.categoria}</div>
        <div class="prod-name">${p.nombre}</div>
        <div class="prod-mat"><i class="bi bi-gem"></i>${p.material}</div>
        <div class="prod-footer">
          <span class="prod-price">S/${parseFloat(p.precio).toFixed(2)}</span>
          <button class="prod-add" onclick="event.stopPropagation();addToCart(${p.id},'${p.nombre.replace(/'/g,"\\'")}',${p.precio},'${p.imagen}','${p.categoria}')" title="Agregar">
            <i class="bi bi-bag-plus-fill"></i>
          </button>
        </div>
        ${p.stock < 5 && p.stock > 0 ? `<p style="color:#e11d48;font-size:0.75rem;margin-top:0.375rem">⚠️ Solo quedan ${p.stock} unidades</p>` : ''}
        ${p.stock === 0 ? '<p style="color:#9ca3af;font-size:0.75rem;margin-top:0.375rem">Agotado</p>' : ''}
      </div>
    </div>`;
  }).join('');
}

function applyFilter(cat, btn) {
  document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  renderAllProducts(cat);
}

function filterCat(cat) {
  showPage('products');
  setTimeout(() => {
    const chips = document.querySelectorAll('.filter-chip');
    chips.forEach(c => { c.classList.remove('active'); if (c.textContent.trim().includes(cat) || (!cat && c.textContent.trim() === 'Todas')) c.classList.add('active'); });
    renderAllProducts(cat);
  }, 100);
}

function openProduct(id) {
  const p = ALL_PRODUCTS.find(x => x.id === id);
  if (!p) return;
  const imgHtml = p.imagen ? `<img src="${UPLOAD_URL}${p.imagen}" alt="${p.nombre}" style="width:100%;border-radius:1rem;object-fit:cover;max-height:280px">` : `<div style="background:var(--pink-pale);border-radius:1rem;height:200px;display:flex;align-items:center;justify-content:center;font-size:4rem">🐝</div>`;
  Swal.fire({
    html: `
      ${imgHtml}
      <div style="padding:1rem 0">
        <div style="font-size:0.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem">${p.categoria}</div>
        <h2 style="font-family:var(--serif);color:var(--primary-dk);font-size:1.5rem;margin-bottom:0.5rem">${p.nombre}</h2>
        <div style="color:var(--text-light);font-size:0.875rem;margin-bottom:0.875rem"><i class="bi bi-gem"></i> ${p.material}</div>
        <p style="color:var(--text-light);font-size:0.875rem;line-height:1.7;margin-bottom:1rem">${p.descripcion || 'Joya de edición limitada, resistente al agua e hipoalergénica.'}</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-bottom:0.75rem">
          <span style="background:var(--pink-pale);color:var(--primary);padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:700"><i class="bi bi-droplet-fill"></i> Resistente al agua</span>
          <span style="background:var(--pink-pale);color:var(--primary);padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:700"><i class="bi bi-heart-fill"></i> Hipoalergénica</span>
        </div>
        <div style="font-size:2rem;font-weight:700;color:var(--primary)">S/${parseFloat(p.precio).toFixed(2)}</div>
        ${p.stock < 5 && p.stock > 0 ? `<p style="color:#e11d48;font-size:0.8rem">⚠️ ¡Solo quedan ${p.stock} unidades!</p>` : ''}
        ${p.stock === 0 ? '<p style="color:#9ca3af;font-size:0.8rem">Agotado actualmente</p>' : ''}
      </div>
    `,
    showConfirmButton: p.stock > 0,
    confirmButtonText: '<i class="bi bi-bag-plus-fill"></i> Agregar al carrito',
    showCancelButton: true,
    cancelButtonText: 'Cerrar',
    width: Math.min(480, window.innerWidth - 32) + 'px'
  }).then(r => {
    if (r.isConfirmed) addToCart(p.id, p.nombre, p.precio, p.imagen, p.categoria);
  });
}

function toggleWish(id) {
  const icon = document.getElementById('wish-' + id);
  if (!icon) return;
  const isLiked = icon.classList.contains('bi-heart-fill');
  icon.className = isLiked ? 'bi bi-heart' : 'bi bi-heart-fill';
  if (!isLiked) {
    icon.style.color = '#e11d48';
    Swal.fire({ icon:'success', title:'💖 Agregado a favoritos', timer:1200, showConfirmButton:false, toast:true, position:'top-end' });
  } else {
    icon.style.color = '';
  }
}

function doSearch() {
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  if (!q) return;
  showPage('products');
  setTimeout(() => {
    const filtered = ALL_PRODUCTS.filter(p => p.nombre.toLowerCase().includes(q) || (p.descripcion||'').toLowerCase().includes(q));
    const grid = document.getElementById('allProductsGrid');
    const count = document.getElementById('prodCount');
    count.textContent = `${filtered.length} resultado(s) para "${q}"`;
    if (!filtered.length) {
      grid.innerHTML = `<div style="text-align:center;padding:3rem;grid-column:1/-1;color:var(--text-light)"><i class="bi bi-search" style="font-size:3rem;display:block;margin-bottom:1rem;color:var(--border)"></i>No encontramos resultados para "<strong>${q}</strong>".<br>Prueba con otra búsqueda.</div>`;
    } else {
      currentFilter = '';
      const chips = document.querySelectorAll('.filter-chip');
      chips.forEach(c => { c.classList.remove('active'); if (c.textContent.trim() === 'Todas') c.classList.add('active'); });
      grid.innerHTML = filtered.map(p => {
        const imgHtml = p.imagen ? `<img src="${UPLOAD_URL}${p.imagen}" alt="${p.nombre}">` : `<div class="img-placeholder"><i class="bi bi-gem" style="font-size:3rem;color:var(--pink-soft)"></i></div>`;
        return `<div class="product-card" onclick="openProduct(${p.id})">
          <div class="prod-img-wrap">${imgHtml}</div>
          <div class="prod-info">
            <div class="prod-cat">${p.categoria}</div>
            <div class="prod-name">${p.nombre}</div>
            <div class="prod-mat"><i class="bi bi-gem"></i>${p.material}</div>
            <div class="prod-footer">
              <span class="prod-price">S/${parseFloat(p.precio).toFixed(2)}</span>
              <button class="prod-add" onclick="event.stopPropagation();addToCart(${p.id},'${p.nombre.replace(/'/g,"\\'")}',${p.precio},'${p.imagen}','${p.categoria}')"><i class="bi bi-bag-plus-fill"></i></button>
            </div>
          </div>
        </div>`;
      }).join('');
    }
  }, 100);
}

// ===== REVIEWS =====
let selectedStar = 5;
let selectedAv   = '🐝';

function setStar(n) {
  selectedStar = n;
  document.querySelectorAll('.star-btn').forEach((b,i) => b.classList.toggle('lit', i < n));
}
function selectAv(el) {
  document.querySelectorAll('.av-opt').forEach(a => a.classList.remove('sel'));
  el.classList.add('sel');
  selectedAv = el.dataset.av;
}
// Init star / avatar
document.querySelectorAll('.star-btn').forEach((b,i) => b.classList.toggle('lit', i < 5));
document.querySelector('.av-opt')?.classList.add('sel');

async function submitReview() {
  const nombre    = document.getElementById('reviewName').value.trim();
  const comentario= document.getElementById('reviewText').value.trim();
  if (!nombre || !comentario) {
    Swal.fire({ icon:'warning', title:'Campos incompletos', text:'Ingresa tu nombre y comentario.', timer:2000, showConfirmButton:false }); return;
  }
  const res  = await fetch('api/resenas.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nombre, comentario, estrellas: selectedStar, avatar: selectedAv })
  });
  const data = await res.json();
  if (data.success) {
    Swal.fire({ icon:'success', title:'¡Gracias!', text:'Tu reseña fue publicada.', timer:2000, showConfirmButton:false });
    document.getElementById('reviewName').value = '';
    document.getElementById('reviewText').value = '';
    setTimeout(() => location.reload(), 2100);
  }
}

async function likeReview(id, btn) {
  await fetch('api/resenas.php', { method:'PATCH', body:`id=${id}`, headers:{'Content-Type':'application/x-www-form-urlencoded'} });
  const span = btn.querySelector('span');
  span.textContent = parseInt(span.textContent) + 1;
  btn.disabled = true;
  btn.style.color = 'var(--secondary)';
}

// ===== NEWSLETTER =====
function subscribeNewsletter() {
  const email = document.getElementById('newsletterEmail').value.trim();
  if (!email || !email.includes('@')) {
    Swal.fire({ icon:'warning', title:'Correo inválido', text:'Ingresa un correo electrónico válido.', timer:2000, showConfirmButton:false }); return;
  }
  Swal.fire({ icon:'success', title:'¡Suscrita! 💖', text:'Recibirás nuestras novedades y promociones exclusivas.', timer:2500, showConfirmButton:false });
  document.getElementById('newsletterEmail').value = '';
}

// ===== CHAT =====
function toggleChat() {
  document.getElementById('chatBox').classList.toggle('open');
}

function chatReply(msg) {
  const body = document.querySelector('.chat-body');
  const bubble = document.createElement('div');
  bubble.className = 'chat-bubble';
  bubble.style.cssText = 'background:var(--pink-pale);margin-top:0.5rem';
  bubble.textContent = msg;
  body.appendChild(bubble);
  body.scrollTop = body.scrollHeight;
}

function sendChat() {
  const input = document.getElementById('chatInput');
  const msg   = input.value.trim();
  if (!msg) return;
  chatReply(msg);
  input.value = '';
  setTimeout(() => chatReply('Gracias por tu mensaje 🐝. Para una respuesta inmediata, contáctanos al WhatsApp 997 933 929 o revisa nuestras opciones rápidas.'), 800);
}

// ===== CONTACT =====
function submitContact() {
  const name  = document.getElementById('cName').value.trim();
  const email = document.getElementById('cEmail').value.trim();
  const msg   = document.getElementById('cMsg').value.trim();
  if (!name || !email || !msg) {
    Swal.fire({ icon:'warning', title:'Completa los campos', text:'Por favor llena todos los campos requeridos.', timer:2000, showConfirmButton:false }); return;
  }
  Swal.fire({ icon:'success', title:'¡Mensaje enviado! 💌', text:'Te responderemos en menos de 24 horas. ¡Gracias!', timer:2500, showConfirmButton:false });
  ['cName','cEmail','cMsg'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('cSubject').selectedIndex = 0;
}

// ===== INIT =====
updateCartBadge();
</script>
</body>
</html>