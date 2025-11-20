<?php
session_start();
require_once "../../db.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"));
$servers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM servers"));
$plans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM plans"));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
</head>
<body>
<?php include_once "../../comun/navbar.php"; ?>
<div class="header">
<br><br><br>
  <h2>⚙️ Panel de Administración - MineHostX</h2>
  <a href="../../autenticacion/cerrar_sesion.php">Cerrar sesión</a>
</div>
<div class="container">
  <div class="card"><h3>Usuarios registrados:</h3><p><?php echo $users["total"]; ?></p></div>
  <div class="card"><h3>Servidores creados:</h3><p><?php echo $servers["total"]; ?></p></div>
  <div class="card"><h3>Planes activos:</h3><p><?php echo $plans["total"]; ?></p></div>

  <p><a href="usuarios.php">👥 Gestionar usuarios</a> | 
     <a href="planes.php">💼 Planes</a> |
     <a href="servidores.php">🖥️ Servidores</a>
  </p>
</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
