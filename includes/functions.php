<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Función para enviar emails
function enviarEmail($destinatario, $nombre, $asunto, $cuerpo) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;
        
        $mail->setFrom($_ENV['SMTP_USER'] ?? 'noreply@taskflow.com', 'TaskFlow');
        $mail->addAddress($destinatario, $nombre);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        $mail->AltBody = strip_tags($cuerpo);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando email: " . $mail->ErrorInfo);
        return false;
    }
}

// Función para registrar logs de actividad
function registrarLog($pdo, $usuario_id, $accion) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, accion) VALUES (?, ?)");
        $stmt->execute([$usuario_id, $accion]);
    } catch (PDOException $e) {
        // Si la tabla logs no existe, no hacer nada
        error_log("Error al registrar log: " . $e->getMessage());
    }
}
?>