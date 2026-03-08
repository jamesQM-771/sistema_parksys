<?php
require_once __DIR__ . '/_auth.php';
require_login_page();
$ticket = strtoupper(trim($_GET['ticket'] ?? ''));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket de entrada <?php echo htmlspecialchars($ticket); ?></title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="receipt-page">
  <main class="receipt-wrap">
    <section class="receipt" id="ticketPanel">
      <header class="receipt-head">
        <img id="receiptLogo" class="receipt-logo" src="assets/img/logo-default.png" alt="Logo ParkSys">
        <div>
          <h1>ParkSys</h1>
          <p>Sistema de parqueadero</p>
        </div>
      </header>

      <div class="receipt-title">TICKET DE INGRESO</div>
      <div class="receipt-date" id="ticketFecha">--</div>
      <hr>

      <div class="receipt-rows" id="ticketEntradaContenido">Cargando...</div>

      <hr>
      <div class="receipt-total">Tarifa/h: <span id="ticketTarifa">--</span></div>

      <div class="barcode" id="ticketBarcode"></div>
      <div class="barcode-code" id="ticketCodeText">--</div>
      <p class="receipt-foot">Conserve este ticket para validar la salida</p>
    </section>

    <div class="actions receipt-actions no-print">
      <button onclick="window.print()">Imprimir ticket</button>
      <a class="btnlink" href="entrada.php">Volver</a>
    </div>
  </main>
  <script src="assets/js/app.js"></script>
  <script>initTicketEntrada('<?php echo htmlspecialchars($ticket); ?>');</script>
</body>
</html>
