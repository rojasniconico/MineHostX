<?php
session_start();
require_once "../../db.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$users = mysqli_query($conn, "SELECT users.*, plans.name AS plan FROM users JOIN plans ON users.plan_id = plans.id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
</head>
<body>
<?php include_once "../../comun/navbar.php"; ?>
<br><br>
<h2 style="text-align:center;">👥 Usuarios registrados</h2>
<table style="width:90%; margin:auto; border-collapse:collapse;">
<tr><th>ID</th><th>Usuario</th><th>Email</th><th>Plan</th><th>Rol</th><th>Acción</th></tr>
<?php while($u = mysqli_fetch_assoc($users)): ?>
<tr>
  <td><?php echo $u["id"]; ?></td>
  <td><?php echo htmlspecialchars($u["username"]); ?></td>
  <td><?php echo htmlspecialchars($u["email"]); ?></td>
  <td><?php echo $u["plan"]; ?></td>
  <td><?php echo $u["role"]; ?></td>
  <td><a href="editar_usuario.php?id=<?php echo $u["id"]; ?>">Editar</a></td>
</tr>
<?php endwhile; ?>
</table>
<p style="text-align:center;"><a href="panel_admin.php">⬅ Volver al panel</a></p>

<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
