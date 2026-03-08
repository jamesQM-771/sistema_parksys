<?php
require_once __DIR__ . '/_auth.php';
require_login_page();
$codigo = strtoupper(trim($_GET['codigo'] ?? ''));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Factura <?php echo htmlspecialchars($codigo); ?></title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="receipt-page">
  <main class="receipt-wrap">
    <section class="receipt" id="facturaPanel">
      <header class="receipt-head">
        <img id="receiptLogo" class="receipt-logo" src="assets/img/logo-default.png" alt="Logo ParkSys">
        <div>
          <h1>ParkSys</h1>
          <p>Sistema de parqueadero</p>
        </div>
      </header>

      <div class="receipt-title">PAID PARKING</div>
      <div class="receipt-date" id="facturaFecha">--</div>
      <hr>

      <div class="receipt-rows" id="facturaContenido">Cargando...</div>

      <hr>
      <div class="receipt-total">Pagado: <span id="facturaTotal">--</span></div>

      <div class="barcode" id="facturaBarcode"></div>
      <div class="barcode-code" id="facturaCodeText">--</div>
      <p class="receipt-foot">Gracias por preferir ParkSys</p>
    </section>

    <div class="actions receipt-actions no-print">
      <button onclick="window.print()">Imprimir</button>
      <a class="btnlink" href="reportes.php">Volver</a>
    </div>
  </main>
  <script src="assets/js/app.js"></script>
  <script>initFactura('<?php echo htmlspecialchars($codigo); ?>');</script>
</body>
</html>
