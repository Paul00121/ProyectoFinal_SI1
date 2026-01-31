# ProyectoFinal_SI1 – Tienda Deportiva

## Descripción del Proyecto

**Tienda Deportiva** es un sistema web integral diseñado para gestionar operaciones completas de una tienda de productos deportivos. La aplicación proporciona herramientas avanzadas para administrar inventario, clientes, proveedores, ventas y compras, con funcionalidades sofisticadas como:

- **Gestión de Inventario**: Control completo de productos, categorías y stock
- **Generación de Reportes en PDF**: Reportes de ventas, inventario y transacciones
- **Exportación de Datos a Excel**: Descarga de información en formato XLSX
- **Simulación de Pagos**: Integración con PayPal Sandbox para pruebas seguras
- **Gestión de Pedidos y Facturas**: Control total del ciclo de venta
- **Control de Acceso**: Sistema de roles (cliente, proveedor, administrador)

El sistema está desarrollado con **PHP 7.4+**, **PostgreSQL** y utiliza librerías modernas para reportes y procesamiento de pagos.

---

## Levantamiento del Proyecto / Setup

### Requisitos Previos

- **PHP** >= 7.4
- **PostgreSQL** >= 12
- **Composer** >= 2.0
- **Git**
- **XAMPP** o servidor web compatible
- Cuenta de **PayPal Sandbox** (opcional, para pruebas de pago)

### Pasos de Instalación

#### 1. Clonar el Repositorio

```bash
git clone https://github.com/Paul00121/ProyectoFinal_SI1.git
cd tienda_deportiva
```

#### 2. Instalar Dependencias con Composer

```bash
composer install
```

Este comando instalará todas las librerías necesarias en la carpeta `vendor/`.

#### 3. Configurar Variables de Entorno

Crea un archivo `.env` en la raíz del proyecto con la siguiente estructura:

```env
# Base de Datos PostgreSQL
DB_HOST=localhost
DB_PORT=5432
DB_NAME=tienda_deportiva
DB_USER=postgres
DB_PASSWORD=tu_contraseña

# PayPal Sandbox
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=tu_client_id_aqui
PAYPAL_SECRET=tu_secret_aqui

# Configuración de Aplicación
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=true
```

#### 4. Configurar la Base de Datos PostgreSQL

**En Windows (cmd/PowerShell):**

```bash
# Crear base de datos
createdb -U postgres tienda_deportiva

# Importar esquema
psql -U postgres -d tienda_deportiva -f config/database.sql
```

**En Linux/Mac (Terminal):**

```bash
# Crear base de datos
psql -U postgres -c "CREATE DATABASE tienda_deportiva;"

# Importar esquema
psql -U postgres -d tienda_deportiva -f config/database.sql
```

**Alternativa con pgAdmin:**
1. Abrir pgAdmin 4
2. Crear una nueva base de datos `tienda_deportiva`
3. Ejecutar el script SQL desde `config/database.sql`

#### 5. Configurar PayPal Sandbox (Opcional)

