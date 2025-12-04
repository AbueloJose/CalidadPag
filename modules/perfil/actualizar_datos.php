<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['usuario_id'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $telefono = $_POST['telefono'];
    $email_respaldo = $_POST['email_respaldo'];

    // Validar (puedes añadir más validaciones)
    if (empty($nombres) || empty($apellidos)) {
        header("Location: ../../mi_perfil.php?error=El nombre y apellido no pueden estar vacíos.");
        exit();
    }

    try {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare(
            "UPDATE usuarios 
             SET nombres = ?, apellidos = ?, telefono = ?, email_respaldo = ?
             WHERE id = ?"
        );
        $stmt->execute([$nombres, $apellidos, $telefono, $email_respaldo, $id_usuario]);
        
        // Actualizar el nombre en la sesión
        $_SESSION['usuario_nombres'] = $nombres;

        header("Location: ../../mi_perfil.php?exito=Perfil actualizado correctamente.");
        exit();

    } catch (PDOException $e) {
        header("Location: ../../mi_perfil.php?error=Error al actualizar la base de datos.");
        exit();
    }
}
?>