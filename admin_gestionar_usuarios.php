<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['admin']);

// 2. Cargar BD y consultar usuarios
require_once 'config/database.php';
$pdo = (new Database())->connect();
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY apellidos, nombres");
$usuarios = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-people-fill"></i> Gestión de Usuarios </h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
        <i class="bi bi-plus-circle-fill"></i> Crear Nuevo Usuario
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Email / Código</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['apellidos'] . ', ' . $usuario['nombres']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($usuario['email']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($usuario['codigo'] ?: 'N/A'); ?></small>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo ucfirst($usuario['rol']); ?></span></td>
                        <td>
                            <?php if ($usuario['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-warning btn-sm btnEditar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarUsuario"
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-nombres="<?php echo htmlspecialchars($usuario['nombres']); ?>"
                                    data-apellidos="<?php echo htmlspecialchars($usuario['apellidos']); ?>"
                                    data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                    data-codigo="<?php echo htmlspecialchars($usuario['codigo']); ?>"
                                    data-rol="<?php echo $usuario['rol']; ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            
                            <form action="modules/admin/gestionar_usuarios.php" method="POST" class="d-inline">
                                <input type="hidden" name="accion" value="cambiar_estado">
                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                <?php if ($usuario['activo']): ?>
                                    <input type="hidden" name="nuevo_estado" value="0">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Desactivar">
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

<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modules/admin/gestionar_usuarios.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearLabel">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombres" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                     <div class="mb-3">
                        <label for="codigo" class="form-label">Código (Opcional, solo estudiantes)</label>
                        <input type="text" class="form-control" name="codigo">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña (mín. 6 caracteres)</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol</label>
                        <select name="rol" class="form-select" required>
                            <option value="estudiante">Estudiante</option>
                            <option value="docente">Docente</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modules/admin/gestionar_usuarios.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_usuario" id="edit_id_usuario"> 
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombres" id="edit_nombres" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" id="edit_apellidos" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                     <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" name="codigo" id="edit_codigo">
                    </div>
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol</label>
                        <select name="rol" class="form-select" id="edit_rol" required>
                            <option value="estudiante">Estudiante</option>
                            <option value="docente">Docente</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña (Opcional)</label>
                        <input type="password" class="form-control" name="password" minlength="6" 
                               placeholder="Dejar en blanco para no cambiar">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Usuario</button>
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
    // Escuchar clics en CUALQUIER botón con la clase 'btnEditar'
    document.querySelectorAll('.btnEditar').forEach(button => {
        button.addEventListener('click', function () {
            // Obtener los datos 'data-*' del botón clickeado
            const id = this.dataset.id;
            const nombres = this.dataset.nombres;
            const apellidos = this.dataset.apellidos;
            const email = this.dataset.email;
            const codigo = this.dataset.codigo;
            const rol = this.dataset.rol;
            
            // Poblar el formulario del modal 'Editar'
            document.getElementById('edit_id_usuario').value = id;
            document.getElementById('edit_nombres').value = nombres;
            document.getElementById('edit_apellidos').value = apellidos;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_codigo').value = codigo;
            document.getElementById('edit_rol').value = rol;
        });
    });
});
</script>