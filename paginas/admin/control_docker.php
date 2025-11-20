<?php
session_start();
include_once "../../db.php";
include_once "../../comun/navbar.php";
if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$containers = [
  ["name"=>"srv_mine_nico", "status"=>"running"],
  ["name"=>"srv_mine_steve", "status"=>"stopped"],
  ["name"=>"srv_mine_alex", "status"=>"running"],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Control Docker - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/dashboard.css">
</head>
<body>
<div class="container">
  <h2>🐳 Control Docker</h2>
  <table>
    <tr><th>Contenedor</th><th>Estado</th><th>Acciones</th></tr>
    <?php foreach($containers as $c): ?>
    <tr>
      <td><?= $c["name"]; ?></td>
      <td><?= $c["status"] === "running" ? "🟢 Activo" : "🔴 Parado"; ?></td>
      <td>
        <button class="btn" onclick="accion('<?= $c["name"]; ?>','start')">▶️ Iniciar</button>
        <button class="btn" onclick="accion('<?= $c["name"]; ?>','stop')">⏹️ Detener</button>
        <button class="btn" onclick="accion('<?= $c["name"]; ?>','restart')">🔁 Reiniciar</button>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <textarea id="res" style="width:90%;height:150px;background:#000;color:#0f0;border-radius:8px;padding:10px;"></textarea>
</div>
<script>
async function accion(name,action){
  const data = new FormData();
  data.append('server_id', 1);
  data.append('action', action);
  const r = await fetch('../../api/control_docker.php',{method:'POST',body:data});
  document.getElementById('res').value = await r.text();
}
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
