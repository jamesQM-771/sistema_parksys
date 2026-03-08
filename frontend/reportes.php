<?php
require_once __DIR__ . '/_auth.php';
require_login_page();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkSys - Reportes</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="topbar">
    <h1>ParkSys</h1>
    <nav>
      <?php echo nav_link('index.php', 'Inicio', 'reportes.php'); ?>
      <?php echo nav_link('entrada.php', 'Entrada', 'reportes.php'); ?>
      <?php echo nav_link('salida.php', 'Salida', 'reportes.php'); ?>
      <?php echo nav_link('reportes.php', 'Reportes', 'reportes.php'); ?>
      <?php if (in_array(page_user()['rol'], ['ADMIN', 'SUPERADMIN'], true)): ?>
        <?php echo nav_link('admin.php', 'Admin', 'reportes.php'); ?>
      <?php endif; ?>
      <a href="logout.php">Cerrar sesion</a>
    </nav>
  </header>

  <main class="container">
    <section class="panel">
      <h2>Reporte operativo (entrada y salida)</h2>
      <div class="filters">
        <select id="tipoReporte">
          <option value="diario">Diario</option>
          <option value="semanal">Semanal</option>
          <option value="mensual">Mensual</option>
        </select>
        <button id="btnCargarReporte">Cargar</button>
        <a id="btnExcel" class="btnlink" target="_blank" href="#">Exportar Excel</a>
        <a id="btnPdf" class="btnlink" target="_blank" href="#">Exportar PDF</a>
      </div>
      <p id="resumenReporte"></p>
      <div class="table-wrap">
        <table class="table-reportes">
          <thead>
            <tr>
              <th>ID</th><th>Estado</th><th>Placa</th><th>Marca</th><th>Modelo</th><th>Color</th><th>Categoria</th><th>Ubicacion</th><th>Ticket</th><th>Factura</th><th>Entrada</th><th>Salida</th><th>Min</th><th>Metodo</th><th>Valor</th><th>Accion</th>
            </tr>
          </thead>
          <tbody id="tablaReporte"></tbody>
        </table>
      </div>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
  <script>initReportesV2();</script>
</body>
</html>
