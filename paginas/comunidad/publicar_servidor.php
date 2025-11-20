<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$user_id = $_SESSION["user_id"];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre = mysqli_real_escape_string($conn, $_POST["nombre"]);
  $descripcion = mysqli_real_escape_string($conn, $_POST["descripcion"]);
  $ip = mysqli_real_escape_string($conn, $_POST["ip"]);
  $version = mysqli_real_escape_string($conn, $_POST["version"]);

  if ($nombre && $descripcion && $ip) {
    mysqli_query($conn, "
      INSERT INTO public_servers (user_id, nombre, descripcion, ip, version, created_at)
      VALUES ($user_id, '$nombre', '$descripcion', '$ip', '$version', NOW())
    ");
    $msg = "✅ Servidor publicado correctamente.";
  } else {
    $msg = "❌ Todos los campos son obligatorios.";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📢 Publicar servidor</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<style>
body {
  background:#121212; color:#fff;
  font-family:"Segoe UI"; margin:0; padding:0;
}
.container {
  max-width:700px;
  margin:100px auto;
  background:#1E1E1E;
  padding:25px;
  border-radius:16px;
  box-shadow:0 6px 18px rgba(0,0,0,0.5);
}
h2 { text-align:center; color:#4FC3F7; }
label { color:#4FC3F7; display:block; margin-top:10px; }
input, textarea {
  width:100%; background:#222; color:#fff;
  border:none; border-radius:8px; padding:8px;
}
button {
  margin-top:15px; background:#4FC3F7; color:#000;
  border:none; border-radius:8px; padding:10px 16px;
  font-weight:bold; cursor:pointer;
}
.msg { text-align:center; color:#4FC3F7; font-weight:bold; }
</style>
</head>
<body>
<?php include "../../comun/navbar.php"; ?>

<div class="container">
  <h2>📢 Publicar Servidor</h2>
  <?php if($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>

  <form method="POST">
    <label>Nombre del servidor</label>
    <input type="text" name="nombre" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4" required></textarea>

    <label>Dirección IP</label>
    <input type="text" name="ip" placeholder="mc.ejemplo.com:25565" required>

    <label>Versión (opcional)</label>
    <input type="text" name="version" placeholder="1.20.1">

    <button type="submit">Publicar servidor</button>
  </form>

  <p style="text-align:center;margin-top:15px;">
    <a href="servidores.php" style="color:#4FC3F7;">⬅ Volver</a>
  </p>
</div>

<?php include "../../comun/chatbot.php"; ?>
</body>
</html>
