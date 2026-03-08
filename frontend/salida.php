<?php
require_once __DIR__ . '/_auth.php';
require_login_page();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkSys - Salida</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="topbar">
    <h1>ParkSys</h1>
    <nav>
      <?php echo nav_link('index.php', 'Inicio', 'salida.php'); ?>
      <?php echo nav_link('entrada.php', 'Entrada', 'salida.php'); ?>
      <?php echo nav_link('salida.php', 'Salida', 'salida.php'); ?>
      <?php echo nav_link('reportes.php', 'Reportes', 'salida.php'); ?>
      <?php if (in_array(page_user()['rol'], ['ADMIN', 'SUPERADMIN'], true)): ?>
        <?php echo nav_link('admin.php', 'Admin', 'salida.php'); ?>
      <?php endif; ?>
      <a href="logout.php">Cerrar sesion</a>
    </nav>
  </header>

  <main class="container narrow">
    <section class="panel">
      <h2>Registro de salida</h2>
      <form id="formSalida" class="form-grid">
        <label>Placa
          <input type="text" name="placa" required maxlength="10" placeholder="ABC123">
        </label>
        <label>Ticket de ingreso
          <input type="text" name="ticket_codigo" required maxlength="20" placeholder="TKXXXXXX">
        </label>
        <label>Metodo de pago
          <select name="metodo">
            <option value="EFECTIVO">Efectivo</option>
            <option value="TRANSFERENCIA">Transferencia</option>
            <option value="TARJETA">Tarjeta</option>
            <option value="QR">QR</option>
          </select>
        </label>
        <div class="actions">
          <button type="button" id="btnCalcular">Calcular pago</button>
          <button type="submit">Registrar salida y factura</button>
        </div>
      </form>
      <div id="resultadoSalida" class="result"></div>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
  <script>initSalidaV2();</script>
</body>
</html>
