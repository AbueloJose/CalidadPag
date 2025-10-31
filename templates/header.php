<?php
// 1. ¡SIEMPRE PRIMERO! Iniciar la sesión.
session_start();

/**
 * 2. FUNCIÓN DE SEGURIDAD (RF02)
 * Verifica si el usuario ha iniciado sesión y si tiene el rol permitido.
 * @param array $roles_permitidos Un array con los roles (ej: ['admin', 'docente'])
 */
function verificarAcceso($roles_permitidos = []) {
    // 2a. ¿Hay alguien logueado?
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php?error=Debes iniciar sesión");
        exit();
    }
    
    // 2b. ¿El rol del usuario está en la lista de permitidos?
    if (!in_array($_SESSION['usuario_rol'], $roles_permitidos)) {
        // Si no está permitido, lo mandamos a su propio dashboard
        $rol = $_SESSION['usuario_rol']; // 'admin', 'docente', 'estudiante'
        header("Location: dashboard_{$rol}.php?error=Acceso no autorizado");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Prácticas</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="public/css/style.css"> 
</head>
<body>

    <?php
    // 4. Cargar la barra de navegación correcta según el ROL
    // (header.php y los nav_*.php están en la misma carpeta 'templates')
    if (isset($_SESSION['usuario_rol'])) {
        switch ($_SESSION['usuario_rol']) {
            case 'admin':
                include 'nav_admin.php'; 
                break;
            case 'docente':
                include 'nav_docente.php';
                break;
            case 'estudiante':
                include 'nav_estudiante.php';
                break;
        }
    }
    ?>

    <div class="container mt-4 mb-5">
    
        <?php
        // 8. Sistema de Alertas (RF09)
        if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($_GET['exito']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
    