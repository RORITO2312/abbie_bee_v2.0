<?php
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

require_once __DIR__ . '/config.php';

// Si ya está logueado, redirigir
if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db  = getDB();
        $stmt = $db->prepare("SELECT id, password_hash, nombre FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id']     = $user['id'];
            $_SESSION['admin_nombre'] = $user['nombre'];
            $_SESSION['admin_user']   = $username;
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Abbie BEE — Acceso Administrador</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <style>
    :root {
      --primary: #731A44;
      --primary-light: #A6215F;
      --secondary: #F277B0;
      --pink-soft: #F2A7CA;
      --bg-pink: #F2DFE8;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      background: linear-gradient(135deg, var(--primary) 0%, #A6215F 60%, #F277B0 100%);
      position: relative;
      overflow: hidden;
    }
    /* Decorative circles */
    body::before {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      top: -120px; right: -120px;
    }
    body::after {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      border-radius: 50%;
      background: rgba(255,255,255,0.05);
      bottom: -80px; left: -60px;
    }

    .login-wrapper {
      display: flex;
      width: 100%;
      min-height: 100vh;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      position: relative;
      z-index: 1;
    }

    .login-card {
      background: white;
      border-radius: 2rem;
      padding: 3rem 2.5rem;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 24px 80px rgba(0,0,0,0.25);
      text-align: center;
      animation: fadeUp 0.5s ease;
    }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(24px); }
      to   { opacity:1; transform:translateY(0); }
    }

    .login-logo {
      width: 90px; height: 90px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 2.5rem;
      box-shadow: 0 8px 32px rgba(115,26,68,0.3);
    }
    .login-title {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 900;
      color: var(--primary);
      margin-bottom: 0.25rem;
    }
    .login-subtitle {
      font-size: 0.875rem;
      color: #888;
      margin-bottom: 2.5rem;
    }

    .form-group {
      text-align: left;
      margin-bottom: 1.25rem;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      font-size: 0.875rem;
      color: #444;
      margin-bottom: 0.5rem;
    }
    .input-wrap {
      position: relative;
    }
    .input-wrap i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #bbb;
      font-size: 1rem;
    }
    .form-group input {
      width: 100%;
      padding: 0.875rem 1rem 0.875rem 2.75rem;
      border: 2px solid #eee;
      border-radius: 0.875rem;
      font-size: 0.95rem;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      transition: border-color 0.2s;
      background: #fafafa;
    }
    .form-group input:focus {
      border-color: var(--primary);
      background: white;
    }

    .toggle-pass {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #aaa;
      cursor: pointer;
      font-size: 1rem;
      display: flex;
      padding: 0;
    }

    .error-msg {
      background: #fff0f3;
      border: 1px solid #ffb3c1;
      color: #c0392b;
      border-radius: 0.75rem;
      padding: 0.875rem 1rem;
      font-size: 0.875rem;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      text-align: left;
    }

    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      font-weight: 700;
      font-size: 1rem;
      padding: 1rem;
      border-radius: 0.875rem;
      border: none;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: 0 6px 20px rgba(115,26,68,0.3);
      font-family: 'DM Sans', sans-serif;
      margin-top: 0.5rem;
    }
    .btn-login:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      color: #aaa;
      font-size: 0.875rem;
      margin-top: 1.5rem;
      text-decoration: none;
      transition: color 0.2s;
    }
    .back-link:hover { color: var(--primary); }

    .hint {
      margin-top: 1.5rem;
      padding: 0.875rem;
      background: var(--bg-pink);
      border-radius: 0.75rem;
      font-size: 0.8rem;
      color: #777;
    }
    .hint strong { color: var(--primary); }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-logo">🐝</div>
      <h1 class="login-title">Abbie BEE</h1>
      <p class="login-subtitle">Panel de Administración · Acceso privado</p>

      <?php if ($error): ?>
        <div class="error-msg">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label for="username">Usuario</label>
          <div class="input-wrap">
            <i class="bi bi-person-fill"></i>
            <input type="text" id="username" name="username" placeholder="Ingresa tu usuario"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username"/>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="input-wrap">
            <i class="bi bi-lock-fill"></i>
            <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña"
                   required autocomplete="current-password"/>
            <button type="button" class="toggle-pass" onclick="togglePass()">
              <i class="bi bi-eye" id="passIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right"></i>
          Iniciar sesión
        </button>
      </form>

      <a href="index.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Volver a la tienda
      </a>

      <div class="hint">
        <strong>Credenciales por defecto:</strong> usuario <strong>admin</strong> · contraseña <strong>admin123</strong>
      </div>
    </div>
  </div>

  <script>
    function togglePass() {
      const inp  = document.getElementById('password');
      const icon = document.getElementById('passIcon');
      if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }
  </script>
</body>
</html>
