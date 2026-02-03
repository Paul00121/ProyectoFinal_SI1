CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (nombre) VALUES
('Administrador'),
('Cliente');

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    verificado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);
select * from usuarios ;
-- Insertar usuario administrador de prueba
INSERT INTO usuarios (nombre, apellidos, correo, password, rol_id, verificado, created_at, updated_at) VALUES 
( 'Manuel', 'Quispe', 'paulquispechoque2018@gmail.com', '$2y$12$jSXDV2utCVxJJRX9fS8pqu9C8bKPGznkyGdENFcS5yCeoh0t1FNh.', 1, TRUE, NOW(), NOW() ); 


CREATE TABLE verificacion_email (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expiracion TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE recuperacion_password (
    id SERIAL PRIMARY KEY,
    correo VARCHAR(100) NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    token VARCHAR(64),
    expiracion TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);
INSERT INTO categorias (nombre, descripcion) VALUES
('Calzado deportivo', 'Zapatillas y calzado para deporte'),
('Ropa deportiva', 'Ropa para entrenamiento y competencia'),
('Accesorios', 'Accesorios deportivos');

CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria_id INT NOT NULL,
    imagen VARCHAR(255),
    precio DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE stock (
    id SERIAL PRIMARY KEY,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);


CREATE TABLE estados (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

INSERT INTO estados (nombre) VALUES
('Pendiente'),
('Pagado'),
('Cancelado');

CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    estado_id INT DEFAULT 1,
    total DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (estado_id) REFERENCES estados(id)
);

ALTER TABLE pedidos
ADD COLUMN direccion VARCHAR(255),
ADD COLUMN telefono VARCHAR(20);
select * from pedidos;


CREATE TABLE detalle_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);


CREATE TABLE pagos (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    estado_pago VARCHAR(50) NOT NULL,
    referencia VARCHAR(255),
    monto DECIMAL(10,2) NOT NULL,
    respuesta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);


ALTER TABLE pedidos
DROP COLUMN metodo_pago;



