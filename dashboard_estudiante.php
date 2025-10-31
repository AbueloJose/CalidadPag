<?php
// 1. Cargar el header
include 'templates/header.php';

// 2. Verificar acceso (Solo 'estudiante')
verificarAcceso(['estudiante']);

// 3. Incluir BD
require_once 'config/database.php';
$pdo = (new Database())->connect();
$id_estudiante = $_SESSION['usuario_id'];

// 4. Consultas
// 4a. Buscar práctica actual
$stmt_practica = $pdo->prepare(
    "SELECT a.*, v.titulo_vacante, i.nombre_empresa 
     FROM aplicaciones a
     JOIN vacantes v ON a.id_vacante = v.id
     JOIN instituciones i ON v.id_institucion = i.id
     WHERE a.id_estudiante = ? 
     AND a.estado IN ('Pendiente_Docente', 'Aprobada', 'En_Curso', 'Finalizada')
     ORDER BY a.fecha_aplicacion DESC
     LIMIT 1"
);
$stmt_practica->execute([$id_estudiante]);
$practica = $stmt_practica->fetch(); // false si no hay práctica

// 4b. Buscar notificaciones no leídas (RF09)
$stmt_notif = $pdo->prepare(
    "SELECT * FROM notificaciones 
     WHERE id_usuario_destino = ? AND leido = 0
     ORDER BY fecha_creacion DESC"
);
$stmt_notif->execute([$id_estudiante]);
$notificaciones = $stmt_notif->fetchAll();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header text-white" style="background-color: #005A9C;">
                <h4 class="mb-0">Mi Práctica Actual</h4>
            </div>
            <div class="card-body">
                <h5 class="card-title">Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombres']); ?>.</h5>
                
                <?php if ($practica): ?>
                    <p>Este es el estado actual de tu práctica:</p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Empresa:
                            <strong><?php echo htmlspecialchars($practica['nombre_empresa']); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Vacante:
                            <strong><?php echo htmlspecialchars($practica['titulo_vacante']); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Estado:
                            <?php 
                            $estado = $practica['estado'];
                            $badge_class = 'bg-secondary';
                            if ($estado == 'Aprobada' || $estado == 'En_Curso') $badge_class = 'bg-success';
                            if ($estado == 'Pendiente_Docente') $badge_class = 'bg-warning text-dark';
                            if ($estado == 'Rechazada') $badge_class = 'bg-danger';
                            ?>
                            <span class="badge <?php echo $badge_class; ?> fs-6"><?php echo str_replace('_', ' ', $estado); ?></span>
                        </li>
                    </ul>
                    <a href="estudiante_mi_practica.php" class="btn btn-primary mt-3">
                        <i class="bi bi-folder-fill"></i> Gestionar mis Documentos 
                    </a>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h4 class="alert-heading">¡Aún no tienes una práctica!</h4>
                        <p>No tienes ninguna práctica registrada o en curso. Explora las vacantes disponibles y postula a una.</p>
                        <hr>
                        <a href="estudiante_buscar_vacantes.php" class="btn btn-success">
                            <i class="bi bi-search"></i> Buscar Vacantes
                        </a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-bell-fill"></i> Notificaciones</h5>
            </div>
            <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($notificaciones)): ?>
                    <li class="list-group-item text-center text-muted">No hay notificaciones nuevas.</li>
                <?php else: ?>
                    <?php foreach ($notificaciones as $notif): ?>
                        <li class="list-group-item">
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?></small>
                            <p class="mb-0"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php
// 6. Cargar el footer
include 'templates/footer.php';
?>