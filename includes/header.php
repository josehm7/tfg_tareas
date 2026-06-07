<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas - Tu organización personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0d6efd">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Gestión de Tareas</a>
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    <?php if($_SESSION['usuario_rol'] == 'admin'): ?>
                        <span class="badge bg-danger">Admin</span>
                    <?php endif; ?>
                </span>
                <a class="nav-link" href="estadisticas.php">📊 Estadísticas</a>
                <a class="nav-link" href="perfil.php">👤 Mi Perfil</a>
                <?php if($_SESSION['usuario_rol'] == 'admin'): ?>
                    <a class="nav-link" href="adminUsuario.php">👥 Admin</a>
                    <a class="nav-link" href="adminReportes.php">🚨 Moderación</a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php">Cerrar sesión</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
    <div class="container mt-4">