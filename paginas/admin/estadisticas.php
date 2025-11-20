<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))["total"] ?? 5;
$total_servers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM servers"))["total"] ?? 10;
$total_plans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM plans"))["total"] ?? 3;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
  <h2>📈 Estadísticas del sistema</h2>

  <div class="stats-grid">
    <div class="stat"><h4>Usuarios</h4><p><?= $total_users; ?></p></div>
    <div class="stat"><h4>Servidores</h4><p><?= $total_servers; ?></p></div>
    <div class="stat"><h4>Planes</h4><p><?= $total_plans; ?></p></div>
  </div>

  <h3>📊 Actividad general</h3>
  <canvas id="chart1" style="max-width:600px;margin:auto;"></canvas>

  <h3 style="margin-top:40px;">🐳 Uso de Docker (simulado)</h3>
  <canvas id="chart2" style="max-width:600px;margin:auto;"></canvas>
</div>

<script>
const ctx1 = document.getElementById('chart1').getContext('2d');
new Chart(ctx1, {
  type: 'bar',
  data: {
    labels: ['Usuarios', 'Servidores', 'Planes'],
    datasets: [{
      label: 'Totales del sistema',
      data: [<?= $total_users; ?>, <?= $total_servers; ?>, <?= $total_plans; ?>],
      backgroundColor: ['#4FC3F7','#81D4FA','#0288D1']
    }]
  },
  options: {
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { display: false } }
  }
});

const ctx2 = document.getElementById('chart2').getContext('2d');
new Chart(ctx2, {
  type: 'doughnut',
  data: {
    labels: ['CPU usada','CPU libre'],
    datasets: [{
      data: [37, 63],
      backgroundColor: ['#4FC3F7','#1E1E1E']
    }]
  },
  options: { cutout: '60%', plugins: { legend: { labels: { color: '#fff' } } } }
});
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
