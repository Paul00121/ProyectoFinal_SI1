<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener imagen del producto
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Eliminar imagen física
    if ($producto && $producto['imagen']) {
        $rutaImagen = "../assets/img/productos/" . $producto['imagen'];
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }
    }

    // Eliminar producto (stock se elimina automáticamente por CASCADE)
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
}

header("Location: index.php");
exit;
