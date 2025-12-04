<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Admin)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

// 3. Verificar que se envíe por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Obtener los datos del formulario
    $id_aplicacion = $_POST['id_aplicacion'];
    $id_docente = $_POST['id_docente'];

    // 5. Validaciones
    if (empty($id_aplicacion) || empty($id_docente)) {
        header("Location: ../../admin_asignaciones.php?error=Datos incompletos.");
        exit();
    }

    try {
        // 6. Conexión a la BD
        $pdo = (new Database())->connect();

        // 7. Actualizar la aplicación con el ID del docente
        // Usamos la columna 'id_docente_revisor' que ya creaste.
        $stmt_update = $pdo->prepare(
            "UPDATE aplicaciones 
             SET id_docente_revisor = ?
             WHERE id = ?"
        );
        $stmt_update->execute([$id_docente, $id_aplicacion]);

        // 8. Crear la notificación para el DOCENTE (RF09)
        
        // 8a. Necesitamos el nombre del estudiante y la vacante
        $stmt_info = $pdo->prepare(
            "SELECT u.nombres, u.apellidos, v.titulo_vacante
             FROM aplicaciones a
             JOIN usuarios u ON a.id_estudiante = u.id
             JOIN vacantes v ON a.id_vacante = v.id
             WHERE a.id = ?"
        );
        $stmt_info->execute([$id_aplicacion]);
        $info = $stmt_info->fetch();
        
        if ($info) {
            $nombre_estudiante = $info['apellidos'] . ', ' . $info['nombres'];
            $nombre_vacante = $info['titulo_vacante'];

            // 8b. Crear el mensaje
            $mensaje = "Se te ha asignado para supervisar la práctica de: $nombre_estudiante en '$nombre_vacante'.";

            // 8c. Insertar la notificación
            $stmt_notif = $pdo->prepare(
                "INSERT INTO notificaciones (id_usuario_destino, mensaje) 
                 VALUES (?, ?)"
            );
            $stmt_notif->execute([$id_docente, $mensaje]);
        }

        // 9. Redirigir de vuelta
        header("Location: ../../admin_asignaciones.php?exito=Docente asignado correctamente");
        exit();

    } catch (PDOException $e) {
        header("Location: ../../admin_asignaciones.php?error=Error en la base de datos.");
        exit();
    }
    
} else {
    // Si no es POST, redirigir
    header("Location: ../../dashboard_admin.php");
    exit();
}
?>