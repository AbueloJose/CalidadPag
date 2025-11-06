<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['estudiante']);

// 2. Cargar BD
require_once 'config/database.php';
$pdo = (new Database())->connect();

// 3. Consultar vacantes activas de instituciones con convenio activo
$stmt = $pdo->prepare("
    SELECT v.*, i.nombre_empresa, i.direccion 
    FROM vacantes v
    JOIN instituciones i ON v.id_institucion = i.id
    WHERE v.activa = 1 AND i.convenio_activo = 1
    ORDER BY i.nombre_empresa, v.titulo_vacante
");
$stmt->execute();
$vacantes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-search"></i> Buscar Vacantes Disponibles</h3>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    
    <?php if (empty($vacantes)): ?>
        <div class="col-12">
            <div class="alert alert-warning text-center">
                No hay vacantes disponibles publicadas por el momento.
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($vacantes as $v): ?>
    <div class="col">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0"><?php echo htmlspecialchars($v['titulo_vacante']); ?></h5>
            </div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-success"><?php echo htmlspecialchars($v['nombre_empresa']); ?></h6>
                <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($v['direccion']); ?></small></p>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($v['descripcion'])); // nl2br para saltos de línea ?></p>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Área:</strong> <?php echo htmlspecialchars($v['area_carrera']); ?></li>
                <li class="list-group-item"><strong>Cupos:</strong> <?php echo $v['cupos_disponibles']; ?></li>
            </ul>
            <div class="card-footer text-center">
                <form action="modules/practicas/aplicar_vacante.php" method="POST" class="d-grid">
                    <input type="hidden" name="id_vacante" value="<?php echo $v['id']; ?>">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('¿Estás seguro de que deseas postular a esta vacante?');">
                        <i class="bi bi-check-lg"></i> Postular Ahora
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
</div>

<?php
// 6. Cargar el footer
include 'templates/footer.php';
?>