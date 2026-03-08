<?php require_once __DIR__ . '/_auth.php'; if (page_user()) { header('Location: index.php'); exit; } ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contrasena</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="auth-wrap"><section class="panel auth-box">
    <h2>Recuperar contrasena</h2>
    <form id="formForgot" class="form-grid">
      <label>Email<input type="email" name="email" required></label>
      <button type="submit">Generar token</button>
    </form>
    <div id="resultadoForgot" class="result"></div>
    <p><a href="reset_password.php">Ya tengo token</a> | <a href="login.php">Volver al login</a></p>
  </section></main>
  <script src="assets/js/app.js"></script><script>initForgot();</script>
</body>
</html>
