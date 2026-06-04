<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header('Location: dashboard.php');
    exit;
}

$mensaje = '';

if (isset($_GET['eliminar_tarea'])) {
    $tarea_id = $_GET['eliminar_tarea'];
    $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
    $stmt->execute([$tarea_id]);
    $stmt = $pdo->prepare("UPDATE denuncias SET estado = 'eliminada' WHERE tarea_id = ?");
    $stmt->execute([$tarea_id]);
    $mensaje = "Tarea eliminada correctamente";
    registrarLog($pdo, $_SESSION['usuario_id'], "Eliminó tarea denunciada ID: $tarea_id");
}

if (isset($_GET['revisar'])) {
    $denuncia_id = $_GET['revisar'];
    $stmt = $pdo->prepare("UPDATE denuncias SET estado = 'revisada' WHERE id = ?");
    $stmt->execute([$denuncia_id]);
    $mensaje = "Denuncia marcada como revisada";
}

$stmt = $pdo->prepare("SELECT d.*, t.titulo as tarea_titulo, u.nombre as usuario_nombre 
                       FROM denuncias d
                       LEFT JOIN tareas t ON d.tarea_id = t.id
                       LEFT JOIN usuarios u ON d.usuario_denunciante = u.id
                       WHERE d.estado = 'pendiente'
                       ORDER BY d.fecha DESC");
$stmt->execute();
$denuncias = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT u.id, u.nombre, u.email, u.advertencias_count, 
                       (SELECT COUNT(*) FROM tareas WHERE usuario_id = u.id) as total_tareas
                       FROM usuarios u
                       WHERE u.advertencias_count > 0
                       ORDER BY u.advertencias_count DESC");
$stmt->execute();
$usuarios_advertidos = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🚨 Panel de Moderación</h2>
    <a href="dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
</div>

<?php if($mensaje): ?>
    <div class="alert alert-success"><?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5>📋 Denuncias Pendientes (<?php echo count($denuncias); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if(empty($denuncias)): ?>
                    <p class="text-muted">No hay denuncias pendientes</p>
                <?php else: ?>
                    <?php foreach($denuncias as $denuncia): ?>
                        <div class="border-bottom mb-3 pb-3">
                            <p><strong>Tarea:</strong> <?php echo htmlspecialchars($denuncia['tarea_titulo'] ?? 'Eliminada'); ?></p>
                            <p><strong>Denunciante:</strong> <?php echo htmlspecialchars($denuncia['usuario_nombre']); ?></p>
                            <p><strong>Motivo:</strong> <?php echo htmlspecialchars($denuncia['motivo']); ?></p>
                            <p><small>Fecha: <?php echo date('d/m/Y H:i', strtotime($denuncia['fecha'])); ?></small></p>
                            <div class="btn-group">
                                <a href="?eliminar_tarea=<?php echo $denuncia['tarea_id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Eliminar esta tarea?')">🗑️ Eliminar tarea</a>
                                <a href="?revisar=<?php echo $denuncia['id']; ?>" 
                                   class="btn btn-sm btn-success">✓ Marcar revisada</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5>⚠️ Usuarios con Advertencias</h5>
            </div>
            <div class="card-body">
                <?php if(empty($usuarios_advertidos)): ?>
                    <p class="text-muted">No hay usuarios con advertencias</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Usuario</th><th>Advertencias</th><th>Tareas</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios_advertidos as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                    <td><span class="badge bg-danger"><?php echo $usuario['advertencias_count']; ?></span></td>
                                    <td><?php echo $usuario['total_tareas']; ?></td>
                                    <td>
                                        <a href="admin_usuarios.php?eliminar=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Eliminar este usuario y todas sus tareas?')">🗑️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>