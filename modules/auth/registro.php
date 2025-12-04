<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    
    // Obtener la imagen
    $img_base64 = $_POST['biometria_base64'];

    if (empty($img_base64)) {
        header("Location: ../../registro.php?error=Falta la foto biométrica");
        exit();
    }

    // Procesar Imagen
    $img_base64 = str_replace('data:image/png;base64,', '', $img_base64);
    $img_base64 = str_replace(' ', '+', $img_base64);
    $data_img = base64_decode($img_base64);
    
    // Generar nombre único
    $nombre_archivo = 'ref_' . time() . '_' . uniqid() . '.png';
    $ruta_fisica = '../../public/uploads/biometria/' . $nombre_archivo;
    $ruta_db = 'public/uploads/biometria/' . $nombre_archivo;

    // Guardar archivo
    file_put_contents($ruta_fisica, $data_img);

    try {
        $pdo = (new Database())->connect();

        // Verificar si existe el email
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->rowCount() > 0) {
            header("Location: ../../registro.php?error=El email ya está registrado");
            exit();
        }

        // Insertar usuario con la foto de biometría
        $sql = "INSERT INTO usuarios (nombres, apellidos, email, password, rol, foto_biometria) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombres, $apellidos, $email, $password, $rol, $ruta_db]);

        header("Location: ../../login.php?exito=Cuenta creada. Ahora prueba el Login Facial.");

    } catch (PDOException $e) {
        header("Location: ../../registro.php?error=Error BD: " . $e->getMessage());
    }
}
?>