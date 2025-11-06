<?php
// 1. Iniciamos sesión y cargamos la BD
session_start();
require_once '../../config/database.php';

// 2. Cargamos la biblioteca FPDF
require_once '../../lib/fpdf/fpdf.php';

// 3. Verificación de Seguridad (RF02 - Solo Admin o Docente)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'docente'])) {
    die("Acceso no autorizado. Debe ser administrador o docente para generar reportes.");
}

// 4. Conexión a la BD
try {
    $pdo = (new Database())->connect();

    // 5. Consulta SQL para el reporte
    $stmt = $pdo->prepare("
        SELECT 
            u.nombres, 
            u.apellidos, 
            u.codigo,
            i.nombre_empresa,
            v.titulo_vacante,
            a.estado,
            a.fecha_inicio
        FROM aplicaciones a
        JOIN usuarios u ON a.id_estudiante = u.id
        JOIN vacantes v ON a.id_vacante = v.id
        JOIN instituciones i ON v.id_institucion = i.id
        WHERE a.estado IN ('Aprobada', 'En_Curso', 'Finalizada')
        ORDER BY i.nombre_empresa, u.apellidos
    ");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Creación del documento PDF
    $pdf = new FPDF('P', 'mm', 'A4'); 
    $pdf->AddPage();
    
    // 7. Título del Reporte
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Reporte de Prácticas Pre-Profesionales'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode('Prácticas Aprobadas, en Curso y Finalizadas'), 0, 1, 'C');
    $pdf->Cell(0, 8, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
    $pdf->Ln(10); 

    // 8. Encabezados de la tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(50, 8, 'Estudiante', 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Código'), 1, 0, 'C', true);
    $pdf->Cell(65, 8, 'Institucion / Empresa', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Estado', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Fecha Inicio', 1, 1, 'C', true);

    // 9. Contenido de la tabla (los datos)
    $pdf->SetFont('Arial', '', 9);
    foreach ($resultados as $row) {
        $nombreCompleto = utf8_decode($row['apellidos'] . ', ' . $row['nombres']);
        $empresa = utf8_decode($row['nombre_empresa']);
        $estado = $row['estado'];
        $fecha_inicio = $row['fecha_inicio'] ? date('d/m/Y', strtotime($row['fecha_inicio'])) : 'N/A';
        
        $pdf->Cell(50, 7, $nombreCompleto, 1);
        $pdf->Cell(25, 7, $row['codigo'], 1);
        $pdf->Cell(65, 7, $empresa, 1);
        $pdf->Cell(25, 7, $estado, 1, 0, 'C');
        $pdf->Cell(25, 7, $fecha_inicio, 1, 1, 'C');
    }
    
    // 10. Salida del PDF
    $pdf->Output('D', 'Reporte_Practicas_'.date('Y-m-d').'.pdf');
    exit;

} catch (PDOException $e) {
    die("Error en la base de datos al generar el reporte: " . $e->getMessage());
} catch (Exception $e) {
    // Esto podría pasar si FPDF falla
    die("Error al generar el PDF: " . $e->getMessage());
}
?>