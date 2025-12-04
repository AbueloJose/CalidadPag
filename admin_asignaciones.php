<?php
// 1. Cargar el header y verificar acceso
include 'templates/header.php';
verificarAcceso(['admin']);

// 2. Cargar BD
require_once 'config/database.php';
$pdo = (new Database())->connect();

// 3. Consultar TODAS las prácticas activas (Aprobadas o En_Curso)
$stmt_practicas = $pdo->query("
    SELECT 
        a.id AS id_aplicacion,
        u.nombres AS est_nombres,
        u.apellidos AS est_apellidos,
        v.titulo_vacante,
        i.nombre_empresa,
        a.id_docente_revisor,
        doc.nombres AS doc_nombres,
        doc.apellidos AS doc_apellidos
    FROM aplicaciones a
    JOIN usuarios u ON a.id_estudiante = u.id
    JOIN vacantes v ON a.id_vacante = v.id
    JOIN instituciones i ON v.id_institucion = i.id
    LEFT JOIN usuarios doc ON a.id_docente_revisor = doc.id 
    WHERE a.estado IN ('Aprobada', 'En_Curso')
    ORDER BY a.fecha_aplicacion DESC
");
$practicas = $stmt_practicas->fetchAll();

// 4. Consultar TODOS los docentes disponibles
$stmt_docentes = $pdo->query("
    SELECT id, nombres, apellidos 
    FROM usuarios 
    WHERE rol = 'docente' AND activo = 1
    ORDER BY apellidos
");
$docentes = $stmt_docentes->fetchAll();
?>

<h3><i class="bi bi-person-video3"></i> Asignar Docentes a Prácticas</h3>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Práctica / Empresa</th>
                        <th>Docente Asignado Actual</th>
                        <th style="width: 35%;">Asignar / Cambiar Docente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($practicas)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay prácticas activas (Aprobadas o En Curso) por asignar.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($practicas as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['est_apellidos'] . ', ' . $p['est_nombres']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($p['titulo_vacante']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($p['nombre_empresa']); ?></small>
                        </td>
                        <td>
                            <?php if ($p['id_docente_revisor']): ?>
                                <span class="badge bg-success"><?php echo htmlspecialchars($p['doc_apellidos'] . ', ' . $p['doc_nombres']); ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Sin Asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="modules/asignacion/asignar_docente.php" method="POST" class="d-flex">
                                <input type="hidden" name="id_aplicacion" value="<?php echo $p['id_aplicacion']; ?>">
                                
                                <select name="id_docente" class="form-select form-select-sm me-2" required>
                                    <option value="">-- Seleccionar docente --</option>
                                    <?php foreach ($docentes as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo ($d['id'] == $p['id_docente_revisor']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['apellidos'] . ', ' . $d['nombres']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
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