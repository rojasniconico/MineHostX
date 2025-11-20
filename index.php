<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>MineHostX - Hosting de Servidores Minecraft</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body {
      background: linear-gradient(180deg, #111 0%, #1e1e1e 100%);
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
      text-align: center;
      margin: 0;
    }
    header {
      padding: 20px;
      background: #4FC3F7;
      color: #000;
    }
    .hero {
      padding: 60px 20px;
    }
    .hero h1 { font-size: 2.5em; }
    .hero p { max-width: 700px; margin: 10px auto; line-height: 1.5; }
    .buttons a {
      display: inline-block;
      background: #4FC3F7;
      color: #000;
      padding: 12px 24px;
      margin: 10px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }
    footer {
      background: #1e1e1e;
      padding: 20px;
      color: #aaa;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
<?php include_once "comun/header.php"; ?>

  <header>
    <h2>☁️ MineHostX</h2>
  </header>

  <section class="hero">
    <h1>Hospeda tu servidor de Minecraft con un clic</h1>
    <p>
      Crea, gestiona y personaliza tu servidor de Minecraft directamente desde tu navegador.
      <br>Sin complicaciones, sin instalación, solo diversión.
    </p>
    <div class="buttons">
      <?php if (!isset($_SESSION["user_id"])): ?>
        <a href="autenticacion/registro.php">Registrarse</a>
        <a href="autenticacion/login.php">Iniciar sesión</a>
      <?php else: ?>
        <a href="paginas/usuario/panel.php">Ir al panel</a>
        <a href="autenticacion/cerrar_sesion.php">Cerrar sesión</a>
      <?php endif; ?>
    </div>
  </section>


<?php include_once "comun/footer.php"; ?>
   

</body>
</html>
