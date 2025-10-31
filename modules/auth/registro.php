<?php
// 1. Incluimos el archivo de conexión
require_once '../../config/database.php';

// 2. Verificamos que los datos se envíen por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Obtenemos y limpiamos los datos del formulario
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $codigo = trim($_POST['codigo']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // 4. Validaciones
    if (empty($nombres) || empty($apellidos) || empty($codigo) || empty($email) || empty($password)) {
        // Redirigimos de vuelta al formulario de registro 
        header("Location: ../../registro.php?error=Todos los campos son obligatorios");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../registro.php?error=El formato del email no es válido");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: ../../registro.php?error=La contraseña debe tener al menos 6 caracteres");
        exit();
    }

    // 5. Hashear la contraseña 
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // 6. Conexión a la BD
    $pdo = (new Database())->connect();
    
    try {
        // 7. Verificamos si el email o el código ya existen
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR codigo = ?");
        $stmt_check->execute([$email, $codigo]);
        
        if ($stmt_check->fetch()) {
            header("Location: ../../registro.php?error=El email o el código de estudiante ya están registrados");
            exit();
        }

        // 8. Insertamos el nuevo usuario (rol 'estudiante' por defecto)
        $stmt_insert = $pdo->prepare(
            "INSERT INTO usuarios (nombres, apellidos, codigo, email, password, rol) 
             VALUES (?, ?, ?, ?, ?, 'estudiante')"
        );
        
        $stmt_insert->execute([$nombres, $apellidos, $codigo, $email, $password_hash]);

        // 9. Redirigimos al login con mensaje de éxito
        header("Location: ../../login.php?exito=Registro completado. Ahora puedes iniciar sesión.");
        exit();

    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        header("Location: ../../registro.php?error=Error en la base de datos, intente más tarde.");
        exit();
    }

} else {
    // Si alguien intenta acceder a este archivo directamente, lo mandamos al registro
    header("Location: ../../registro.php");
    exit();
}
?>