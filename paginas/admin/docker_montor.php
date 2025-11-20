<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

// Datos simulados (en producción vendrían de `docker stats` o la tabla docker_stats)
$containers = [
  ["name" => "srv_mine_nico", "status" => "running", "cpu" => "23%", "mem" => "1.5 GB"],
  ["name" => "srv_mine_alex", "status" => "stopped", "cpu" => "0%", "mem" => "—"],
  ["name" => "srv_mine_steve", "status" => "running", "cpu" => "18%", "mem" => "2.1 GB"],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Monitor Docker - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
</head>
<body>
<?php include_once "../../comun/navbar.php"; ?>
<div class="container">
  <h2>🐳 Monitor de contenedores Docker</h2>
  <table>
    <tr><th>Nombre del contenedor</th><th>Estado</th><th>CPU</th><th>Memoria</th></tr>
    <?php foreach($containers as $c): ?>
    <tr>
      <td><?php echo $c["name"]; ?></td>
      <td><?php echo $c["status"] === "running" ? "🟢 Activo" : "🔴 Parado"; ?></td>
      <td><?php echo $c["cpu"]; ?></td>
      <td><?php echo $c["mem"]; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p><a href="panel_admin.php">⬅ Volver al panel</a></p>
</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
