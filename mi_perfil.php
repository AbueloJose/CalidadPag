<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['estudiante', 'docente', 'admin']); // Todos pueden ver su perfil

// 2. Cargar BD y datos del usuario
require_once 'config/database.php';
$pdo = (new Database())->connect();
$id_usuario = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();
?>

<h3><i class="bi bi-person-circle"></i> Mi Perfil</h3>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body text-center">
                <img src="<?php echo htmlspecialchars($usuario['ruta_imagen_perfil']); ?>" alt="Foto de Perfil" class="profile-image">
                
                <h5 class="card-title"><?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?></h5>
                <p class="text-muted"><?php echo ucfirst($usuario['rol']); ?></p>

                <form action="modules/perfil/subir_imagen_perfil.php" method="POST" enctype="multipart/form-data">
                    <label for="imagen_perfil" class="form-label">Cambiar foto (JPG, PNG)</label>
                    <input class="form-control form-control-sm mb-2" type="file" name="imagen_perfil" required>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Subir Imagen</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Mi Currículum (CV)</h5>
                <?php if ($usuario['ruta_cv']): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> CV Subido
                        <a href="<?php echo htmlspecialchars($usuario['ruta_cv']); ?>" target="_blank" class="btn btn-sm btn-outline-success float-end">Ver</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> No has subido tu CV.
                    </div>
                <?php endif; ?>
                
                <form action="modules/perfil/subir_cv.php" method="POST" enctype="multipart/form-data">
                    <label for="archivo_cv" class="form-label">Subir/Actualizar CV (Solo PDF)</label>
                    <input class="form-control form-control-sm mb-2" type="file" name="archivo_cv" required>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Subir CV</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="mb-0">Datos Personales</h5>
            </div>
            <div class="card-body">
                <form action="modules/perfil/actualizar_datos.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" value="<?php echo htmlspecialchars($usuario['nombres']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Principal (Institucional)</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled readonly>
                        <small class="text-muted">El email principal no se puede cambiar.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email de Respaldo</label>
                            <input type="email" class="form-control" name="email_respaldo" value="<?php echo htmlspecialchars($usuario['email_respaldo'] ?: ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?: ''); ?>">
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el footer
include 'templates/footer.php';
?>