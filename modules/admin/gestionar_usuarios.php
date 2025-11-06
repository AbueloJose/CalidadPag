<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Verificación de Seguridad (RF02 - Solo Admin)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    // Si no es admin, lo sacamos
    header("Location: ../../login.php?error=Acceso no autorizado");
    exit();
}

$pdo = (new Database())->connect();

// 3. Verificamos que se envíe una acción por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {

    $accion = $_POST['accion'];

    // 4. Usamos un switch para manejar las diferentes acciones
    switch ($accion) {
        
        // --- ACCIÓN: CREAR NUEVO USUARIO ---
        case 'crear':
            $nombres = trim($_POST['nombres']);
            $apellidos = trim($_POST['apellidos']);
            $codigo = trim($_POST['codigo']) ?: null; // Permite código nulo (para docentes/admin)
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $rol = trim($_POST['rol']); // 'estudiante', 'docente', 'admin'

            // Validaciones
            if (empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($rol)) {
                header("Location: ../../admin_gestionar_usuarios.php?error=Todos los campos son requeridos");
                exit();
            }

            // Hashear contraseña
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (nombres, apellidos, codigo, email, password, rol) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$nombres, $apellidos, $codigo, $email, $password_hash, $rol]);
                header("Location: ../../admin_gestionar_usuarios.php?exito=Usuario creado correctamente");
                
            } catch (PDOException $e) {
                // Error de email/código duplicado
                if ($e->errorInfo[1] == 1062) { 
                    header("Location: ../../admin_gestionar_usuarios.php?error=El email o código ya existe");
                } else {
                    header("Location: ../../admin_gestionar_usuarios.php?error=Error al crear usuario");
                }
            }
            break;

        // --- ACCIÓN: EDITAR USUARIO ---
        case 'editar':
            $id_usuario = $_POST['id_usuario'];
            $nombres = trim($_POST['nombres']);
            $apellidos = trim($_POST['apellidos']);
            $codigo = trim($_POST['codigo']) ?: null;
            $email = trim($_POST['email']);
            $rol = trim($_POST['rol']);
            $password = trim($_POST['password']); // Contraseña (opcional)

            if (empty($id_usuario) || empty($nombres) || empty($apellidos) || empty($email) || empty($rol)) {
                header("Location: ../../admin_gestionar_usuarios.php?error=Campos requeridos vacíos");
                exit();
            }

            try {
                // Si el campo contraseña NO está vacío, la actualizamos
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare(
                        "UPDATE usuarios SET nombres = ?, apellidos = ?, codigo = ?, email = ?, rol = ?, password = ? 
                         WHERE id = ?"
                    );
                    $stmt->execute([$nombres, $apellidos, $codigo, $email, $rol, $password_hash, $id_usuario]);
                } else {
                    // Si la contraseña está vacía, no la actualizamos
                    $stmt = $pdo->prepare(
                        "UPDATE usuarios SET nombres = ?, apellidos = ?, codigo = ?, email = ?, rol = ? 
                         WHERE id = ?"
                    );
                    $stmt->execute([$nombres, $apellidos, $codigo, $email, $rol, $id_usuario]);
                }
                header("Location: ../../admin_gestionar_usuarios.php?exito=Usuario actualizado");

            } catch (PDOException $e) {
                header("Location: ../../admin_gestionar_usuarios.php?error=Error al actualizar: " . $e->getMessage());
            }
            break;

        // --- ACCIÓN: CAMBIAR ESTADO (ACTIVAR/DESACTIVAR) ---
        case 'cambiar_estado':
            $id_usuario = $_POST['id_usuario'];
            $nuevo_estado = $_POST['nuevo_estado']; // Será 0 o 1

            if (!isset($id_usuario) || !isset($nuevo_estado)) {
                header("Location: ../../admin_gestionar_usuarios.php?error=Datos incompletos");
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $id_usuario]);
            
            $mensaje = $nuevo_estado == 1 ? "Usuario activado" : "Usuario desactivado";
            header("Location: ../../admin_gestionar_usuarios.php?exito=" . $mensaje);
            break;
            
        // --- DEFAULT ---
        default:
            header("Location: ../../admin_gestionar_usuarios.php?error=Acción no válida");
            break;
    }
    
    exit();

} else {
    // Si no es POST o no hay acción
    header("Location: ../../dashboard_admin.php");
    exit();
}
?>