1. Acceder a [PayPal Developer Dashboard](https://developer.paypal.com)
2. Iniciar sesión o crear una cuenta
3. Navegar a **Apps & Credentials**
4. Seleccionar **Sandbox** mode
5. Copiar **Client ID** y **Secret** de tu aplicación
6. Guardar credenciales en el archivo `.env`

#### 6. Ejecutar el Proyecto en Local

**Opción A: Con XAMPP**

```bash
# Copiar la carpeta al directorio htdocs
cp -r tienda_deportiva C:\xampp\htdocs\

# Iniciar Apache y PostgreSQL desde XAMPP Control Panel
# Acceder en navegador: http://localhost/tienda_deportiva
```

**Opción B: Con PHP Built-in Server**

```bash
# Desde la raíz del proyecto
php -S localhost:8000

# Acceder en navegador: http://localhost:8000
```

**Opción C: Con Docker (Opcional)**

```bash
# Asegurar estar en la raíz del proyecto
docker-compose up -d

# Acceder en navegador: http://localhost:8080

# Detener contenedores
docker-compose down
```

---

## Dependencias Principales

| Dependencia | Versión | Descripción |
|---|---|---|
| **PHP** | >=7.4 | Lenguaje de programación backend |
| **PostgreSQL** | >=12 | Sistema de base de datos relacional |
| **Composer** | >=2.0 | Gestor de dependencias para PHP |
| **Dompdf** | ^2.0 | Generación de reportes y documentos PDF |
| **PhpSpreadsheet** | ^1.25 | Exportación de datos a Excel (XLSX) |
| **PayPal SDK** | ^2.0 | Integración de pagos con PayPal |
| **PDO** | - | Driver de conexión a PostgreSQL |
| **Docker** | - | Containerización (opcional) |

### Instalar Dependencias Específicas

```bash
# Generador de PDF
composer require dompdf/dompdf

# Exportación a Excel
composer require phpoffice/phpspreadsheet

# SDK de PayPal
composer require paypal/checkout-sdk-php
```

---

## Estructura del Proyecto

```
tienda_deportiva/
│
├── config/
│   ├── database.php              # Configuración y conexión a PostgreSQL
│   └── database.sql              # Esquema de base de datos (tablas, índices)
│
├── funciones/
│   ├── enviar_correo.php         # Funciones para envío de correos
│   ├── auth.php                  # Funciones de autenticación y sesiones
│   ├── validaciones.php          # Validaciones de datos de entrada
│   └── helpers.php               # Funciones auxiliares generales
│
├── views/
│   ├── admin/                    # Vistas del panel administrativo
│   │   ├── index_admin.php       # Dashboard del administrador
│   │   ├── usuarios/             # Gestión de usuarios
│   │   ├── productos/            # Gestión de productos
│   │   ├── pedidos/              # Gestión de pedidos
│   │   ├── categoria/            # Gestión de categorías
│   │   ├── reportes/             # Generación de reportes
│   │   └── assets/               # CSS y JS del admin
│   │
│   ├── cliente/                  # Vistas del cliente
│   │   ├── index_cliente.php     # Página principal de cliente
│   │   ├── header.php            # Encabezado común
│   │   ├── footer.php            # Pie de página común
│   │   ├── crear_pedido/         # Crear nuevos pedidos
│   │   │   └── procesar_pedido.php
│   │   ├── mis_pedidos/          # Historial de pedidos
│   │   ├── producto/             # Detalle de productos
│   │   └── assets/               # Estilos y scripts
│   │
│   ├── proveedor/                # Vistas del proveedor
│   │   ├── index_proveedor.php   # Dashboard del proveedor
│   │   └── ordenes_compra/       # Gestión de órdenes
│   │
│   ├── auth/                     # Vistas de autenticación
│   │   ├── login.php             # Formulario de inicio de sesión
│   │   ├── registrar.php         # Formulario de registro
│   │   ├── recuperar.php         # Recuperación de contraseña
│   │   ├── cambiar_password.php  # Cambio de contraseña
│   │   ├── verificar_codigo.php  # Verificación de código
│   │   ├── verificar_email.php   # Confirmación de email
│   │   ├── verificar_login.php   # Validación de login
│   │   └── logout.php            # Cierre de sesión
│   │
│   └── partials/                 # Componentes reutilizables
│       ├── chatbot.php           # Chatbot de soporte
│       └── navbar.php            # Barra de navegación
│
├── public/
│   ├── index.php                 # Punto de entrada alternativo
│   ├── css/                      # Hojas de estilos CSS
│   │   ├── login.css
│   │   ├── registrar.css
│   │   ├── cambiar_password.css
│   │   ├── recuperar.css
│   │   ├── verificar_codigo.css
│   │   └── chatbot.css
│   ├── js/                       # Scripts JavaScript
│   │   ├── login.js
│   │   ├── registrar.js
│   │   ├── recuperar.js
│   │   ├── verificar_codigo.js
│   │   └── chatbot.js
│   └── img/                      # Imágenes y recursos gráficos
│
├── paypal/
│   ├── procesar_pago.php         # Procesar pagos con PayPal
│   ├── verificar_pago.php        # Verificar estado de pago
│   └── cancelado.php             # Página de pago cancelado
│
├── vendor/                       # Dependencias de Composer (NO editar)
│   ├── autoload.php
│   ├── composer/
│   ├── dompdf/                   # Librería para generar PDF
│   ├── phpmailer/                # Librería para envío de correos
│   ├── phpoffice/                # Librería para Excel
│   ├── paypal/                   # SDK de PayPal
│   └── symfony/                  # Componentes de Symfony
│
├── reporte_pdf.php               # Generación de reportes en PDF
├── export_excel.php              # Exportación de datos a Excel
├── index.php                     # Punto de entrada principal
├── test_db.php                   # Script para probar conexión a BD
├── .env                          # Variables de entorno (NO versionar)
├── .env.example                  # Ejemplo de archivo .env
├── .gitignore                    # Archivos ignorados por Git
├── composer.json                 # Configuración de Composer
├── composer.lock                 # Lock file de Composer
├── Dockerfile                    # Configuración de Docker
├── docker-compose.yml            # Orquestación de servicios Docker
└── README.md                     # Este archivo
```

---

## Funcionalidades Principales

### 🛒 Gestión de Productos
- Registrar, editar y eliminar productos deportivos
- Organizar productos por categorías
- Aplicar descuentos y promociones
- Control de inventario en tiempo real
- Búsqueda y filtrado de productos

### 👥 Gestión de Clientes
- Registro e inicio de sesión seguro
- Perfil de usuario personalizable
- Historial completo de compras
- Seguimiento de pedidos en tiempo real
- Recuperación de contraseña por correo

### 📦 Gestión de Pedidos y Ventas
- Crear pedidos desde catálogo de productos
- Actualizar estado de pedidos (pendiente, enviado, entregado)
- Generación automática de facturas
- Carrito de compras funcional
- Confirmación de pedidos por correo

### 💳 Pagos Seguros con PayPal
- Integración con PayPal Sandbox para pruebas
- Simulación completa de transacciones
- Confirmación instantánea de pagos
- Notificación de pagos exitosos o cancelados

### 📊 Reportes y Análisis
- Generación de reportes en formato PDF
- Exportación de datos a Excel (.xlsx)
- Reportes de ventas por período
- Análisis de inventario
- Estadísticas de clientes

### 🤝 Gestión de Proveedores
- Registro y gestión de proveedores
- Creación de órdenes de compra
- Seguimiento de suministros
- Historial de compras a proveedores

### 🔐 Panel Administrativo
- Dashboard con estadísticas generales
- Gestión completa de usuarios
- Control de roles y permisos
- Auditoría de operaciones
- Configuración del sistema

### 💬 Soporte al Cliente
- Chatbot integrado para consultas frecuentes
- Disponibilidad 24/7 de información
- Respuestas automáticas a preguntas comunes

---

## Guía de Uso Rápido

### Iniciar Sesión

1. Acceder a `http://localhost/tienda_deportiva`
2. Hacer clic en **"Iniciar Sesión"**
3. Usar credenciales de usuario registrado
4. Para pruebas: usuario `admin@example.com` con contraseña `admin123`

### Crear un Nuevo Usuario

1. En la página de login, hacer clic en **"Registrarse"**
2. Completar formulario con datos válidos
3. Verificar email recibido
4. Confirmar correo electrónico
5. ¡Listo para usar la aplicación!

### Generar un Reporte PDF

```php
require_once 'vendor/autoload.php';
require_once 'reporte_pdf.php';

generarReportePedido($pedido_id);
```

### Exportar Datos a Excel

```php
require_once 'vendor/autoload.php';
require_once 'export_excel.php';

exportarVentas($fecha_inicio, $fecha_fin);
```

### Procesar un Pago con PayPal

1. Ir a **"Mis Pedidos"**
2. Seleccionar un pedido pendiente de pago
3. Hacer clic en **"Proceder al Pago"**
4. Usar credenciales de sandbox de PayPal
5. Confirmar transacción

---

## Solución de Problemas

### Error: "Base de datos no encontrada"
```bash
# Verificar que PostgreSQL esté ejecutándose
# En Windows, ejecutar en terminal:
pg_isready -h localhost -U postgres

# Si falta la BD, crearla:
createdb -U postgres tienda_deportiva
```

### Error: "Clase no encontrada" (PayPal SDK)
```bash
# Reinstalar dependencias
composer dump-autoload -o
composer install
```

### Error: "Permiso denegado" en carpeta
```bash
# En Linux/Mac:
chmod -R 755 tienda_deportiva

# En Windows, verificar propiedades de carpeta
```

### Error: "Conexión rechazada" a PostgreSQL
- Verificar que PostgreSQL esté corriendo
- Confirmar credenciales en `.env`
- Revisar puerto (por defecto 5432)

### Error 404 en rutas
- Si usa XAMPP, acceder como: `http://localhost/tienda_deportiva/`
- Si usa PHP server, verificar estructura de carpetas

---

## Mejores Prácticas de Desarrollo

### Seguridad
- Nunca versionar el archivo `.env` con credenciales reales
- Usar prepared statements en todas las consultas SQL
- Validar y sanitizar entrada de usuarios
- Implementar CSRF tokens en formularios

### Base de Datos
- Realizar backups regularmente
- Usar transacciones para operaciones críticas
- Mantener índices en columnas frecuentemente consultadas

### Código
- Seguir estándar PSR-12 para PHP
- Comentar funciones complejas
- Usar nombres descriptivos para variables
- Separar lógica de vistas

---

## Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Hacer fork del repositorio
2. Crear una rama para tu característica (`git checkout -b feature/MiCaracteristica`)
3. Commit de cambios (`git commit -m 'Agregar MiCaracteristica'`)
4. Push a la rama (`git push origin feature/MiCaracteristica`)
5. Abrir un Pull Request

---

## Autor

**Desarrollador**: [Paul, Diego, Deymar]  
**Email**: tu_email@ejemplo.com  
**GitHub**: [@tu_usuario](https://github.com/Paul00121)  

---

## Licencia

Este proyecto está licenciado bajo la **Licencia MIT**. 

```
MIT License

Copyright (c) 2026 [Paul, Diego, Deymar]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## Recursos Adicionales

- [Documentación de PHP](https://www.php.net/docs.php)
- [Documentación de PostgreSQL](https://www.postgresql.org/docs/)
- [PayPal Developer](https://developer.paypal.com)
- [Dompdf Documentation](https://github.com/dompdf/dompdf)
- [PhpSpreadsheet Documentation](https://phpspreadsheet.readthedocs.io/)

---

**Última actualización**: 30 de enero de 2026  
**Versión**: 1.0.0
