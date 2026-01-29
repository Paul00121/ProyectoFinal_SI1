<?php
session_start();
require_once '../../../config/database.php';

// Mostrar errores temporales para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Solo admin puede acceder
if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

// Obtener usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) { echo "<div style='padding:20px;'>Usuario no encontrado.</div>"; exit; }

// Obtener roles
$roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $rol_id = $_POST['rol_id'];

    try {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=:nombre, apellidos=:apellidos, correo=:correo, rol_id=:rol_id, password=:password WHERE id=:id");
            $stmt->execute([
                'nombre'=>$nombre,
                'apellidos'=>$apellidos,
                'correo'=>$correo,
                'rol_id'=>$rol_id,
                'password'=>$password,
                'id'=>$id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=:nombre, apellidos=:apellidos, correo=:correo, rol_id=:rol_id WHERE id=:id");
            $stmt->execute([
                'nombre'=>$nombre,
                'apellidos'=>$apellidos,
                'correo'=>$correo,
                'rol_id'=>$rol_id,
                'id'=>$id
            ]);
        }
        // Redirigir al index al guardar
        $_SESSION['success'] = "Usuario actualizado correctamente.";
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $error = "Error al actualizar usuario: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario | Panel Admin</title>
<link rel="stylesheet" href="../assets/css/usuarios.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">

    <h2 class="mb-4">Editar Usuario</h2>

    <?php if(isset($error) && $error): ?>
        <div class="alert alert-warning"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="form-producto">
        <div class="mb-3">
            <label class="form-label">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Apellidos:</label>
            <input type="text" name="apellidos" class="form-control" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Correo:</label>
            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña (dejar vacío si no cambia):</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Rol:</label>
            <select name="rol_id" class="form-select" required>
                <?php foreach($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id']==$usuario['rol_id']?'selected':'' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
