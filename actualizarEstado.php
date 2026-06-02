<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tarea_id = $_POST['tarea_id'] ?? 0;
    $nuevo_estado = $_POST['estado'] ?? '';
    
    // Validar que el estado sea válido
    if ($nuevo_estado == 'pendiente' || $nuevo_estado == 'completada') {
        $stmt = $pdo->prepare("UPDATE tareas SET estado = ? WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$nuevo_estado, $tarea_id, $_SESSION['usuario_id']]);
    }
}

header('Location: dashboard.php');
exit;
?>