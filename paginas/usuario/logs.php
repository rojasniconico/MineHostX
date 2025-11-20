<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$user_id = $_SESSION["user_id"];
$server_id = intval($_GET["id"]);

$check = mysqli_query($conn, "SELECT id FROM servers WHERE id=$server_id AND user_id=$user_id");
if (mysqli_num_rows($check) === 0) {
  die("❌ No tienes permiso para ver estos logs.");
}

$logs = mysqli_query($conn, "SELECT * FROM logs WHERE server_id=$server_id ORDER BY timestamp DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Logs del Servidor - MineHostX</title>
<link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php include_once "../../comun/navbar.php"; ?>
<h2>📜 Logs del Servidor</h2>
<div class="container card" style="text-align:left; max-width:800px;">
  <?php while($log = mysqli_fetch_assoc($logs)): ?>
    <p><strong>[<?php echo $log["timestamp"]; ?>]</strong> 
      <?php echo htmlspecialchars($log["message"]); ?>
    </p>
  <?php endwhile; ?>
</div>
<p style="text-align:center;">
  <a href="ver_servidores.php?id=<?php echo $server_id; ?>">⬅ Volver al servidor</a>
</p>
</body>
<?php include_once "../../comun/chatbot.php"; ?>
</html>
