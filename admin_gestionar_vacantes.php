<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['admin']);

// 2. Cargar BD y consultar datos
require_once 'config/database.php';
$pdo = (new Database())->connect();

// 3. Consultar TODAS las vacantes (uniendo el nombre de la empresa)
$stmt_vacantes = $pdo->query("
    SELECT v.*, i.nombre_empresa 
    FROM vacantes v
    JOIN instituciones i ON v.id_institucion = i.id
    ORDER BY v.activa DESC, i.nombre_empresa, v.titulo_vacante
");
$vacantes = $stmt_vacantes->fetchAll();

// 4. Consultar solo instituciones ACTIVAS (para los formularios)
$stmt_inst = $pdo->query("
    SELECT id, nombre_empresa 
    FROM instituciones 
    WHERE convenio_activo = 1 
    ORDER BY nombre_empresa
");
$instituciones_activas = $stmt_inst->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-briefcase-fill"></i> Gestión de Vacantes</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearVacante">
        <i class="bi bi-plus-circle-fill"></i> Crear Nueva Vacante
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Institución</th>
                        <th>Título de Vacante</th>
                        <th>Área / Carrera</th>
                        <th>Cupos</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vacantes as $v): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['nombre_empresa']); ?></td>
                        <td><?php echo htmlspecialchars($v['titulo_vacante']); ?></td>
                        <td><?php echo htmlspecialchars($v['area_carrera'] ?: 'N/A'); ?></td>
                        <td><?php echo $v['cupos_disponibles']; ?></td>
                        <td>
                            <?php if ($v['activa']): ?>
                                <span class="badge bg-success">Activa</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm btnEditarVacante" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarVacante"
                                    data-id="<?php echo $v['id']; ?>"
                                    data-id_institucion="<?php echo $v['id_institucion']; ?>"
                                    data-titulo="<?php echo htmlspecialchars($v['titulo_vacante']); ?>"
                                    data-descripcion="<?php echo htmlspecialchars($v['descripcion']); ?>"
                                    data-area="<?php echo htmlspecialchars($v['area_carrera']); ?>"
                                    data-cupos="<?php echo $v['cupos_disponibles']; ?>"
                                    data-activa="<?php echo $v['activa']; ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            
                            <form action="modules/admin/gestionar_vacantes.php" method="POST" class="d-inline">
                                <input type="hidden" name="accion" value="cambiar_estado">
                                <input type="hidden" name="id_vacante" value="<?php echo $v['id']; ?>">
                                <?php if ($v['activa']): ?>
                                    <input type="hidden" name="nuevo_estado" value="0">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Desactivar">
                                        <i class="bi bi-toggle-off"></i>
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="nuevo_estado" value="1">
                                    <button type="submit" class="btn btn-success btn-sm" title="Activar">
                                        <i class="bi bi-toggle-on"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearVacante" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="modules/admin/gestionar_vacantes.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearLabel">Crear Nueva Vacante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Institución (con convenio activo)</label>
                        <select name="id_institucion" class="form-select" required>
                            <option value="">-- Seleccione una institución --</option>
                            <?php foreach ($instituciones_activas as $inst): ?>
                                <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['nombre_empresa']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Título de la Vacante</label>
                        <input type="text" class="form-control" name="titulo_vacante" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Área o Carrera</label>
                            <input type="text" class="form-control" name="area_carrera" placeholder="Ej: Ingeniería de Software">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cupos Disponibles</label>
                            <input type="number" class="form-control" name="cupos_disponibles" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activa" value="1" id="crear_activa" checked>
                        <label class="form-check-label" for="crear_activa">
                            Publicar esta vacante (Activa)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Vacante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarVacante" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="modules/admin/gestionar_vacantes.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_vacante" id="edit_id_vacante">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Vacante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Institución (con convenio activo)</label>
                        <select name="id_institucion" id="edit_id_institucion" class="form-select" required>
                            <option value="">-- Seleccione una institución --</option>
                            <?php foreach ($instituciones_activas as $inst): ?>
                                <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['nombre_empresa']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Título de la Vacante</label>
                        <input type="text" class="form-control" name="titulo_vacante" id="edit_titulo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Área o Carrera</label>
                            <input type="text" class="form-control" name="area_carrera" id="edit_area">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cupos Disponibles</label>
                            <input type="number" class="form-control" name="cupos_disponibles" id="edit_cupos" min="1" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activa" value="1" id="edit_activa">
                        <label class="form-check-label" for="edit_activa">
                            Publicar esta vacante (Activa)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Vacante</button>
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
    document.querySelectorAll('.btnEditarVacante').forEach(button => {
        button.addEventListener('click', function () {
            // Poblar el formulario del modal 'Editar'
            document.getElementById('edit_id_vacante').value = this.dataset.id;
            document.getElementById('edit_id_institucion').value = this.dataset.id_institucion;
            document.getElementById('edit_titulo').value = this.dataset.titulo;
            document.getElementById('edit_descripcion').value = this.dataset.descripcion;
            document.getElementById('edit_area').value = this.dataset.area;
            document.getElementById('edit_cupos').value = this.dataset.cupos;
            
            // Marcar el checkbox si está activa
            const activa = this.dataset.activa == '1';
            document.getElementById('edit_activa').checked = activa;
        });
    });
});
</script>