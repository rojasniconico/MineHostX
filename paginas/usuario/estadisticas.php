<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$id = intval($_GET["id"]);

// verificar servidor
$server = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT * FROM servers WHERE id=$id AND user_id=$user_id
"));
if (!$server) die("❌ No tienes acceso a este servidor.");

// generar estadísticas simuladas
$ram = rand(300, $server["ram_gb"] * 1024);
$cpu = rand(2, 90);
$players = rand(0, 10);
$tps = rand(16, 20) + (rand(0,10)/10);

// guardar en BD
mysqli_query($conn,"
    INSERT INTO server_stats (server_id, ram_used, cpu_used, players, tps)
    VALUES ($id, $ram, $cpu, $players, $tps)
");

// obtener últimos datos
$stats = mysqli_query($conn,"
    SELECT * FROM server_stats
    WHERE server_id = $id
    ORDER BY created_at DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas - MineHostX</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; text-align:center; }
.container {
    width:80%; margin:auto; margin-top:25px;
    background:#1E1E1E; padding:20px; border-radius:12px;
}
h2 { color:#4FC3F7; }
.box {
    background:#191919; padding:15px; border-radius:8px;
    margin:10px; text-align:left;
}
.data {
    font-size:20px; margin-bottom:5px;
}
a { color:#4FC3F7; }
/* Footer */
footer {
  background: #0d0d0d;
  color: #aaa;
  text-align: center;
  padding: 20px;
  font-size: 0.9em;
}
footer a {
  color: #4FC3F7;
  text-decoration: none;
}
</style>
</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="container">

<h2>📊 Estadísticas del servidor <?php echo $server["name"]; ?></h2>

<div class="box">
    <p class="data"><b>RAM usada:</b> <?php echo $ram; ?> MB / <?php echo $server["ram_gb"] * 1024; ?> MB</p>
    <p class="data"><b>CPU usada:</b> <?php echo $cpu; ?>%</p>
    <p class="data"><b>Jugadores:</b> <?php echo $players; ?></p>
    <p class="data"><b>TPS:</b> <?php echo number_format($tps,1); ?></p>
</div>

<hr style="border-color:#333">

<h3>📈 Historial reciente</h3>
<canvas id="chart" height="130"></canvas>

<script>
const labels = [
    <?php 
    $tmp = [];
    while($r = mysqli_fetch_assoc($stats)){
        $tmp[] = "'" . $r["created_at"] . "'";
    }
    echo implode(",", array_reverse($tmp));
    ?>
];

const ramData = [
    <?php 
    $stats->data_seek(0);
    $tmp=[];
    while($r = mysqli_fetch_assoc($stats)){
        $tmp[] = $r["ram_used"];
    }
    echo implode(",", array_reverse($tmp));
    ?>
];

const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'RAM usada (MB)',
            data: ramData,
            borderWidth: 2
        }]
    }
});
</script>

<br>
<a href="ver_servidores.php?id=<?php echo $id; ?>">⬅ Volver al servidor</a>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
