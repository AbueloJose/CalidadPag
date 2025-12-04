<?php
// 1. Iniciar sesión
session_start();
require_once '../../config/database.php';

// 2. Verificar que se envíen datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $identificador = $_POST['email_o_codigo'];
    $password = $_POST['password'];

    // 3. Validar campos vacíos
    if (empty($identificador) || empty($password)) {
        header("Location: ../../login.php?error=Por favor, llena todos los campos");
        exit();
    }

    try {
        // 4. Conectar a la BD
        $pdo = (new Database())->connect();
        
        // 5. Buscar usuario por email o código
        $stmt = $pdo->prepare("SELECT id, nombres, password, rol, activo FROM usuarios WHERE (email = ? OR codigo = ?)");
        $stmt->execute([$identificador, $identificador]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 6. Verificar contraseña y usuario existente
        if ($usuario && password_verify($password, $usuario['password'])) {
            
            // 7. Verificar si está activo
            if ($usuario['activo'] == 0) {
                 header("Location: ../../login.php?error=Tu cuenta está desactivada");
                 exit();
            }

            // 8. Crear variables de sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombres'] = $usuario['nombres'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // 9. LÓGICA DE REDIRECCIÓN (Aquí está la magia)
            
            // Si es ESTUDIANTE -> Entra directo a su panel
            if ($usuario['rol'] == 'estudiante') {
                header("Location: ../../dashboard_estudiante.php");
            } 
            // Si es ADMIN o DOCENTE -> Los mandamos a la cámara
            else {
                header("Location: ../../verificacion_facial.php");
            }
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: ../../login.php?error=Credenciales incorrectas");
            exit();
        }

    } catch (PDOException $e) {
        header("Location: ../../login.php?error=Error de conexión");
        exit();
    }

} else {
    // Si intentan entrar directo sin formulario
    header("Location: ../../login.php");
    exit();
}
?>