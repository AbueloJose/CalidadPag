<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Estudiante)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'estudiante') {
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

$id_estudiante = $_SESSION['usuario_id'];

// 3. Verificar que se envíe por POST y tengamos todos los datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_aplicacion'], $_POST['tipo_documento'], $_FILES['archivo_documento'])) {

    $id_aplicacion = $_POST['id_aplicacion'];
    $tipo_documento = $_POST['tipo_documento'];
    $file = $_FILES['archivo_documento'];

    // 4. Validar el archivo subido
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../../estudiante_mi_practica.php?error=Error al subir el archivo (Código: " . $file['error'] . ")");
        exit();
    }

    // 5. Configuración de subida
    $upload_dir = '../../public/uploads/'; // Directorio de subida (relativo a este script)
    $db_path_dir = 'public/uploads/';    // Ruta que se guardará en la BD (relativa a la raíz del proyecto)

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'rar'];

    if (!in_array($file_ext, $allowed_ext)) {
        header("Location: ../../estudiante_mi_practica.php?error=Tipo de archivo no permitido. Solo se aceptan: " . implode(', ', $allowed_ext));
        exit();
    }
    
    // 6. Crear un nombre de archivo único y seguro
    // Formato: app[ID_APP]_doc[TIPO]_user[ID_USER]_[timestamp].[ext]
    $new_file_name = "app{$id_aplicacion}_doc{$tipo_documento}_user{$id_estudiante}_" . time() . "." . $file_ext;
    $target_path = $upload_dir . $new_file_name;
    $db_path = $db_path_dir . $new_file_name;

    // 7. Mover el archivo
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        
        try {
            $pdo = (new Database())->connect();

            // 8. LÓGICA DE RE-SUBIDA:
            // Si el estudiante sube un 'Plan_Trabajo' que estaba 'Observado', 
            // actualizamos el existente en lugar de crear uno nuevo.
            
            $stmt_check = $pdo->prepare(
                "SELECT id, ruta_archivo FROM documentos_seguimiento 
                 WHERE id_aplicacion = ? AND tipo_documento = ? 
                 AND estado_revision IN ('Pendiente', 'Observado')"
            );
            $stmt_check->execute([$id_aplicacion, $tipo_documento]);
            $existing_doc = $stmt_check->fetch();

            if ($existing_doc) {
                // YA EXISTÍA (estaba observado/pendiente): Actualizamos
                $stmt = $pdo->prepare(
                    "UPDATE documentos_seguimiento 
                     SET ruta_archivo = ?, estado_revision = 'Pendiente', fecha_subida = CURRENT_TIMESTAMP, comentarios_docente = NULL 
                     WHERE id = ?"
                );
                $stmt->execute([$db_path, $existing_doc['id']]);
                
                // Borramos el archivo físico anterior para ahorrar espacio
                if (file_exists('../../' . $existing_doc['ruta_archivo'])) {
                    unlink('../../' . $existing_doc['ruta_archivo']);
                }
                
            } else {
                // ES NUEVO: Insertamos
                $stmt = $pdo->prepare(
                    "INSERT INTO documentos_seguimiento (id_aplicacion, tipo_documento, ruta_archivo, estado_revision) 
                     VALUES (?, ?, ?, 'Pendiente')"
                );
                $stmt->execute([$id_aplicacion, $tipo_documento, $db_path]);
            }
            
            header("Location: ../../estudiante_mi_practica.php?exito=Documento '$tipo_documento' subido correctamente.");
            exit();

        } catch (PDOException $e) {
            header("Location: ../../estudiante_mi_practica.php?error=Error al guardar en la base de datos.");
            exit();
        }

    } else {
        header("Location: ../../estudiante_mi_practica.php?error=Error al mover el archivo al servidor.");
        exit();
    }

} else {
    header("Location: ../../dashboard_estudiante.php");
    exit();
}
?>