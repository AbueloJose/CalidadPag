<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Docente)
// Solo un 'docente' puede revisar documentos
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'docente') {
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

// 3. Verificar que se envíe por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Obtener los datos del formulario de revisión
    $id_documento = $_POST['id_documento'];
    $nuevo_estado = $_POST['nuevo_estado']; // Debería ser 'Aprobado' u 'Observado'
    $comentarios = trim($_POST['comentarios']);
    
    // 5. Validaciones
    if (empty($id_documento) || empty($nuevo_estado)) {
         header("Location: ../../docente_revisiones.php?error=Datos incompletos para la revisión");
         exit();
    }
    
    // Es obligatorio un comentario si se marca como 'Observado'
    if ($nuevo_estado == 'Observado' && empty($comentarios)) {
         
         // Se redirige a la lista de revisiones, no a un formulario que no existe.
         header("Location: ../../docente_revisiones.php?error=Debe agregar un comentario para las observaciones");
         exit();
    }

    try {
        // 6. Conexión a la BD
        $pdo = (new Database())->connect();

        // 7. Actualizar el documento (RF07)
        $stmt_update = $pdo->prepare(
            "UPDATE documentos_seguimiento 
             SET estado_revision = ?, comentarios_docente = ? 
             WHERE id = ?"
        );
        $stmt_update->execute([$nuevo_estado, $comentarios, $id_documento]);

        // 8. Crear la notificación para el estudiante (RF09)
        
        // 8a. Necesitamos saber a qué estudiante notificar.
        // Buscamos el id_estudiante y el tipo_documento usando el id_documento
        $stmt_info = $pdo->prepare(
            "SELECT a.id_estudiante, d.tipo_documento
             FROM documentos_seguimiento d
             JOIN aplicaciones a ON d.id_aplicacion = a.id
             WHERE d.id = ?"
        );
        $stmt_info->execute([$id_documento]);
        $info_doc = $stmt_info->fetch();
        
        if ($info_doc) {
            $id_estudiante_destino = $info_doc['id_estudiante'];
            // Formateamos "Plan_Trabajo" a "Plan Trabajo" para un mensaje amigable
            $nombre_documento = str_replace('_', ' ', $info_doc['tipo_documento']);

            // 8b. Crear el mensaje
            if ($nuevo_estado == 'Aprobado') {
                $mensaje = "¡Felicidades! Tu $nombre_documento ha sido APROBADO.";
            } else { // 'Observado'
                $mensaje = "Atención: Tienes observaciones en tu $nombre_documento. Revisa los comentarios.";
            }

            // 8c. Insertar la notificación en la BD
            $stmt_notif = $pdo->prepare(
                "INSERT INTO notificaciones (id_usuario_destino, mensaje) 
                 VALUES (?, ?)"
            );
            $stmt_notif->execute([$id_estudiante_destino, $mensaje]);
        }

        // 9. Redirigir de vuelta a la lista de revisiones
        header("Location: ../../docente_revisiones.php?exito=Revisión guardada correctamente");
        exit();

    } catch (PDOException $e) {
        // Manejo de errores
         header("Location: ../../docente_revisiones.php?error=Error en la base de datos.");
         exit();
    }
    
} else {
    // Si no es POST, redirigir al dashboard del docente
    header("Location: ../../dashboard_docente.php");
    exit();
}
?>