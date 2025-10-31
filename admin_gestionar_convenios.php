<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['admin']);

// 2. Cargar BD y consultar instituciones
require_once 'config/database.php';
$pdo = (new Database())->connect();
$stmt = $pdo->query("SELECT * FROM instituciones ORDER BY nombre_empresa");
$instituciones = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-building-check"></i> Gestión de Convenios </h3>
    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseCrearConvenio">
        <i class="bi bi-plus-circle-fill"></i> Registrar Institución
    </button>
</div>

<div class="collapse" id="collapseCrearConvenio">
    <div class="card card-body mb-3 shadow-sm">
        <form action="modules/admin/gestionar_convenios.php" method="POST">
            <input type="hidden" name="accion" value="crear">
            <h5 class="mb-3">Nueva Institución</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nombre Empresa</label>
                    <input type="text" class="form-control" name="nombre_empresa" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>RUC</label>
                    <input type="text" class="form-control" name="ruc" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Dirección</label>
                <input type="text" class="form-control" name="direccion">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nombre Contacto (RRHH)</label>
                    <input type="text" class="form-control" name="contacto_nombre">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email Contacto</label>
                    <input type="email" class="form-control" name="contacto_email">
                </div>
            </div>
             <div class="mb-3">
                <label>Fecha Fin de Convenio (Opcional)</label>
                <input type="date" class="form-control" name="convenio_fecha_fin">
                <small class="text-muted">Si se establece una fecha, el convenio se marcará como activo.</small>
            </div>
            <button type="submit" class="btn btn-success">Guardar Institución</button>
        </form>
    </div>
</div>


<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Institución</th>
                        <th>RUC</th>
                        <th>Contacto</th>
                        <th>Estado Convenio</th>
                        <th>Fin Convenio</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($instituciones as $inst): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inst['nombre_empresa']); ?></td>
                        <td><?php echo htmlspecialchars($inst['ruc']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($inst['contacto_nombre'] ?: 'N/A'); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($inst['contacto_email'] ?: 'N/A'); ?></small>
                        </td>
                        <td>
                            <?php if ($inst['convenio_activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $inst['convenio_fecha_fin'] ?: 'N/A'; ?></td>
                        
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm btnEditarConvenio" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarConvenio"
                                    data-id="<?php echo $inst['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($inst['nombre_empresa']); ?>"
                                    data-ruc="<?php echo htmlspecialchars($inst['ruc']); ?>"
                                    data-direccion="<?php echo htmlspecialchars($inst['direccion']); ?>"
                                    data-contacto_nombre="<?php echo htmlspecialchars($inst['contacto_nombre']); ?>"
                                    data-contacto_email="<?php echo htmlspecialchars($inst['contacto_email']); ?>"
                                    data-fecha_fin="<?php echo $inst['convenio_fecha_fin']; ?>"
                                    data-activo="<?php echo $inst['convenio_activo']; ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarConvenio" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="modules/admin/gestionar_convenios.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_institucion" id="edit_id_institucion">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre Empresa</label>
                            <input type="text" class="form-control" name="nombre_empresa" id="edit_nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>RUC</label>
                            <input type="text" class="form-control" name="ruc" id="edit_ruc" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Dirección</label>
                        <input type="text" class="form-control" name="direccion" id="edit_direccion">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre Contacto (RRHH)</label>
                            <input type="text" class="form-control" name="contacto_nombre" id="edit_contacto_nombre">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email Contacto</label>
                            <input type="email" class="form-control" name="contacto_email" id="edit_contacto_email">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Fecha Fin de Convenio</label>
                            <input type="date" class="form-control" name="convenio_fecha_fin" id="edit_fecha_fin">
                        </div>
                         <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="convenio_activo" value="1" id="edit_activo">
                                <label class="form-check-label" for="edit_activo">
                                    Convenio Activo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Institución</button>
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
    document.querySelectorAll('.btnEditarConvenio').forEach(button => {
        button.addEventListener('click', function () {
            // Poblar el formulario del modal 'Editar'
            document.getElementById('edit_id_institucion').value = this.dataset.id;
            document.getElementById('edit_nombre').value = this.dataset.nombre;
            document.getElementById('edit_ruc').value = this.dataset.ruc;
            document.getElementById('edit_direccion').value = this.dataset.direccion;
            document.getElementById('edit_contacto_nombre').value = this.dataset.contactoNombre;
            document.getElementById('edit_contacto_email').value = this.dataset.contactoEmail;
            document.getElementById('edit_fecha_fin').value = this.dataset.fechaFin;
            
            // Marcar el checkbox si está activo
            const activo = this.dataset.activo == '1';
            document.getElementById('edit_activo').checked = activo;
        });
    });
});
</script>