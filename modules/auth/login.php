<?php
// 1. Importante de Iniciar la sesión ANTES de cualquier salida.
session_start();

// 2. Incluir la base de datos
require_once '../../config/database.php';

// 3. Verificar que se envíe por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Obtener datos
    $identificador = $_POST['email_o_codigo'];
    $password = $_POST['password'];

    if (empty($identificador) || empty($password)) {
        header("Location: ../../login.php?error=Email/Código y contraseña son requeridos");
        exit();
    }

    try {
        // 5. Conexión a la BD
        $pdo = (new Database())->connect();
        
        
        // 6. Buscar al usuario por email O por código
        $stmt = $pdo->prepare(
            "SELECT id, nombres, password, rol, activo 
             FROM usuarios 
             WHERE (email = ? OR codigo = ?)"
        );
        
        // Pasamos la variable $identificador dos veces, una para cada ?
        $stmt->execute([$identificador, $identificador]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 7. Verificar si el usuario existe Y si la contraseña es correcta
        if ($usuario && password_verify($password, $usuario['password'])) {
            
            // 8. Verificamos si la cuenta está activa
            if ($usuario['activo'] == 0) {
                 header("Location: ../../login.php?error=Tu cuenta está desactivada.");
                 exit();
            }

            // 9. ¡Éxito! Creamos las variables de sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombres'] = $usuario['nombres'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // 10. Redirigir según el ROL (Cumple RF02)
            if ($usuario['rol'] == 'admin') {
                header("Location: ../../dashboard_admin.php");
            } elseif ($usuario['rol'] == 'docente') {
                header("Location: ../../dashboard_docente.php");
            } else { // 'estudiante'
                header("Location: ../../dashboard_estudiante.php");
            }
            exit();

        } else {
            // Si $usuario es falso o password_verify falla
            header("Location: ../../login.php?error=Credenciales incorrectas.");
            exit();
        }

    } catch (PDOException $e) {
        
        // MODO DEBUG (Corregido de mi error de tipeo)
        die("¡Error de Debug! El problema real es: " . $e->getMessage());
        
        // header("Location: ../../login.php?error=Error de base de datos.");
        // exit();
    }

} else {
    // Si alguien intenta acceder a este archivo directamente
    header("Location: ../../login.php");
    exit();
}
?>