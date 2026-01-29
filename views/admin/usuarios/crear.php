<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol_id = $_POST['rol_id'] ?? 2;

    // Insertar
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellidos, correo, password, rol_id, verificado) VALUES (:nombre,:apellidos,:correo,:password,:rol_id,0)");
    $stmt->execute([
        'nombre'=>$nombre,
        'apellidos'=>$apellidos,
        'correo'=>$correo,
        'password'=>$password,
        'rol_id'=>$rol_id
    ]);

    header("Location: index.php");
    exit;
}

// Roles
$roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Usuario | Panel Admin</title>
<link rel="stylesheet" href="../assets/css/usuarios.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Agregar Nuevo Usuario</h2>
    <?php if($error) echo "<div class='alert alert-warning'>$error</div>"; ?>
    <form method="POST" class="form-producto">
        <div class="mb-3">
            <label class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Apellidos:</label>
            <input type="text" name="apellidos" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo:</label>
            <input type="email" name="correo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Rol:</label>
            <select name="rol_id" class="form-select">
                <?php foreach($roles as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Agregar Usuario</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
