<?php
// 1. Iniciar la sesión para poder acceder a ella
session_start();

// 2. Limpiar todas las variables de sesión
$_SESSION = array();

// 3. Destruir la sesión (elimina el archivo de sesión en el servidor)
session_destroy();

// 4. Redirigir al login con un mensaje de éxito
header("Location: ../../login.php?exito=Has cerrado sesión correctamente.");
exit();
?>