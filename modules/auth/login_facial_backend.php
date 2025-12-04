<?php
// 1. Iniciar sesión obligatoriamente
session_start();

// 2. Conectar a la base de datos
require_once '../../config/database.php';

// 3. Obtener el email. Usamos $_REQUEST para que acepte tanto GET (URL) como POST.
// Esto es necesario porque el javascript hace una redirección con ?email=...
if (isset($_REQUEST['email'])) {
    
    $email = $_REQUEST['email'];

    try {
        $pdo = (new Database())->connect();

        // 4. Buscar al usuario por su email
        $stmt = $pdo->prepare("SELECT id, nombres, rol, activo FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            
            // 5. Verificar si la cuenta está activa
            if ($usuario['activo'] == 0) {
                header("Location: ../../login.php?error=Tu cuenta ha sido desactivada.");
                exit();
            }

            // 6. ¡LOGIN EXITOSO! - Guardar datos en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombres'] = $usuario['nombres'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // 7. Redirigir al Dashboard correcto
            // Como ya pasó la prueba biométrica estricta en el paso anterior,
            // entran directo (sin pasar por la cámara de nuevo).
            
            switch ($usuario['rol']) {
                case 'admin':
                    header("Location: ../../dashboard_admin.php");
                    break;
                case 'docente':
                    header("Location: ../../dashboard_docente.php");
                    break;
                case 'estudiante':
                    header("Location: ../../dashboard_estudiante.php");
                    break;
                default:
                    header("Location: ../../login.php?error=Rol de usuario desconocido");
                    break;
            }
            exit();

        } else {
            // Si por alguna razón el email no existe (raro si ya pasó la validación visual)
            header("Location: ../../login.php?error=Usuario no encontrado.");
            exit();
        }

    } catch (Exception $e) {
        header("Location: ../../login.php?error=Error de conexión con la base de datos.");
        exit();
    }

} else {
    // Si alguien intenta abrir este archivo directamente sin pasar parámetros
    header("Location: ../../login.php");
    exit();
}
?>