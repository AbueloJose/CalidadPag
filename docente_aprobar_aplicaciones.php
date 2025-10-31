<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['docente']);

// 2. Cargar BD
require_once 'config/database.php';
$pdo = (new Database())->connect();

// 3. Consultar aplicaciones pendientes
$stmt = $pdo->prepare("
    SELECT 
        a.id AS id_aplicacion,
        a.fecha_aplicacion,
        u.nombres,
        u.apellidos,
        u.codigo,
        v.titulo_vacante,
        i.nombre_empresa
    FROM aplicaciones a
    JOIN usuarios u ON a.id_estudiante = u.id
    JOIN vacantes v ON a.id_vacante = v.id
    JOIN instituciones i ON v.id_institucion = i.id
    WHERE a.estado = 'Pendiente_Docente'
    ORDER BY a.fecha_aplicacion ASC
");
$stmt->execute();
$aplicaciones = $stmt->fetchAll();
?>

<h3><i class="bi bi-person-check-fill"></i> Aprobar Aplicaciones de Estudiantes</h3>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Código</th>
                        <th>Vacante</th>
                        <th>Empresa</th>
                        <th>Fecha Postulación</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aplicaciones)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay aplicaciones pendientes por aprobar.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($aplicaciones as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['apellidos'] . ', ' . $app['nombres']); ?></td>
                        <td><?php echo htmlspecialchars($app['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($app['titulo_vacante']); ?></td>
                        <td><?php echo htmlspecialchars($app['nombre_empresa']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($app['fecha_aplicacion'])); ?></td>
                        <td class="text-end">
                            
                            <form action="modules/evaluacion/gestionar_aplicacion.php" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de APROBAR a este estudiante?');">
                                <input type="hidden" name="id_aplicacion" value="<?php echo $app['id_aplicacion']; ?>">
                                <input type="hidden" name="nuevo_estado" value="Aprobada">
                                <button type="submit" class="btn btn-success btn-sm" title="Aprobar">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            
                            <form action="modules/evaluacion/gestionar_aplicacion.php" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Estás seguro de RECHAZAR a este estudiante?');">
                                <input type="hidden" name="id_aplicacion" value="<?php echo $app['id_aplicacion']; ?>">
                                <input type="hidden" name="nuevo_estado" value="Rechazada">
                                <button type="submit" class="btn btn-danger btn-sm" title="Rechazar">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Cargar el footer
include 'templates/footer.php';
?>