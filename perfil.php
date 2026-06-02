<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Obtener datos del usuario (incluyendo fecha_registro si existe)
$stmt = $pdo->prepare("SELECT nombre, email, fecha_registro FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['cambiar_password'])) {
        $actual = $_POST['password_actual'];
        $nueva = $_POST['password_nueva'];
        $confirmar = $_POST['password_confirmar'];
        
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $hash = $stmt->fetch()['password'];
        
        if(password_verify($actual, $hash)) {
            if($nueva == $confirmar && strlen($nueva) >= 6) {
                $nuevo_hash = password_hash($nueva, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt->execute([$nuevo_hash, $_SESSION['usuario_id']]);
                $success = "Contraseña actualizada correctamente";
            } else {
                $error = "Las contraseñas no coinciden o son muy cortas (mínimo 6 caracteres)";
            }
        } else {
            $error = "Contraseña actual incorrecta";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">👤 Mi Perfil</div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <h5>Información de cuenta</h5>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'] ?? 'now')); ?></p>
                
                <hr>
                <h5>Cambiar contraseña</h5>
                <form method="POST">
                    <input type="hidden" name="cambiar_password" value="1">
                    <div class="mb-3">
                        <label>Contraseña actual</label>
                        <input type="password" name="password_actual" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password_nueva" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmar" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                    <a href="dashboard.php" class="btn btn-secondary">Volver</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>