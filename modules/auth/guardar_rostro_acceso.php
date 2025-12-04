<?php
session_start();
require_once '../../config/database.php';

// Solo aceptamos peticiones POST con datos JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['imagen']) && isset($_SESSION['usuario_id'])) {
    
    // 1. Obtener la imagen en Base64 (viene como texto largo)
    $imagen_base64 = $data['imagen'];
    
    // 2. Limpiar la cabecera del string (data:image/png;base64,...)
    $imagen_base64 = str_replace('data:image/png;base64,', '', $imagen_base64);
    $imagen_base64 = str_replace(' ', '+', $imagen_base64);
    
    // 3. Decodificar a archivo binario
    $imagen_binaria = base64_decode($imagen_base64);
    
    // 4. Crear un nombre único: ID_FECHA_HORA.png
    $nombre_archivo = 'acceso_' . $_SESSION['usuario_id'] . '_' . date('Ymd_His') . '.png';
    $ruta_guardado = '../../public/uploads/accesos/' . $nombre_archivo;
    
    // 5. Guardar el archivo en la carpeta
    if (file_put_contents($ruta_guardado, $imagen_binaria)) {
        
        // (Opcional) Aquí podrías guardar un registro en la BD: "INSERT INTO historial_accesos..."
        
        echo json_encode(['success' => true, 'mensaje' => 'Rostro guardado']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al guardar archivo']);
    }
    
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
}
?>