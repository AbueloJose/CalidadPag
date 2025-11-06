<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Docente)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'docente') {
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

// 3. Verificar que se envíe por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Obtener los datos
    $id_aplicacion = $_POST['id_aplicacion'];
    $nuevo_estado = $_POST['nuevo_estado']; // 'Aprobada' o 'Rechazada'
    $id_docente_revisor = $_SESSION['usuario_id'];

    // 5. Validaciones
    if (empty($id_aplicacion) || !in_array($nuevo_estado, ['Aprobada', 'Rechazada'])) {
        header("Location: ../../docente_aprobar_aplicaciones.php?error=Datos incompletos.");
        exit();
    }

    try {
        // 6. Conexión a la BD
        $pdo = (new Database())->connect();

        // 7. Actualizar el estado de la aplicación
        // Asignamos el docente que revisó y la fecha de revisión
        $stmt_update = $pdo->prepare(
            "UPDATE aplicaciones 
             SET estado = ?, id_docente_revisor = ?, fecha_revision = CURRENT_TIMESTAMP
             WHERE id = ? AND estado = 'Pendiente_Docente'"
        );
        $stmt_update->execute([$nuevo_estado, $id_docente_revisor, $id_aplicacion]);

        // 8. Crear la notificación para el estudiante (RF09)
        
        // 8a. Necesitamos el ID del estudiante y el nombre de la vacante
        $stmt_info = $pdo->prepare(
            "SELECT a.id_estudiante, v.titulo_vacante
             FROM aplicaciones a
             JOIN vacantes v ON a.id_vacante = v.id
             WHERE a.id = ?"
        );
        $stmt_info->execute([$id_aplicacion]);
        $info = $stmt_info->fetch();
        
        if ($info) {
            $id_estudiante_destino = $info['id_estudiante'];
            $nombre_vacante = $info['titulo_vacante'];

            // 8b. Crear el mensaje
            if ($nuevo_estado == 'Aprobada') {
                $mensaje = "¡Felicidades! Tu postulación para '$nombre_vacante' ha sido APROBADA.";
            } else { // 'Rechazada'
                $mensaje = "Lo sentimos, tu postulación para '$nombre_vacante' ha sido RECHAZADA.";
            }

            // 8c. Insertar la notificación
            $stmt_notif = $pdo->prepare(
                "INSERT INTO notificaciones (id_usuario_destino, mensaje) 
                 VALUES (?, ?)"
            );
            $stmt_notif->execute([$id_estudiante_destino, $mensaje]);
        }

        // 9. Redirigir de vuelta
        header("Location: ../../docente_aprobar_aplicaciones.php?exito=Aplicación gestionada correctamente");
        exit();

    } catch (PDOException $e) {
        
        // --- MODO DEBUG ACTIVADO ---
        // Mostramos el error real en lugar de ocultarlo
        die("¡Error de Debug! El problema real es: " . $e->getMessage());

        // header("Location: ../../docente_aprobar_aplicaciones.php?error=Error en la base de datos.");
        // exit();
    }
    
} else {
    // Si no es POST, redirigir
    header("Location: ../../dashboard_docente.php");
    exit();
}
?>