<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$servers = mysqli_query($conn, "
  SELECT servers.*, users.username
  FROM servers
  JOIN users ON servers.user_id = users.id
  ORDER BY servers.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Servidores - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
</head>
<body>
<?php include_once "../../comun/navbar.php"; ?>
<div class="container">
  <h2>🖥️ Todos los servidores</h2>
  <table>
    <tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Estado</th><th>RAM</th><th>Software</th><th>Creado</th></tr>
    <?php while($s = mysqli_fetch_assoc($servers)): ?>
    <tr>
      <td><?php echo $s["id"]; ?></td>
      <td><?php echo htmlspecialchars($s["username"]); ?></td>
      <td><?php echo htmlspecialchars($s["name"]); ?></td>
      <td><?php echo $s["status"] === "running" ? "🟢 Encendido" : "🔴 Apagado"; ?></td>
      <td><?php echo $s["ram_gb"]; ?> GB</td>
      <td><?php echo $s["software"]; ?></td>
      <td><?php echo $s["created_at"]; ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
  <p><a href="panel_admin.php">⬅ Volver al panel</a></p>
</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
