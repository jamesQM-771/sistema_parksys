<?php
require_once __DIR__ . '/_auth.php';
allow_roles_page(['ADMIN', 'SUPERADMIN']);
$role = page_user()['rol'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkSys - Admin</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="topbar">
    <h1>ParkSys</h1>
    <nav>
      <?php echo nav_link('index.php', 'Inicio', 'admin.php'); ?>
      <?php echo nav_link('entrada.php', 'Entrada', 'admin.php'); ?>
      <?php echo nav_link('salida.php', 'Salida', 'admin.php'); ?>
      <?php echo nav_link('reportes.php', 'Reportes', 'admin.php'); ?>
      <?php echo nav_link('admin.php', 'Admin', 'admin.php'); ?>
      <a href="logout.php">Cerrar sesion</a>
    </nav>
  </header>

  <main class="container">
    <section class="panel">
      <h2>Logo del sistema</h2>
      <form id="formLogo" class="form-grid compact" enctype="multipart/form-data">
        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" required>
        <button type="submit">Subir logo</button>
      </form>
      <div id="resultadoLogo" class="result"></div>
    </section>

    <section class="panel">
      <h2>Regla de cobro</h2>
      <form id="formModoCobro" class="form-grid compact">
        <select name="valor" id="modoCobroSelect">
          <option value="POR_MINUTO">Por minuto (proporcional)</option>
          <option value="POR_HORA">Por hora (redondeo arriba)</option>
        </select>
        <input type="number" name="minutos_gracia" id="minutosGracia" min="0" max="120" step="1" value="5" placeholder="Minutos de gracia">
        <button type="submit">Guardar regla</button>
      </form>
      <div id="resultadoModoCobro" class="result"></div>
    </section>

    <section class="panel">
      <h2>Simulador de cobro</h2>
      <form id="formSimuladorCobro" class="form-grid compact">
        <select name="categoria_id" id="simCategoria" required></select>
        <input type="number" name="minutos" min="1" step="1" placeholder="Minutos" required>
        <button type="submit">Simular</button>
      </form>
      <div id="resultadoSimuladorCobro" class="result"></div>
    </section>

    <?php if ($role === 'SUPERADMIN'): ?>
    <section class="panel">
      <h2>Usuarios</h2>
      <form id="formUsuario" class="form-grid compact">
        <input type="hidden" name="id"><input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Password (opcional editar)">
        <select name="rol"><option>OPERADOR</option><option>ADMIN</option><option>SUPERADMIN</option></select>
        <select name="activo"><option value="1">Activo</option><option value="0">Inactivo</option></select>
        <button type="submit">Guardar usuario</button>
      </form>
      <div id="resultadoUsuario" class="result"></div>
      <table><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Accion</th></tr></thead><tbody id="tablaUsuarios"></tbody></table>
    </section>
    <?php endif; ?>

    <section class="panel">
      <h2>Categorias y tarifas por hora</h2>
      <form id="formCategoria" class="form-grid compact">
        <input type="hidden" name="id"><input type="text" name="nombre" placeholder="Categoria" required>
        <input type="number" name="valor_hora" placeholder="Valor/hora" min="1" step="100" required>
        <select name="activo"><option value="1">Activa</option><option value="0">Inactiva</option></select>
        <button type="submit">Guardar categoria</button>
      </form>
      <div id="resultadoCategoria" class="result"></div>
      <table><thead><tr><th>ID</th><th>Categoria</th><th>Tarifa</th><th>Activo</th><th>Accion</th></tr></thead><tbody id="tablaCategorias"></tbody></table>
    </section>

    <section class="grid-two">
      <section class="panel">
        <h2>Marcas (locales)</h2>
        <form id="formMarca" class="form-grid compact">
          <input type="hidden" name="id"><input type="text" name="nombre" placeholder="Marca" required>
          <select name="activo"><option value="1">Activa</option><option value="0">Inactiva</option></select>
          <button type="submit">Guardar marca</button>
        </form>
        <div id="resultadoMarca" class="result"></div>
        <table><thead><tr><th>ID</th><th>Marca</th><th>Activo</th><th>Accion</th></tr></thead><tbody id="tablaMarcas"></tbody></table>
      </section>

      <section class="panel">
        <h2>Modelos (locales)</h2>
        <form id="formModelo" class="form-grid compact">
          <input type="hidden" name="id"><input type="text" name="nombre" placeholder="Modelo" required>
          <select name="marca_id" id="modeloMarca" required></select>
          <select name="categoria_id" id="modeloCategoria"></select>
          <select name="activo"><option value="1">Activo</option><option value="0">Inactivo</option></select>
          <button type="submit">Guardar modelo</button>
        </form>
        <div id="resultadoModelo" class="result"></div>
        <table><thead><tr><th>ID</th><th>Modelo</th><th>Marca</th><th>Categoria</th><th>Activo</th><th>Accion</th></tr></thead><tbody id="tablaModelos"></tbody></table>
      </section>
    </section>

    <section class="panel">
      <h2>Admin CarQuery (consulta global)</h2>
      <div class="actions">
        <button id="btnAdminCarqueryMakes" type="button">Cargar todas las marcas CarQuery</button>
        <input id="adminCarqueryMake" type="text" placeholder="Marca para ver modelos (ej: toyota)">
        <button id="btnAdminCarqueryModels" type="button">Cargar modelos de esa marca</button>
      </div>
      <div class="grid-two">
        <div>
          <h3>Marcas CarQuery</h3>
          <table><thead><tr><th>#</th><th>Marca</th></tr></thead><tbody id="tablaCarqueryMakes"></tbody></table>
        </div>
        <div>
          <h3>Modelos CarQuery</h3>
          <table><thead><tr><th>#</th><th>Modelo</th></tr></thead><tbody id="tablaCarqueryModels"></tbody></table>
        </div>
      </div>
    </section>

    <section class="panel">
      <h2>Mapa y ubicaciones</h2>
      <form id="formUbicacion" class="form-grid compact">
        <input type="hidden" name="id"><input type="text" name="codigo" placeholder="A01" required>
        <input type="text" name="zona" placeholder="A" required>
        <select name="estado"><option>LIBRE</option><option>OCUPADO</option><option>MANTENIMIENTO</option></select>
        <input type="text" name="observacion" placeholder="Observacion"><button type="submit">Guardar ubicacion</button>
      </form>
      <div id="resultadoUbicacion" class="result"></div>
      <table><thead><tr><th>ID</th><th>Codigo</th><th>Zona</th><th>Estado</th><th>Obs.</th><th>Accion</th></tr></thead><tbody id="tablaUbicaciones"></tbody></table>
    </section>

    <section class="panel">
      <h2>Auditoria de acciones</h2>
      <button id="btnAuditoria" type="button">Recargar auditoria</button>
      <table>
        <thead><tr><th>ID</th><th>Fecha</th><th>Usuario</th><th>Accion</th><th>Detalle</th><th>IP</th></tr></thead>
        <tbody id="tablaAuditoria"></tbody>
      </table>
    </section>
  </main>

  <script>window.PARKSYS_ROLE = '<?php echo $role; ?>';</script>
  <script src="assets/js/app.js"></script>
  <script>initAdmin();</script>
</body>
</html>
