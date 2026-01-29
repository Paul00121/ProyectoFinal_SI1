<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

// No permitir eliminar al administrador principal (opcional)
if ($id == 1) { 
    $_SESSION['error'] = "No se puede eliminar el usuario administrador principal.";
    header("Location: index.php");
    exit;
}

// Eliminar usuario
$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id=:id");
$stmt->execute(['id'=>$id]);

$_SESSION['success'] = "Usuario eliminado correctamente.";
header("Location: index.php");
exit;
?>
