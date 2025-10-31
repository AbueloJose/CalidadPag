<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['docente']);

// 2. Cargar BD
require_once 'config/database.php';
$pdo = (new Database())->connect();

// 3. Consultar documentos pendientes de revisión
// (Uniendo tablas para obtener los nombres del estudiante y la vacante)
$stmt = $pdo->prepare("
    SELECT 
        d.id AS doc_id, 
        d.tipo_documento, 
        d.ruta_archivo, 
        d.fecha_subida,
        u.nombres, 
        u.apellidos,
        v.titulo_vacante
    FROM documentos_seguimiento d
    JOIN aplicaciones a ON d.id_aplicacion = a.id
    JOIN usuarios u ON a.id_estudiante = u.id
    JOIN vacantes v ON a.id_vacante = v.id
    WHERE d.estado_revision = 'Pendiente'
    ORDER BY d.fecha_subida ASC
");
$stmt->execute();
$documentos_pendientes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-clipboard2-check-fill"></i> Documentos Pendientes de Revisión </h3>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Vacante</th>
                        <th>Tipo Documento</th>
                        <th>Fecha Subida</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documentos_pendientes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay documentos pendientes por revisar.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($documentos_pendientes as $doc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['apellidos'] . ', ' . $doc['nombres']); ?></td>
                        <td><?php echo htmlspecialchars($doc['titulo_vacante']); ?></td>
                        <td><?php echo str_replace('_', ' ', $doc['tipo_documento']); // Ej: Plan_Trabajo -> Plan Trabajo ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($doc['fecha_subida'])); ?></td>
                        <td class="text-end">
                            <a href="<?php echo htmlspecialchars($doc['ruta_archivo']); ?>" target="_blank" 
                               class="btn btn-info btn-sm" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                            <button class="btn btn-warning btn-sm btnRevisar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalRevisar"
                                    data-doc-id="<?php echo $doc['doc_id']; ?>"
                                    data-estudiante="<?php echo htmlspecialchars($doc['apellidos'] . ', ' . $doc['nombres']); ?>"
                                    data-documento="<?php echo str_replace('_', ' ', $doc['tipo_documento']); ?>">
                                <i class="bi bi-pencil-square"></i> Revisar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRevisar" tabindex="-1" aria-labelledby="modalRevisarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modules/evaluacion/revisar_documento.php" method="POST">
                <input type="hidden" name="id_documento" id="review_doc_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRevisarLabel">Revisar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Estás revisando el documento: <br><strong><span id="review_doc_nombre"></span></strong></p>
                    <p>Estudiante: <strong><span id="review_doc_estudiante"></span></strong></p>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Decisión:</label>
                        <select name="nuevo_estado" class="form-select" required>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Observado">Observado (Requiere corrección)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios / Observaciones</label>
                        <textarea name="comentarios" class="form-control" rows="4" 
                                  placeholder="Si marcas 'Observado', es obligatorio dejar un comentario."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Guardar Revisión</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
// 7. Cargar el footer
include 'templates/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btnRevisar').forEach(button => {
        button.addEventListener('click', function () {
            // Obtener los datos del botón
            const docId = this.dataset.docId;
            const estudiante = this.dataset.estudiante;
            const documento = this.dataset.documento;
            
            // Poblar el modal
            document.getElementById('review_doc_id').value = docId;
            document.getElementById('review_doc_nombre').textContent = documento;
            document.getElementById('review_doc_estudiante').textContent = estudiante;
        });
    });
});
</script>