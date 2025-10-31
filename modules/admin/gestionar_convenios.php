<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Admin)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

$pdo = (new Database())->connect();

// 3. Verificamos POST y acción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {

    $accion = $_POST['accion'];

    switch ($accion) {
        
        // --- ACCIÓN: CREAR INSTITUCIÓN/CONVENIO ---
        case 'crear':
            $nombre_empresa = trim($_POST['nombre_empresa']);
            $ruc = trim($_POST['ruc']);
            $direccion = trim($_POST['direccion']);
            $contacto_nombre = trim($_POST['contacto_nombre']);
            $contacto_email = trim($_POST['contacto_email']);
            $convenio_fecha_fin = trim($_POST['convenio_fecha_fin']) ?: null;
            
            // Si hay fecha de fin, el convenio está activo
            $convenio_activo = !empty($convenio_fecha_fin) ? 1 : 0; 

            if (empty($nombre_empresa) || empty($ruc)) {
                header("Location: ../../admin_gestionar_convenios.php?error=Nombre y RUC son obligatorios");
                exit();
            }

            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO instituciones (nombre_empresa, ruc, direccion, contacto_nombre, contacto_email, convenio_activo, convenio_fecha_fin) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$nombre_empresa, $ruc, $direccion, $contacto_nombre, $contacto_email, $convenio_activo, $convenio_fecha_fin]);
                header("Location: ../../admin_gestionar_convenios.php?exito=Institución registrada");

            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // RUC duplicado
                    header("Location: ../../admin_gestionar_convenios.php?error=El RUC ya está registrado");
                } else {
                    header("Location: ../../admin_gestionar_convenios.php?error=Error al registrar");
                }
            }
            break;

        // --- ACCIÓN: EDITAR INSTITUCIÓN/CONVENIO ---
        case 'editar':
            $id_institucion = $_POST['id_institucion'];
            $nombre_empresa = trim($_POST['nombre_empresa']);
            $ruc = trim($_POST['ruc']);
            $direccion = trim($_POST['direccion']);
            $contacto_nombre = trim($_POST['contacto_nombre']);
            $contacto_email = trim($_POST['contacto_email']);
            $convenio_fecha_fin = trim($_POST['convenio_fecha_fin']) ?: null;
            $convenio_activo = isset($_POST['convenio_activo']) ? 1 : 0; // Checkbox

            if (empty($id_institucion) || empty($nombre_empresa) || empty($ruc)) {
                header("Location: ../../admin_gestionar_convenios.php?error=Nombre y RUC son obligatorios");
                exit();
            }

            try {
                $stmt = $pdo->prepare(
                    "UPDATE instituciones 
                     SET nombre_empresa = ?, ruc = ?, direccion = ?, contacto_nombre = ?, contacto_email = ?, convenio_activo = ?, convenio_fecha_fin = ? 
                     WHERE id = ?"
                );
                $stmt->execute([$nombre_empresa, $ruc, $direccion, $contacto_nombre, $contacto_email, $convenio_activo, $convenio_fecha_fin, $id_institucion]);
                header("Location: ../../admin_gestionar_convenios.php?exito=Institución actualizada");
                
            } catch (PDOException $e) {
                 header("Location: ../../admin_gestionar_convenios.php?error=Error al actualizar");
            }
            break;
            
        // --- DEFAULT ---
        default:
            header("Location: ../../admin_gestionar_convenios.php?error=Acción no válida");
            break;
    }
    
    exit();

} else {
    // Si no es POST o no hay acción
    header("Location: ../../dashboard_admin.php");
    exit();
}
?>