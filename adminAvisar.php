<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = $_POST['usuario_id'] ?? 0;
    $motivo = trim($_POST['motivo']) ?? '';
    
    if (!$usuario_id || empty($motivo)) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO advertencias (usuario_id, admin_id, motivo) VALUES (?, ?, ?)");
    $stmt->execute([$usuario_id, $_SESSION['usuario_id'], $motivo]);
    
    $stmt = $pdo->prepare("UPDATE usuarios SET advertencias_count = advertencias_count + 1 WHERE id = ?");
    $stmt->execute([$usuario_id]);
    
    $asunto = "⚠️ AVISO IMPORTANTE - TaskFlow";
    $cuerpo = "
        <h1>Has recibido una advertencia</h1>
        <p>Hola {$usuario['nombre']},</p>
        <p>Has recibido una advertencia por parte del administrador.</p>
        <p><strong>Motivo:</strong> {$motivo}</p>
        <p>Por favor, revisa tus tareas y asegúrate de cumplir con las normas de la comunidad.</p>
        <hr>
        <p>TaskFlow - Administración</p>
    ";
    
    enviarEmail($usuario['email'], $usuario['nombre'], $asunto, $cuerpo);
    registrarLog($pdo, $_SESSION['usuario_id'], "Envió advertencia al usuario ID: $usuario_id - Motivo: $motivo");
    
    echo json_encode(['success' => true]);
}
?>