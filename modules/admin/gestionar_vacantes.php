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

    // Usamos un switch para cada acción
    switch ($accion) {
        
        // --- ACCIÓN: CREAR NUEVA VACANTE ---
        case 'crear':
            $id_institucion = $_POST['id_institucion'];
            $titulo_vacante = trim($_POST['titulo_vacante']);
            $descripcion = trim($_POST['descripcion']);
            $area_carrera = trim($_POST['area_carrera']);
            $cupos_disponibles = intval($_POST['cupos_disponibles']); // Convertir a entero
            $activa = isset($_POST['activa']) ? 1 : 0; // Checkbox 'activa'

            if (empty($id_institucion) || empty($titulo_vacante) || $cupos_disponibles <= 0) {
                header("Location: ../../admin_gestionar_vacantes.php?error=Institución, Título y Cupos (>0) son obligatorios");
                exit();
            }

            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO vacantes (id_institucion, titulo_vacante, descripcion, area_carrera, cupos_disponibles, activa) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$id_institucion, $titulo_vacante, $descripcion, $area_carrera, $cupos_disponibles, $activa]);
                header("Location: ../../admin_gestionar_vacantes.php?exito=Vacante creada correctamente");

            } catch (PDOException $e) {
                header("Location: ../../admin_gestionar_vacantes.php?error=Error al crear la vacante");
            }
            break;

        // --- ACCIÓN: EDITAR VACANTE ---
        case 'editar':
            $id_vacante = $_POST['id_vacante'];
            $id_institucion = $_POST['id_institucion'];
            $titulo_vacante = trim($_POST['titulo_vacante']);
            $descripcion = trim($_POST['descripcion']);
            $area_carrera = trim($_POST['area_carrera']);
            $cupos_disponibles = intval($_POST['cupos_disponibles']);
            $activa = isset($_POST['activa']) ? 1 : 0; // Checkbox 'activa'

            if (empty($id_vacante) || empty($id_institucion) || empty($titulo_vacante) || $cupos_disponibles <= 0) {
                header("Location: ../../admin_gestionar_vacantes.php?error=Datos incompletos o inválidos");
                exit();
            }

            try {
                $stmt = $pdo->prepare(
                    "UPDATE vacantes 
                     SET id_institucion = ?, titulo_vacante = ?, descripcion = ?, area_carrera = ?, cupos_disponibles = ?, activa = ?
                     WHERE id = ?"
                );
                $stmt->execute([$id_institucion, $titulo_vacante, $descripcion, $area_carrera, $cupos_disponibles, $activa, $id_vacante]);
                header("Location: ../../admin_gestionar_vacantes.php?exito=Vacante actualizada");
                
            } catch (PDOException $e) {
                 header("Location: ../../admin_gestionar_vacantes.php?error=Error al actualizar la vacante");
            }
            break;
            
        // --- ACCIÓN: CAMBIAR ESTADO (ACTIVAR/DESACTIVAR) ---
        // Esto es útil para ocultar rápidamente una vacante sin borrarla
        case 'cambiar_estado':
            $id_vacante = $_POST['id_vacante'];
            $nuevo_estado = $_POST['nuevo_estado']; // Será 0 o 1

            if (!isset($id_vacante) || !isset($nuevo_estado)) {
                header("Location: ../../admin_gestionar_vacantes.php?error=Datos incompletos");
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE vacantes SET activa = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $id_vacante]);
            
            $mensaje = $nuevo_estado == 1 ? "Vacante activada" : "Vacante desactivada";
            header("Location: ../../admin_gestionar_vacantes.php?exito=" . $mensaje);
            break;

        // --- DEFAULT ---
        default:
            header("Location: ../../admin_gestionar_vacantes.php?error=Acción no válida");
            break;
    }
    
    exit();

} else {
    // Si no es POST o no hay acción
    header("Location: ../../dashboard_admin.php");
    exit();
}
?>