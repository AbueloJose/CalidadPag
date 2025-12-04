<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$upload_dir = '../../public/uploads/perfiles/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] == 0) {
    $file = $_FILES['imagen_perfil'];

    // 1. Validar tipo de archivo
    if (!in_array($file['type'], $allowed_types)) {
        header("Location: ../../mi_perfil.php?error=Tipo de archivo no permitido (solo JPG, PNG, GIF).");
        exit();
    }

    // 2. Mover archivo
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Crear un nombre único (ej: usuario_5.jpg)
    $new_filename = 'usuario_' . $id_usuario . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    $db_path = 'public/uploads/perfiles/' . $new_filename; // Ruta para guardar en BD

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // 3. Actualizar BD
        try {
            $pdo = (new Database())->connect();
            $stmt = $pdo->prepare("UPDATE usuarios SET ruta_imagen_perfil = ? WHERE id = ?");
            $stmt->execute([$db_path, $id_usuario]);
            
            header("Location: ../../mi_perfil.php?exito=Imagen de perfil actualizada.");
            exit();

        } catch (PDOException $e) {
            header("Location: ../../mi_perfil.php?error=Error al guardar la imagen en la BD.");
            exit();
        }
    } else {
        header("Location: ../../mi_perfil.php?error=Error al mover el archivo subido.");
        exit();
    }
} else {
    header("Location: ../../mi_perfil.php?error=No se seleccionó ningún archivo o hubo un error.");
    exit();
}
?>