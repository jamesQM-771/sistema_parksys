<?php
require_once __DIR__ . '/_auth.php';
if (page_user()) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkSys - Login</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="auth-wrap">
    <section class="panel auth-box">
      <h1>ParkSys</h1>
      <p>Iniciar sesion</p>
      <form id="formLogin" class="form-grid">
        <label>Email
          <input type="email" name="email" required placeholder="*********@example.com">
        </label>
        <label>Password
          <input type="password" name="password" required placeholder="*********">
        </label>
        <button type="submit">Entrar</button>
      </form>
      <div id="resultadoLogin" class="result"></div>
      <p><a href="forgot_password.php">Olvide mi contraseña</a></p>
    </section>
  </main>
  <script src="assets/js/app.js"></script>
  <script>initLogin();</script>
</body>
</html>
