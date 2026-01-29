<?php
require __DIR__ . '/../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$correo = trim($_POST['correo']);
$password = $_POST['password'];

if (empty($correo) || empty($password)) {
    header("Location: login.php?error=Complete todos los campos");
    exit;
}

// Buscar usuario
$sql = "
SELECT u.idu, u.nombre, u.correo, u.passwd, u.verificado, r.nombre AS rol
FROM usuarios u
JOIN roles r ON u.idr = r.idr
WHERE u.correo = :correo
LIMIT 1
";

$stmt = $db->prepare($sql);
$stmt->execute(['correo' => $correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: login.php?error=Usuario no encontrado");
    exit;
}

// Verificar correo
if (!$usuario['verificado']) {
    header("Location: login.php?error=Debe verificar su correo");
    exit;
}

// Verificar contraseña
if (!password_verify($password, $usuario['passwd'])) {
    header("Location: login.php?error=Contraseña incorrecta");
    exit;
}

// Crear sesión
$_SESSION['usuario'] = [
    'id'   => $usuario['idu'],
    'nombre' => $usuario['nombre'],
    'correo' => $usuario['correo'],
    'rol' => $usuario['rol']
];

// Redirección según rol
if ($usuario['rol'] === 'Administrador') {
    header("Location: ../admin/dashboard.php");
} else {
    header("Location: ../client/home.php");
}
exit;
