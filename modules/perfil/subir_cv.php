<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$upload_dir = '../../public/uploads/cv/';
$allowed_types = ['application/pdf']; // Solo PDF

if (isset($_FILES['archivo_cv']) && $_FILES['archivo_cv']['error'] == 0) {
    $file = $_FILES['archivo_cv'];

    // 1. Validar tipo de archivo
    if ($file['type'] != 'application/pdf') {
        header("Location: ../../mi_perfil.php?error=Tipo de archivo no permitido (solo PDF).");
        exit();
    }

    // 2. Mover archivo
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Crear un nombre único (ej: cv_5_gonzales.pdf)
    $new_filename = 'cv_' . $id_usuario . '_' . preg_replace("/[^a-zA-Z0-9]/", "", $_SESSION['usuario_nombres']) . '.pdf';
    $upload_path = $upload_dir . $new_filename;
    $db_path = 'public/uploads/cv/' . $new_filename; // Ruta para guardar en BD

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // 3. Actualizar BD
        try {
            $pdo = (new Database())->connect();
            $stmt = $pdo->prepare("UPDATE usuarios SET ruta_cv = ? WHERE id = ?");
            $stmt->execute([$db_path, $id_usuario]);
            
            header("Location: ../../mi_perfil.php?exito=CV actualizado correctamente.");
            exit();

        } catch (PDOException $e) {
            header("Location: ../../mi_perfil.php?error=Error al guardar el CV en la BD.");
            exit();
        }
    } else {
        header("Location: ../../mi_perfil.php?error=Error al mover el archivo CV.");
        exit();
    }
} else {
    header("Location: ../../mi_perfil.php?error=No se seleccionó ningún archivo CV.");
    exit();
}
?>