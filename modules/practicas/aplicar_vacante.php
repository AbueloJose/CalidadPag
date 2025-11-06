<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Estudiante)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'estudiante') {
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

// 3. Verificar que se envíe por POST y tengamos el ID de la vacante
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_vacante'])) {

    $id_vacante = $_POST['id_vacante'];
    $id_estudiante = $_SESSION['usuario_id']; // ID del estudiante logueado

    try {
        $pdo = (new Database())->connect();

        // 4. VALIDACIÓN CRÍTICA: 
        // ¿El estudiante ya tiene una práctica activa o pendiente?
        $stmt_check = $pdo->prepare(
            "SELECT id FROM aplicaciones 
             WHERE id_estudiante = ? 
             AND estado IN ('Pendiente_Docente', 'Aprobada', 'En_Curso')"
        );
        $stmt_check->execute([$id_estudiante]);

        if ($stmt_check->fetch()) {
            // Si ya tiene una, no puede aplicar a otra.
            header("Location: ../../estudiante_buscar_vacantes.php?error=Ya tienes una aplicación activa o en curso. No puedes aplicar a otra.");
            exit();
        }

        // 5. VALIDACIÓN DE CUPOS (RF05)
        // Contamos cuántas aplicaciones 'Aprobadas' tiene esta vacante
        $stmt_cupos = $pdo->prepare(
            "SELECT COUNT(id) AS cupos_tomados FROM aplicaciones 
             WHERE id_vacante = ? AND estado IN ('Aprobada', 'En_Curso')"
        );
        $stmt_cupos->execute([$id_vacante]);
        $cupos_tomados = $stmt_cupos->fetchColumn();

        // Traemos los cupos totales de la vacante
        $stmt_vacante = $pdo->prepare("SELECT cupos_disponibles FROM vacantes WHERE id = ?");
        $stmt_vacante->execute([$id_vacante]);
        $cupos_disponibles = $stmt_vacante->fetchColumn();

        if ($cupos_tomados >= $cupos_disponibles) {
            header("Location: ../../estudiante_buscar_vacantes.php?error=Lo sentimos, esta vacante ya no tiene cupos disponibles.");
            exit();
        }

        // 6. ¡TODO BIEN! Creamos la aplicación
        // El estado 'Pendiente_Docente' significa que un docente debe revisarla
        $stmt_insert = $pdo->prepare(
            "INSERT INTO aplicaciones (id_estudiante, id_vacante, estado) 
             VALUES (?, ?, 'Pendiente_Docente')"
        );
        $stmt_insert->execute([$id_estudiante, $id_vacante]);

        // 7. Redirigimos al estudiante a "Mi Práctica" para que vea el estado
        header("Location: ../../estudiante_mi_practica.php?exito=¡Postulación exitosa! Tu aplicación está pendiente de revisión por un docente.");
        exit();

    } catch (PDOException $e) {
        header("Location: ../../estudiante_buscar_vacantes.php?error=Error en la base de datos.");
        exit();
    }

} else {
    // Si no es POST, redirigir al dashboard
    header("Location: ../../dashboard_estudiante.php");
    exit();
}
?>