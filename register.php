<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($password != $confirm) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "El email ya está registrado";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // NUEVO: asignar rol 'usuario' por defecto
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')");
            if ($stmt->execute([$nombre, $email, $hashed])) {
                $success = "Registro exitoso. <a href='login.php'>Inicia sesión aquí</a>";
                
                $cuerpo = "<h1>Bienvenido a TaskFlow</h1>
                           <p>Hola $nombre,</p>
                           <p>Tu cuenta ha sido creada exitosamente con rol de USUARIO.</p>
                           <a href='http://localhost/tfg_tareas/login.php'>Iniciar sesión</a>";
                enviarEmail($email, $nombre, "Bienvenido a TaskFlow", $cuerpo);
            } else {
                $error = "Error al registrar";
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">Registro de Usuario</div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                    <a href="login.php" class="btn btn-link">¿Ya tienes cuenta? Inicia sesión</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>