<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_limite = $_POST['fecha_limite'] ?: null;
    $prioridad = $_POST['prioridad'] ?? 'media';
    
    if (empty($titulo)) {
        $error = "El título es obligatorio";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tareas (usuario_id, titulo, descripcion, fecha_limite,prioridad) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['usuario_id'], $titulo, $descripcion, $fecha_limite, $prioridad])) {
            $success = "Tarea creada correctamente";
            // Limpiar formulario
            $titulo = $descripcion = '';
            $fecha_limite = null;
        } else {
            $error = "Error al crear la tarea";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">Crear Nueva Tarea</div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Título *</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Fecha límite</label>
                        <input type="date" name="fecha_limite" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="alta">🔴 Alta</option>
                            <option value="media">🟡 Media</option>
                            <option value="baja">🟢 Baja</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar Tarea</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>