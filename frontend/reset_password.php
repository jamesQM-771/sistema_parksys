<?php require_once __DIR__ . '/_auth.php'; if (page_user()) { header('Location: index.php'); exit; } ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer contrasena</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="auth-wrap"><section class="panel auth-box">
    <h2>Restablecer contrasena</h2>
    <form id="formReset" class="form-grid">
      <label>Token<input type="text" name="token" required></label>
      <label>Nueva contrasena<input type="password" name="password" required minlength="6"></label>
      <button type="submit">Cambiar contrasena</button>
    </form>
    <div id="resultadoReset" class="result"></div>
    <p><a href="login.php">Volver al login</a></p>
  </section></main>
  <script src="assets/js/app.js"></script><script>initReset();</script>
</body>
</html>
