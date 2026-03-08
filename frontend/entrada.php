<?php
require_once __DIR__ . '/_auth.php';
require_login_page();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkSys - Entrada</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="topbar">
    <h1>ParkSys</h1>
    <nav>
      <?php echo nav_link('index.php', 'Inicio', 'entrada.php'); ?>
      <?php echo nav_link('entrada.php', 'Entrada', 'entrada.php'); ?>
      <?php echo nav_link('salida.php', 'Salida', 'entrada.php'); ?>
      <?php echo nav_link('reportes.php', 'Reportes', 'entrada.php'); ?>
      <?php if (in_array(page_user()['rol'], ['ADMIN', 'SUPERADMIN'], true)): ?>
        <?php echo nav_link('admin.php', 'Admin', 'entrada.php'); ?>
      <?php endif; ?>
      <a href="logout.php">Cerrar sesion</a>
    </nav>
  </header>

  <main class="container narrow">
    <section class="panel">
      <h2>Registro de entrada</h2>
      <form id="formEntrada" class="form-grid">
        <label>Placa
          <input type="text" name="placa" required maxlength="10" placeholder="ABC123">
        </label>

        <label>Categoria
          <select id="entradaCategoria" name="categoria_id" required></select>
        </label>

        <div class="grid-two">
          <label>Marca (CarQuery)
            <input list="carqueryMakes" id="entradaMarcaNombre" name="marca_nombre" placeholder="Ej: TOYOTA" required>
            <datalist id="carqueryMakes"></datalist>
          </label>
          <label>Modelo (CarQuery)
            <input list="carqueryModels" id="entradaModeloNombre" name="modelo_nombre" placeholder="Ej: COROLLA" required>
            <datalist id="carqueryModels"></datalist>
          </label>
        </div>
        <div class="actions">
          <button type="button" id="btnCarqueryMakes">Cargar marcas CarQuery</button>
          <button type="button" id="btnCarqueryModels">Cargar modelos CarQuery</button>
        </div>

        <label>Color
          <input type="text" name="color" maxlength="40" placeholder="Rojo" required>
        </label>
        <label>Ubicacion (mapa)
          <select id="entradaUbicacion" name="ubicacion_id" required></select>
        </label>
        <button type="submit">Registrar entrada</button>
      </form>
      <div id="resultadoEntrada" class="result"></div>
      <div id="ticketAccion" class="actions"></div>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
  <script>initEntradaV2();</script>
</body>
</html>
