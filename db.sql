-- =====================================================
-- ABBIE BEE — Base de datos para XAMPP/MySQL
-- Ejecutar en phpMyAdmin o MySQL CLI
-- =====================================================

CREATE DATABASE IF NOT EXISTS abbie_bee CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE abbie_bee;

-- Tabla de administradores
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(150) DEFAULT 'Administrador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuario admin por defecto: usuario=admin, contraseña=admin123
INSERT INTO admin_users (username, password_hash, nombre) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria ENUM('Anillos','Collares','Pulseras','Aretes','Accesorios') NOT NULL DEFAULT 'Accesorios',
    material ENUM('Acero Inoxidable','Enchapado Oro Rosa 18K','Enchapado Rodio','Plata') NOT NULL DEFAULT 'Acero Inoxidable',
    color VARCHAR(80) DEFAULT '',
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(500) DEFAULT '',
    destacado TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Productos de muestra
INSERT INTO productos (nombre, descripcion, precio, categoria, material, color, stock, destacado) VALUES
('Anillo Estrella Rosa', 'Anillo delicado con estrella y cristal rosado. Edición limitada.', 45.00, 'Anillos', 'Enchapado Oro Rosa 18K', 'Dorado Rosa', 15, 1),
('Collar Corazón Eterno', 'Collar con dije de corazón, cadena fina de 45cm. Resistente al agua.', 65.00, 'Collares', 'Enchapado Oro Rosa 18K', 'Dorado Rosa', 8, 1),
('Pulsera Bee Gold', 'Pulsera ajustable con charm de abeja, símbolo de la marca.', 38.00, 'Pulseras', 'Enchapado Oro Rosa 18K', 'Dorado', 20, 1),
('Aretes Luna Plateada', 'Aretes colgantes con luna creciente. Hipoalergénicos.', 29.00, 'Aretes', 'Enchapado Rodio', 'Plateado', 25, 0),
('Anillo Infinito', 'Anillo símbolo infinito en acero inoxidable. Para uso diario.', 22.00, 'Anillos', 'Acero Inoxidable', 'Plateado', 30, 0),
('Collar Perla Moderna', 'Collar con perla sintética y baño de oro rosa. Elegante y versátil.', 75.00, 'Collares', 'Enchapado Oro Rosa 18K', 'Blanco Perla', 10, 1);

-- Tabla de ventas
CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT,
    producto_nombre VARCHAR(200) NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    cliente_nombre VARCHAR(200) DEFAULT 'Cliente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

-- Ventas de muestra
INSERT INTO ventas (producto_id, producto_nombre, cantidad, precio_unitario, total, cliente_nombre) VALUES
(1, 'Anillo Estrella Rosa', 2, 45.00, 90.00, 'María González'),
(2, 'Collar Corazón Eterno', 1, 65.00, 65.00, 'Lucía Quispe'),
(3, 'Pulsera Bee Gold', 3, 38.00, 114.00, 'Ana Torres'),
(4, 'Aretes Luna Plateada', 1, 29.00, 29.00, 'Sofía Mamani');

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    cliente_nombre VARCHAR(200) NOT NULL,
    cliente_email VARCHAR(200) DEFAULT '',
    cliente_telefono VARCHAR(20) DEFAULT '',
    cliente_direccion TEXT DEFAULT '',
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('Pendiente','Procesando','Enviado','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
    metodo_pago VARCHAR(80) DEFAULT 'Transferencia',
    notas TEXT DEFAULT '',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla detalle de pedidos
CREATE TABLE IF NOT EXISTS pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT,
    producto_nombre VARCHAR(200) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

-- Pedidos de muestra
INSERT INTO pedidos (codigo, cliente_nombre, cliente_email, total, estado, metodo_pago) VALUES
('PED-001', 'María González', 'maria@email.com', 155.00, 'Entregado', 'Yape'),
('PED-002', 'Lucía Quispe', 'lucia@email.com', 65.00, 'Enviado', 'Transferencia'),
('PED-003', 'Ana Torres', 'ana@email.com', 114.00, 'Procesando', 'Tarjeta'),
('PED-004', 'Sofía Mamani', 'sofia@email.com', 29.00, 'Pendiente', 'Plin');

-- Tabla de promociones
CREATE TABLE IF NOT EXISTS promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descuento VARCHAR(20) NOT NULL,
    tipo ENUM('porcentaje','monto') DEFAULT 'porcentaje',
    valor DECIMAL(10,2) DEFAULT 0,
    descripcion VARCHAR(200) DEFAULT '',
    estado ENUM('Activo','Inactivo') DEFAULT 'Activo',
    usos INT DEFAULT 0,
    limite_usos INT DEFAULT NULL,
    fecha_expiracion DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Promociones de muestra
INSERT INTO promociones (codigo, descuento, tipo, valor, descripcion, estado) VALUES
('ABBIE10', '10%', 'porcentaje', 10.00, 'Descuento bienvenida para nuevos clientes', 'Activo'),
('ABBIE20', '20%', 'porcentaje', 20.00, 'Descuento San Valentín', 'Inactivo'),
('ENVIOGRATIS', 'Envío gratis', 'monto', 15.00, 'Envío gratis en compras mayores a S/80', 'Activo');

-- Tabla de reseñas
CREATE TABLE IF NOT EXISTS resenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    comentario TEXT NOT NULL,
    estrellas TINYINT DEFAULT 5,
    avatar VARCHAR(10) DEFAULT '🐝',
    likes INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reseñas de muestra
INSERT INTO resenas (nombre, comentario, estrellas, avatar) VALUES
('María G.', '¡Las joyas son preciosas! La calidad es increíble y el packaging muy bonito. Definitivamente volvería a comprar.', 5, '🌸'),
('Lucía Q.', 'Me encantó el collar de corazón. Muy delicado y elegante. El envío llegó súper rápido y bien protegido.', 5, '💎'),
('Ana T.', 'Las pulseras son exactamente como en las fotos. Resistentes al agua, ya las usé en la piscina sin problema.', 4, '⭐');
