<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers.php';

require_roles(['ADMIN', 'SUPERADMIN']);

$tipo = strtolower(trim($_GET['tipo'] ?? 'diario'));
$formato = strtolower(trim($_GET['formato'] ?? 'excel'));

$tipos = ['diario', 'semanal', 'mensual'];
if (!in_array($tipo, $tipos, true)) {
    $tipo = 'diario';
}
if (!in_array($formato, ['excel', 'pdf'], true)) {
    $formato = 'excel';
}

if ($tipo === 'diario') {
    $filtro = 'DATE(rp.hora_salida) = CURDATE()';
} elseif ($tipo === 'semanal') {
    $filtro = 'YEARWEEK(rp.hora_salida, 1) = YEARWEEK(CURDATE(), 1)';
} else {
    $filtro = 'YEAR(rp.hora_salida)=YEAR(CURDATE()) AND MONTH(rp.hora_salida)=MONTH(CURDATE())';
}

$sql = "
    SELECT rp.id, rp.ticket_codigo, rp.factura_codigo,
           v.placa, c.nombre categoria, u.codigo ubicacion,
           rp.hora_entrada, rp.hora_salida, rp.total_minutos, rp.valor_total,
           p.metodo
    FROM registros_parqueo rp
    INNER JOIN vehiculos v ON v.id = rp.vehiculo_id
    INNER JOIN categorias_vehiculo c ON c.id = v.categoria_id
    INNER JOIN ubicaciones u ON u.id = rp.ubicacion_id
    LEFT JOIN pagos p ON p.registro_id = rp.id
    WHERE rp.estado='FINALIZADO' AND $filtro
    ORDER BY rp.hora_salida DESC
";
$rows = $pdo->query($sql)->fetchAll();

if ($formato === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Placa', 'Categoria', 'Ubicacion', 'Ticket', 'Factura', 'Entrada', 'Salida', 'Minutos', 'Metodo', 'Valor']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['placa'], $r['categoria'], $r['ubicacion'], $r['ticket_codigo'], $r['factura_codigo'], $r['hora_entrada'], $r['hora_salida'], $r['total_minutos'], $r['metodo'], $r['valor_total']]);
    }
    fclose($out);
    audit_log($pdo, 'EXPORT_EXCEL', 'Tipo: ' . $tipo);
    exit;
}

$total = array_reduce($rows, fn($c, $i) => $c + (float)$i['valor_total'], 0.0);
audit_log($pdo, 'EXPORT_PDF_PRINT', 'Tipo: ' . $tipo);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte <?php echo htmlspecialchars($tipo); ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 18px; }
    h1 { margin-bottom: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
    th, td { border: 1px solid #999; padding: 4px; text-align: left; }
    .meta { margin: 6px 0; }
    @media print { .noprint { display:none; } }
  </style>
</head>
<body>
  <button class="noprint" onclick="window.print()">Imprimir / Guardar PDF</button>
  <h1>Reporte <?php echo strtoupper(htmlspecialchars($tipo)); ?></h1>
  <p class="meta">Generado: <?php echo date('Y-m-d H:i:s'); ?> | Registros: <?php echo count($rows); ?> | Total: <?php echo number_format($total, 0, ',', '.'); ?></p>
  <table>
    <thead><tr><th>ID</th><th>Placa</th><th>Categoria</th><th>Ubicacion</th><th>Ticket</th><th>Factura</th><th>Entrada</th><th>Salida</th><th>Min</th><th>Metodo</th><th>Valor</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['id']); ?></td>
        <td><?php echo htmlspecialchars($r['placa']); ?></td>
        <td><?php echo htmlspecialchars($r['categoria']); ?></td>
        <td><?php echo htmlspecialchars($r['ubicacion']); ?></td>
        <td><?php echo htmlspecialchars($r['ticket_codigo']); ?></td>
        <td><?php echo htmlspecialchars($r['factura_codigo']); ?></td>
        <td><?php echo htmlspecialchars($r['hora_entrada']); ?></td>
        <td><?php echo htmlspecialchars($r['hora_salida']); ?></td>
        <td><?php echo htmlspecialchars($r['total_minutos']); ?></td>
        <td><?php echo htmlspecialchars($r['metodo']); ?></td>
        <td><?php echo htmlspecialchars($r['valor_total']); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
