<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {

    // Opcional: validar si hay productos asignados a esta categoría
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = :id");
    $stmt->execute(['id' => $id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "No se puede eliminar esta categoría porque tiene productos asignados.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $_SESSION['success'] = "Categoría eliminada correctamente.";
    }
}

header("Location: index.php");
exit;
