<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Contar tareas por estado
$stmt = $pdo->prepare("SELECT 
    SUM(estado = 'pendiente') as pendientes,
    SUM(estado = 'completada') as completadas,
    COUNT(*) as total
    FROM tareas WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$stats = $stmt->fetch();

$porcentaje = $stats['total'] > 0 ? round(($stats['completadas'] / $stats['total']) * 100) : 0;
?>
<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <h2>📈 Mis Estadísticas</h2>
        <hr>
    </div>
</div>

<div class="row text-center mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Tareas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h3><?php echo $stats['completadas']; ?></h3>
                <p>Completadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h3><?php echo $stats['pendientes']; ?></h3>
                <p>Pendientes</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Progreso</div>
    <div class="card-body">
        <div class="progress mb-3" style="height: 30px;">
            <div class="progress-bar bg-success" style="width: <?php echo $porcentaje; ?>%;">
                <?php echo $porcentaje; ?>% Completado
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>