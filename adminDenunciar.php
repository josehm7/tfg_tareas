<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tarea_id = $_POST['tarea_id'] ?? 0;
    $motivo = trim($_POST['motivo']) ?? '';
    
    if (!$tarea_id || empty($motivo)) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO denuncias (tarea_id, usuario_denunciante, motivo) VALUES (?, ?, ?)");
    $stmt->execute([$tarea_id, $_SESSION['usuario_id'], $motivo]);
    
    registrarLog($pdo, $_SESSION['usuario_id'], "Denunció la tarea ID: $tarea_id - Motivo: $motivo");
    
    echo json_encode(['success' => true]);
}
?>