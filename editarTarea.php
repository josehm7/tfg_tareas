<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Verificar que la tarea existe y pertenece al usuario
$stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
$tarea = $stmt->fetch();

if (!$tarea) {
    header('Location: dashboard.php');
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
        $stmt = $pdo->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, fecha_limite = ?, prioridad = ? WHERE id = ? AND usuario_id = ?");
        if ($stmt->execute([$titulo, $descripcion, $fecha_limite, $prioridad, $id, $_SESSION['usuario_id']])) {
            $success = "Tarea actualizada correctamente";
            // Recargar datos
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $_SESSION['usuario_id']]);
            $tarea = $stmt->fetch();
        } else {
            $error = "Error al actualizar la tarea";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">Editar Tarea</div>
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
                        <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($tarea['titulo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($tarea['descripcion']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Fecha límite</label>
                        <input type="date" name="fecha_limite" class="form-control" value="<?php echo $tarea['fecha_limite']; ?>">
                    </div>
                    <div class="mb-3">
                        <label>Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="alta" <?php echo ($tarea['prioridad'] ?? 'media') == 'alta' ? 'selected' : ''; ?>>🔴 Alta</option>
                            <option value="media" <?php echo ($tarea['prioridad'] ?? 'media') == 'media' ? 'selected' : ''; ?>>🟡 Media</option>
                            <option value="baja" <?php echo ($tarea['prioridad'] ?? 'media') == 'baja' ? 'selected' : ''; ?>>🟢 Baja</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Actualizar Tarea</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>