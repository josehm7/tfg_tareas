<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Verificar que la tarea existe y pertenece al usuario
$stmt = $pdo->prepare("SELECT id FROM tareas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
if ($stmt->fetch()) {
    $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
}

header('Location: dashboard.php');
exit;
?>