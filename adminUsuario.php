<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header('Location: dashboard.php');
    exit;
}

$mensaje = '';
$error = '';

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($id != $_SESSION['usuario_id']) { // No eliminar a sí mismo
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = "Usuario eliminado correctamente";
        registrarLog($pdo, $_SESSION['usuario_id'], "Eliminó al usuario ID: $id");
    } else {
        $error = "No puedes eliminar tu propio usuario";
    }
}

// Cambiar rol
if (isset($_GET['cambiar_rol'])) {
    $id = $_GET['cambiar_rol'];
    $nuevo_rol = $_GET['rol'];
    if ($id != $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $stmt->execute([$nuevo_rol, $id]);
        $mensaje = "Rol actualizado correctamente";
        registrarLog($pdo, $_SESSION['usuario_id'], "Cambió el rol del usuario ID: $id a $nuevo_rol");
    } else {
        $error = "No puedes cambiar tu propio rol";
    }
}

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT id, nombre, email, rol, fecha_registro FROM usuarios ORDER BY id");
$usuarios = $stmt->fetchAll();

// Contar tareas por usuario
$stmt = $pdo->query("SELECT usuario_id, COUNT(*) as total FROM tareas GROUP BY usuario_id");
$tareas_count = [];
while ($row = $stmt->fetch()) {
    $tareas_count[$row['usuario_id']] = $row['total'];
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>👥 Administración de Usuarios</h2>
    <a href="dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
</div>

<?php if($mensaje): ?>
    <div class="alert alert-success"><?php echo $mensaje; ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-dark text-white">Lista de Usuarios</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Tareas</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td>
                            <?php if($usuario['rol'] == 'admin'): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Usuario</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $tareas_count[$usuario['id']] ?? 0; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                        <td>
                            <?php if($usuario['id'] != $_SESSION['usuario_id']): ?>
                                <?php if($usuario['rol'] == 'usuario'): ?>
                                    <a href="?cambiar_rol=<?php echo $usuario['id']; ?>&rol=admin" class="btn btn-sm btn-warning">👑 Hacer Admin</a>
                                <?php else: ?>
                                    <a href="?cambiar_rol=<?php echo $usuario['id']; ?>&rol=usuario" class="btn btn-sm btn-info">📌 Hacer Usuario</a>
                                <?php endif; ?>
                                <a href="?eliminar=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este usuario? Se borrarán todas sus tareas.')">🗑️ Eliminar</a>
                            <?php else: ?>
                                <span class="text-muted">(Eres tú)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>