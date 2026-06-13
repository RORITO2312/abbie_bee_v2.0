# Abbie BEE — Sistema Web PHP + MySQL (XAMPP)

## Estructura de archivos

```
abbie_bee/
├── config.php          ← Configuración de base de datos
├── db.sql              ← Script SQL para crear la base de datos
├── index.php           ← Tienda principal (pública)
├── login.php           ← Inicio de sesión administrador
├── logout.php          ← Cerrar sesión
├── admin.php           ← Panel de administración
├── api/
│   ├── productos.php   ← API REST productos (JSON)
│   └── resenas.php     ← API REST reseñas (JSON)
└── uploads/            ← Imágenes de productos (se crea automáticamente)
```

## Instalación paso a paso

### 1. Instalar XAMPP
Descarga e instala XAMPP desde: https://www.apachefriends.org/

### 2. Copiar los archivos
Copia la carpeta `abbie_bee/` completa dentro de:
```
C:\xampp\htdocs\abbie_bee\    (Windows)
/Applications/XAMPP/htdocs/abbie_bee/   (Mac)
```

### 3. Crear la base de datos
- Abre XAMPP → Iniciar **Apache** y **MySQL**
- Ve a: http://localhost/phpmyadmin
- Haz clic en **"Nueva"** o **"Importar"**
- Importa el archivo `db.sql` o copia su contenido en la pestaña SQL y ejecuta

### 4. Verificar configuración
Abre `config.php` y verifica:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // Usuario por defecto XAMPP
define('DB_PASS', '');        // Contraseña vacía por defecto
define('DB_NAME', 'abbie_bee');
```

### 5. Acceder al sistema

| URL | Descripción |
|-----|-------------|
| http://localhost/abbie_bee/ | Tienda principal |
| http://localhost/abbie_bee/login.php | Login admin |
| http://localhost/abbie_bee/admin.php | Panel admin |

### 6. Credenciales de administrador
- **Usuario:** `admin`
- **Contraseña:** `admin123`

---

## Panel de Administración

El panel admin incluye:

### 📊 Dashboard
- Estadísticas generales: productos, ventas, pedidos pendientes, promociones activas
- Ventas y pedidos recientes

### 📦 Productos
- Agregar nuevos productos con imagen, precio, categoría, material, color, stock
- Editar productos existentes
- Eliminar productos
- Marcar como destacados
- Activar/desactivar visibilidad

### 🛒 Ventas
- Historial de ventas
- Estadísticas: total ingresos, transacciones, unidades vendidas
- Exportar a CSV

### 📋 Pedidos
- Gestión de pedidos con estados: Pendiente, Procesando, Enviado, Entregado, Cancelado
- Cambio de estado directamente desde la tabla

### 🏷️ Promociones
- Crear códigos de descuento (porcentaje o monto fijo)
- Activar/desactivar promociones
- Configurar límite de usos y fecha de expiración

---

## Paleta de colores
- `#A6215F` — Fucsia intenso (primario)
- `#731A44` — Granate (oscuro)
- `#F277B0` — Rosa vibrante
- `#F2A7CA` — Rosa suave
- `#F2DFE8` — Rosa muy claro
- `#FFFFFF` — Blanco

## Tipografías
- **Títulos:** Playfair Display + Kavoon
- **Cuerpo:** DM Sans

---

## Cambiar contraseña del admin

En phpMyAdmin, ejecuta:
```sql
USE abbie_bee;
UPDATE admin_users 
SET password_hash = '$2y$10$...' -- hash de tu nueva contraseña
WHERE username = 'admin';
```

Para generar un hash en PHP:
```php
echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT);
```

---

Desarrollado para **Abbie BEE Joyería** · Universidad Andina del Cusco
Marketing Digital · 2026
