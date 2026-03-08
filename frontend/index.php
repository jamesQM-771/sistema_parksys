<?php
require_once __DIR__ . '/_auth.php';
require_login_page();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkSys - Dashboard</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="topbar">
    <h1>ParkSys</h1>
    <nav>
      <?php echo nav_link('index.php', 'Inicio', 'index.php'); ?>
      <?php echo nav_link('entrada.php', 'Entrada', 'index.php'); ?>
      <?php echo nav_link('salida.php', 'Salida', 'index.php'); ?>
      <?php echo nav_link('reportes.php', 'Reportes', 'index.php'); ?>
      <?php if (in_array(page_user()['rol'], ['ADMIN', 'SUPERADMIN'], true)): ?>
        <?php echo nav_link('admin.php', 'Admin', 'index.php'); ?>
      <?php endif; ?>
      <a href="logout.php">Cerrar sesion</a>
    </nav>
  </header>

  <main class="container">
    <section class="panel welcome">
      <h2>Bienvenido, <?php echo htmlspecialchars(page_user()['nombre']); ?></h2>
      <p>Rol: <?php echo htmlspecialchars(page_user()['rol']); ?></p>
    </section>

    <section class="grid-cards">
      <article class="card"><h3>Cupos totales</h3><p id="totalCupos">-</p></article>
      <article class="card"><h3>Ocupados</h3><p id="ocupados">-</p></article>
      <article class="card success"><h3>Disponibles</h3><p id="disponibles">-</p></article>
      <article class="card"><h3>Salidas hoy</h3><p id="salidasHoy">-</p></article>
      <article class="card"><h3>Ingresos hoy</h3><p id="ingresosHoy">-</p></article>
    </section>

    <section class="grid-two">
      <section class="panel">
        <h3>Mapa de ocupacion por zona</h3>
        <table>
          <thead><tr><th>Zona</th><th>Ocupados</th><th>Total</th></tr></thead>
          <tbody id="tablaZona"></tbody>
        </table>
      </section>

      <section class="panel">
        <h3>Vehiculos activos por categoria</h3>
        <table>
          <thead><tr><th>Categoria</th><th>Cantidad</th></tr></thead>
          <tbody id="tablaCategoriaActiva"></tbody>
        </table>
      </section>
    </section>

    <section class="panel">
      <h3>Vehiculos activos</h3>
      <table>
        <thead><tr><th>Placa</th><th>Categoria</th><th>Ubicacion</th><th>Entrada</th><th>Ticket</th></tr></thead>
        <tbody id="tablaActivos"></tbody>
      </table>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
  <script>initDashboard();</script>
</body>
</html>
