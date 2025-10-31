<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['estudiante']);

// 2. Cargar BD
require_once 'config/database.php';
$pdo = (new Database())->connect();
$id_estudiante = $_SESSION['usuario_id'];

// 3. Buscar la práctica activa o pendiente del estudiante
$stmt_practica = $pdo->prepare(
    "SELECT a.*, v.titulo_vacante, i.nombre_empresa 
     FROM aplicaciones a
     JOIN vacantes v ON a.id_vacante = v.id
     JOIN instituciones i ON v.id_institucion = i.id
     WHERE a.id_estudiante = ? 
     AND a.estado IN ('Pendiente_Docente', 'Aprobada', 'En_Curso', 'Finalizada', 'Rechazada')
     ORDER BY a.fecha_aplicacion DESC
     LIMIT 1"
);
$stmt_practica->execute([$id_estudiante]);
$practica = $stmt_practica->fetch(); // false si no hay práctica

$documentos = [];
if ($practica) {
    // 4. Si hay práctica, buscar sus documentos
    $stmt_docs = $pdo->prepare(
        "SELECT * FROM documentos_seguimiento 
         WHERE id_aplicacion = ? 
         ORDER BY FIELD(tipo_documento, 'Plan_Trabajo', 'Informe_Mensual_1', 'Informe_Mensual_2', 'Informe_Final', 'Constancia_Empresa')"
    );
    $stmt_docs->execute([$practica['id']]);
    $documentos = $stmt_docs->fetchAll();
}
?>

<h3><i class="bi bi-folder-fill"></i> Mi Práctica y Documentos </h3>

<?php if (!$practica): ?>
    <div class="alert alert-info mt-4">
        <h4 class="alert-heading">Aún no tienes una práctica registrada.</h4>
        <p>No has postulado a ninguna vacante o tu postulación anterior fue anulada. Primero debes postular a una vacante.</p>
        <hr>
        <a href="estudiante_buscar_vacantes.php" class="btn btn-primary">
            <i class="bi bi-search"></i> Buscar Vacantes
        </a>
    </div>
    
<?php else: ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Estado de la Práctica</h5>
        </div>
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($practica['titulo_vacante']); ?></h5>
            <h6 class="card-subtitle mb-2 text-success"><?php echo htmlspecialchars($practica['nombre_empresa']); ?></h6>
            <?php 
                $estado = $practica['estado'];
                $badge_class = 'bg-secondary';
                if ($estado == 'Aprobada' || $estado == 'En_Curso') $badge_class = 'bg-success';
                if ($estado == 'Pendiente_Docente') $badge_class = 'bg-warning text-dark';
                if ($estado == 'Rechazada') $badge_class = 'bg-danger';
            ?>
            <p><strong>Estado:</strong> <span class="badge <?php echo $badge_class; ?> fs-6"><?php echo str_replace('_', ' ', $estado); ?></span></p>
        </div>
    </div>

    <?php if (in_array($practica['estado'], ['Aprobada', 'En_Curso', 'Finalizada'])): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Subir Documento </h5>
            </div>
            <div class="card-body">
                <form action="modules/practicas/subir_documento.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_aplicacion" value="<?php echo $practica['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                            <select name="tipo_documento" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Plan_Trabajo">Plan de Trabajo</option>
                                <option value="Informe_Mensual_1">Informe Mensual 1</option>
                                <option value="Informe_Mensual_2">Informe Mensual 2</option>
                                <option value="Informe_Final">Informe Final</option>
                                <option value="Constancia_Empresa">Constancia de la Empresa</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="archivo_documento" class="form-label">Archivo (PDF, DOCX, ZIP)</label>
                            <input type="file" class="form-control" name="archivo_documento" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-upload"></i> Subir
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="mb-0">Historial de Documentos</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo Documento</th>
                            <th>Estado Revisión</th>
                            <th>Fecha Subida</th>
                            <th>Comentarios del Docente</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr class="<?php echo $doc['estado_revision'] == 'Observado' ? 'table-warning' : ''; ?>">
                                <td><?php echo str_replace('_', ' ', $doc['tipo_documento']); ?></td>
                                <td>
                                    <?php 
                                    $estado_rev = $doc['estado_revision'];
                                    $badge_rev = 'bg-secondary';
                                    if ($estado_rev == 'Aprobado') $badge_rev = 'bg-success';
                                    if ($estado_rev == 'Observado') $badge_rev = 'bg-danger';
                                    if ($estado_rev == 'Pendiente') $badge_rev = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?php echo $badge_rev; ?>"><?php echo $estado_rev; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($doc['fecha_subida'])); ?></td>
                                <td>
                                    <?php if ($doc['estado_revision'] == 'Observado'): ?>
                                        <small class="text-danger"><?php echo htmlspecialchars($doc['comentarios_docente']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($doc['ruta_archivo']); ?>" target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                       <i class="bi bi-download"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($documentos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aún no has subido ningún documento.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    
    <?php elseif ($practica['estado'] == 'Pendiente_Docente'): ?>
        <div class="alert alert-warning">
            <h4 class="alert-heading">Postulación Pendiente</h4>
            <p>Tu postulación a <strong><?php echo htmlspecialchars($practica['nombre_empresa']); ?></strong> está siendo revisada por un docente. 
            Podrás subir tus documentos una vez que sea aprobada.</p>
        </div>
    <?php elseif ($practica['estado'] == 'Rechazada'): ?>
         <div class="alert alert-danger">
            <h4 class="alert-heading">Postulación Rechazada</h4>
            <p>Lo sentimos, tu postulación a <strong><?php echo htmlspecialchars($practica['nombre_empresa']); ?></strong> fue rechazada.</p>
            <hr>
            <a href="estudiante_buscar_vacantes.php" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar otras Vacantes
            </a>
        </div>
    <?php endif; ?>
    
<?php endif; ?>

<?php
// 8. Cargar el footer
include 'templates/footer.php';
?>