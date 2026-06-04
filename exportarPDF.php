<?php
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

// Obtener tareas del usuario
if ($_SESSION['usuario_rol'] == 'admin') {
    $stmt = $pdo->prepare("SELECT tareas.*, usuarios.nombre as usuario_nombre 
                           FROM tareas 
                           LEFT JOIN usuarios ON tareas.usuario_id = usuarios.id 
                           ORDER BY tareas.fecha_creacion DESC");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT * FROM tareas WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$_SESSION['usuario_id']]);
}
$tareas = $stmt->fetchAll();

// Crear PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('TaskFlow');
$pdf->SetAuthor($_SESSION['usuario_nombre']);
$pdf->SetTitle('Mis Tareas');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

$html = '<h1 style="text-align:center">Mis Tareas</h1>';
$html .= '<p style="text-align:center">Usuario: ' . $_SESSION['usuario_nombre'] . '</p>';
$html .= '<p style="text-align:center">Fecha: ' . date('d/m/Y H:i:s') . '</p>';
$html .= '<br>';

if (empty($tareas)) {
    $html .= '<p>No hay tareas registradas.</p>';
} else {
    $html .= '<table border="1" cellpadding="5" style="width:100%; border-collapse:collapse;">';
    $html .= '<thead>
                 <tr style="background-color:#0d6efd; color:white;">
                     <th>Título</th>
                     <th>Descripción</th>
                     <th>Fecha Límite</th>
                     <th>Prioridad</th>
                     <th>Estado</th>
                  </tr>
              </thead>';
    $html .= '<tbody>';
    
    foreach($tareas as $tarea) {
        $prioridad_texto = match($tarea['prioridad'] ?? 'media') {
            'alta' => 'Alta',
            'media' => 'Media',
            'baja' => 'Baja',
        };
        
        $estado_texto = $tarea['estado'] == 'completada' ? 'Completada' : 'Pendiente';
        
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($tarea['titulo']) . '</td>';
        $html .= '<td>' . htmlspecialchars(substr($tarea['descripcion'] ?? '', 0, 100)) . '</td>';
        $html .= '<td>' . ($tarea['fecha_limite'] ?? 'Sin fecha') . '</td>';
        $html .= '<td>' . $prioridad_texto . '</td>';
        $html .= '<td>' . $estado_texto . '</td>';
        $html .= '</tr>';
        
        if ($_SESSION['usuario_rol'] == 'admin' && isset($tarea['usuario_nombre'])) {
            $html .= '<tr style="background-color:#f0f0f0;">';
            $html .= '<td colspan="5"><small>👤 Usuario: ' . htmlspecialchars($tarea['usuario_nombre']) . '</small></td>';
            $html .= '</tr>';
        }
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
}

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('mis_tareas.pdf', 'D');
?>