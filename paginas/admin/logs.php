<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

// Logs simulados (en producción podrías guardar acciones reales)
$logs = [
  ["fecha"=>"2025-11-03 10:15", "usuario"=>"Nico", "accion"=>"Creó un servidor"],
  ["fecha"=>"2025-11-03 10:20", "usuario"=>"Steve", "accion"=>"Inició su servidor"],
  ["fecha"=>"2025-11-03 10:25", "usuario"=>"Admin", "accion"=>"Actualizó un plan"],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Logs del sistema - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/dashboard.css">
</head>
<body>
<div class="container">
  <h2>📜 Logs del sistema</h2>
  <table>
    <tr><th>Fecha</th><th>Usuario</th><th>Acción</th></tr>
    <?php foreach($logs as $l): ?>
    <tr>
      <td><?= $l["fecha"]; ?></td>
      <td><?= $l["usuario"]; ?></td>
      <td><?= $l["accion"]; ?></td>
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
