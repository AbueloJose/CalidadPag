<?php
// 1. Cargar el header
include 'templates/header.php';

// 2. Verificar acceso
verificarAcceso(['admin']);

// 3. Incluir BD
require_once 'config/database.php';
$pdo = (new Database())->connect();

// 4. Consultas para estadísticas
$stmt_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios");
$total_usuarios = $stmt_usuarios->fetchColumn();

$stmt_empresas = $pdo->query("SELECT COUNT(*) FROM instituciones WHERE convenio_activo = 1");
$total_empresas = $stmt_empresas->fetchColumn();

$stmt_practicas = $pdo->query("SELECT COUNT(*) FROM aplicaciones WHERE estado = 'En_Curso'");
$total_practicas_activas = $stmt_practicas->fetchColumn();

?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white">
        <h4 class="mb-0">Dashboard del Administrador</h4>
    </div>
    <div class="card-body">
        <h5 class="card-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombres']); ?>.</h5>
        <p class="card-text">Desde este panel puedes gestionar todo el sistema de prácticas pre-profesionales.</p>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center text-bg-primary shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-people-fill fs-1"></i>
                        <h3 class="card-title"><?php echo $total_usuarios; ?></h3>
                        <p class="card-text">Usuarios Totales (Estudiantes, Docentes, Admins)</p>
                        <a href="admin_gestionar_usuarios.php" class="btn btn-outline-light">Gestionar</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center text-bg-success shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-building-check fs-1"></i>
                        <h3 class="card-title"><?php echo $total_empresas; ?></h3>
                        <p class="card-text">Instituciones con Convenio Activo</p>
                        <a href="admin_gestionar_convenios.php" class="btn btn-outline-light">Gestionar</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-center text-bg-info shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-briefcase-fill fs-1"></i>
                        <h3 class="card-title"><?php echo $total_practicas_activas; ?></h3>
                        <p class="card-text">Estudiantes con Prácticas "En Curso"</p>
                        <a href="modules/reportes/generar_reporte_pdf.php" class="btn btn-outline-light">Ver Reporte</a>
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