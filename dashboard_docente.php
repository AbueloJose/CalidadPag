<?php
// 1. Cargar el header
include 'templates/header.php';

// 2. Verificar acceso (Solo 'docente')
verificarAcceso(['docente']);

// 3. Incluir BD
require_once 'config/database.php';
$pdo = (new Database())->connect();
$id_docente = $_SESSION['usuario_id']; // Asumimos que el docente revisa lo que se le asigna

// 4. Consultas para estadísticas
// TODO: Esta consulta debe mejorarse cuando se implemente la asignación de docentes.
// Por ahora, contamos TODOS los documentos pendientes.
$stmt_pendientes = $pdo->query("SELECT COUNT(*) FROM documentos_seguimiento WHERE estado_revision = 'Pendiente'");
$total_pendientes = $stmt_pendientes->fetchColumn();

// Contamos aplicaciones pendientes de aprobación
$stmt_apps = $pdo->query("SELECT COUNT(*) FROM aplicaciones WHERE estado = 'Pendiente_Docente'");
$total_apps_pendientes = $stmt_apps->fetchColumn();


?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Dashboard del Docente</h4>
    </div>
    <div class="card-body">
        <h5 class="card-title">Bienvenido, Prof. <?php echo htmlspecialchars($_SESSION['usuario_nombres']); ?>.</h5>
        <p class="card-text">Aquí puede gestionar y dar seguimiento a las prácticas de los estudiantes asignados.</p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card text-center text-bg-warning shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-arrow-down-fill fs-1"></i>
                        <h3 class="card-title"><?php echo $total_pendientes; ?></h3>
                        <p class="card-text">Informes Pendientes de Revisión </p>
                        <a href="docente_revisiones.php" class="btn btn-outline-dark">Revisar Informes</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card text-center text-bg-danger shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-person-check-fill fs-1"></i>
                        <h3 class="card-title"><?php echo $total_apps_pendientes; ?></h3>
                        <p class="card-text">Aplicaciones de Estudiantes por Aprobar</p>
                        <a href="docente_aprobar_aplicaciones.php" class="btn btn-outline-light">Revisar Aplicaciones</a>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php
// 6. Cargar el footer
include 'templates/footer.php';
?>