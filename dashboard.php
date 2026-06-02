<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tareas WHERE usuario_id = ? ORDER BY fecha_limite ASC, fecha_creacion DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$tareas = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<?php
// Verificar tareas próximas (próximos 3 días)
$stmt = $pdo->prepare("SELECT COUNT(*) as proximas FROM tareas 
    WHERE usuario_id = ? AND estado = 'pendiente' 
    AND fecha_limite IS NOT NULL
    AND fecha_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
$stmt->execute([$_SESSION['usuario_id']]);
$proximas = $stmt->fetch()['proximas'];

if($proximas > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>⏰ ¡Atención!</strong> Tienes <?php echo $proximas; ?> tarea(s) que vencen en los próximos 3 días.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Tareas</h2>
    <a href="crearTarea.php" class="btn btn-success">➕ Nueva Tarea</a>
    <a href="estadisticas.php" class="btn btn-info">📊 Estadísticas</a>
</div>

<?php if(empty($tareas)): ?>
    <div class="alert alert-info">No tienes tareas. ¡Crea tu primera tarea!</div>
<?php else: ?>
    <div class="row">
        <?php foreach($tareas as $tarea): ?>
            <div class="col-md-6 mb-3">
                <div class="card <?php echo $tarea['estado'] == 'completada' ? 'bg-light' : ''; ?>">
                    <div class="card-body">
                        <?php 
                        $prioridad_clase = match($tarea['prioridad'] ?? 'media') {
                            'alta' => 'bg-danger',
                            'media' => 'bg-warning text-dark',
                            'baja' => 'bg-success',
                        };
                        ?>
                        <span class="badge <?php echo $prioridad_clase; ?> mb-2"><?php echo ucfirst($tarea['prioridad'] ?? 'Media'); ?></span>
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title <?php echo $tarea['estado'] == 'completada' ? 'text-decoration-line-through' : ''; ?>">
                                <?php echo htmlspecialchars($tarea['titulo']); ?>
                            </h5>
                            <div>
                                <a href="editarTarea.php?id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                                <a href="eliminarTarea.php?id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminar()">🗑️</a>
                            </div>
                        </div>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                📅 Límite: <?php echo $tarea['fecha_limite'] ? date('d/m/Y', strtotime($tarea['fecha_limite'])) : 'Sin fecha'; ?>
                            </small>
                        </p>
                        <form method="POST" action="actualizarEstado.php" class="d-inline">
                            <input type="hidden" name="tarea_id" value="<?php echo $tarea['id']; ?>">
                            <input type="hidden" name="estado" value="<?php echo $tarea['estado'] == 'pendiente' ? 'completada' : 'pendiente'; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $tarea['estado'] == 'pendiente' ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $tarea['estado'] == 'pendiente' ? '✅ Marcar completada' : '↩️ Pendiente'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>