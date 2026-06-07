<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Si es admin, ve todas las tareas con filtro de búsqueda
if ($_SESSION['usuario_rol'] == 'admin') {
    $busqueda = $_GET['buscar'] ?? '';
    
    if (!empty($busqueda)) {
        $stmt = $pdo->prepare("SELECT tareas.*, usuarios.nombre as usuario_nombre 
                               FROM tareas 
                               LEFT JOIN usuarios ON tareas.usuario_id = usuarios.id 
                               WHERE tareas.titulo LIKE :busqueda OR usuarios.nombre LIKE :busqueda
                               ORDER BY CASE prioridad 
                                   WHEN 'alta' THEN 1 
                                   WHEN 'media' THEN 2 
                                   WHEN 'baja' THEN 3 
                               END, fecha_limite ASC, fecha_creacion DESC");
        $stmt->execute(['busqueda' => "%$busqueda%"]);
    } else {
        $stmt = $pdo->prepare("SELECT tareas.*, usuarios.nombre as usuario_nombre 
                               FROM tareas 
                               LEFT JOIN usuarios ON tareas.usuario_id = usuarios.id 
                               ORDER BY CASE prioridad 
                                   WHEN 'alta' THEN 1 
                                   WHEN 'media' THEN 2 
                                   WHEN 'baja' THEN 3 
                               END, fecha_limite ASC, fecha_creacion DESC");
        $stmt->execute();
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM tareas WHERE usuario_id = ? 
                           ORDER BY CASE prioridad 
                               WHEN 'alta' THEN 1 
                               WHEN 'media' THEN 2 
                               WHEN 'baja' THEN 3 
                           END, fecha_limite ASC, fecha_creacion DESC");
    $stmt->execute([$_SESSION['usuario_id']]);
}
$tareas = $stmt->fetchAll();

// Verificar tareas próximas (solo para usuarios normales)
$proximas = 0;
if ($_SESSION['usuario_rol'] != 'admin') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as proximas FROM tareas 
        WHERE usuario_id = ? AND estado = 'pendiente' 
        AND fecha_limite IS NOT NULL
        AND fecha_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
    $stmt->execute([$_SESSION['usuario_id']]);
    $proximas = $stmt->fetch()['proximas'];
}
?>

<?php include 'includes/header.php'; ?>

<?php if($proximas > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>⏰ ¡Atención!</strong> Tienes <?php echo $proximas; ?> tarea(s) que vencen en los próximos 3 días.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Tareas</h2>
    <div>
        <a href="crearTarea.php" class="btn btn-success">➕ Nueva Tarea</a>
        <a href="exportarPDF.php" class="btn btn-secondary">📄 Exportar PDF</a>
    </div>
</div>

<!-- Filtros y buscador -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="filtrarTareas('todas')">Todas</button>
            <button type="button" class="btn btn-outline-success" onclick="filtrarTareas('pendiente')">Pendientes</button>
            <button type="button" class="btn btn-outline-secondary" onclick="filtrarTareas('completadas')">Completadas</button>
        </div>
    </div>
    <div class="col-md-8">
        <form method="GET" action="" class="d-flex gap-2">
            <input type="text" name="buscar" id="buscador" class="form-control" 
                   placeholder="🔍 Buscar por título o nombre de usuario..." 
                   value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
            <?php if(!empty($_GET['buscar'])): ?>
                <a href="dashboard.php" class="btn btn-secondary">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if(!empty($_GET['buscar'])): ?>
    <div class="alert alert-info mb-3">
        Resultados para: <strong><?php echo htmlspecialchars($_GET['buscar']); ?></strong>
    </div>
<?php endif; ?>

<?php if(empty($tareas)): ?>
    <div class="alert alert-info">No hay tareas. ¡Crea tu primera tarea!</div>
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
                        
                        <?php if($_SESSION['usuario_rol'] == 'admin' && isset($tarea['usuario_nombre'])): ?>
                            <span class="badge bg-dark mb-2">👤 <?php echo htmlspecialchars($tarea['usuario_nombre']); ?></span>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title <?php echo $tarea['estado'] == 'completada' ? 'text-decoration-line-through' : ''; ?>">
                                <?php echo htmlspecialchars($tarea['titulo']); ?>
                            </h5>
                            <div>
                                <a href="editarTarea.php?id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                                <a href="eliminarTarea.php?id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminar()">🗑️</a>
                                <?php if($_SESSION['usuario_rol'] == 'admin'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="denunciarTarea(<?php echo $tarea['id']; ?>, '<?php echo htmlspecialchars($tarea['titulo']); ?>')">
                                        🚨
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="advertirUsuario(<?php echo $tarea['usuario_id']; ?>, '<?php echo htmlspecialchars($tarea['usuario_nombre'] ?? 'Usuario'); ?>')">
                                        ⚠️
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                📅 Límite: <?php echo $tarea['fecha_limite'] ? date('d/m/Y', strtotime($tarea['fecha_limite'])) : 'Sin fecha'; ?><br>
                                📅 Creada: <?php echo date('d/m/Y', strtotime($tarea['fecha_creacion'])); ?>
                            </small>
                        </p>
                        <form method="POST" action="actualizarEstado.php" class="d-inline">
                            <input type="hidden" name="tarea_id" value="<?php echo $tarea['id']; ?>">
                            <input type="hidden" name="estado" value="<?php echo $tarea['estado'] == 'pendiente' ? 'completada' : 'pendiente'; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $tarea['estado'] == 'pendiente' ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $tarea['estado'] == 'pendiente' ? '✅ Marcar completada' : '↩️ Reabrir'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function denunciarTarea(tareaId, titulo) {
    let motivo = prompt("Motivo de la denuncia para la tarea: " + titulo);
    if (motivo && motivo.trim() !== "") {
        fetch('adminDenunciar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'tarea_id=' + tareaId + '&motivo=' + encodeURIComponent(motivo)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("✅ Tarea denunciada correctamente");
                location.reload();
            } else {
                alert("❌ Error: " + data.error);
            }
        });
    }
}

function advertirUsuario(usuarioId, nombre) {
    let motivo = prompt("Motivo de la advertencia para el usuario: " + nombre);
    if (motivo && motivo.trim() !== "") {
        fetch('adminAvisar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'usuario_id=' + usuarioId + '&motivo=' + encodeURIComponent(motivo)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("✅ Advertencia enviada al usuario");
            } else {
                alert("❌ Error: " + data.error);
            }
        });
    }
}

function filtrarTareas(estado) {
    const tareas = document.querySelectorAll('.col-md-6');
    tareas.forEach(tarea => {
        const card = tarea.querySelector('.card');
        const esCompletada = card.classList.contains('bg-light');
        
        if (estado === 'todas') {
            tarea.style.display = 'block';
        } else if (estado === 'pendiente' && esCompletada) {
            tarea.style.display = 'none';
        } else if (estado === 'completadas' && !esCompletada) {
            tarea.style.display = 'none';
        } else {
            tarea.style.display = 'block';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>