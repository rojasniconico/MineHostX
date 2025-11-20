<?php
session_start();
require_once "../../db.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$id = intval($_GET["id"]);
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));
$plans = mysqli_query($conn, "SELECT * FROM plans");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"]);
  $plan_id = intval($_POST["plan_id"]);
  $role = $_POST["role"];
  mysqli_query($conn, "UPDATE users SET username='$username', plan_id=$plan_id, role='$role' WHERE id=$id");
  $msg = "✅ Usuario actualizado.";
  $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
</head>
<body>
<h2>✏️ Editar Usuario</h2>
<form method="POST" style="text-align:center;">
  <input type="text" name="username" value="<?php echo htmlspecialchars($user["username"]); ?>" required>
  <select name="plan_id">
    <?php while($p = mysqli_fetch_assoc($plans)): ?>
      <option value="<?php echo $p["id"]; ?>" <?php if($user["plan_id"] == $p["id"]) echo "selected"; ?>>
        <?php echo $p["name"]; ?>
      </option>
    <?php endwhile; ?>
  </select>
  <select name="role">
    <option value="user" <?php if($user["role"]=="user") echo "selected"; ?>>Usuario</option>
    <option value="admin" <?php if($user["role"]=="admin") echo "selected"; ?>>Administrador</option>
  </select>
  <button type="submit">Guardar</button>
</form>
<?php if($msg) echo "<p style='text-align:center;'>$msg</p>"; ?>
<p style="text-align:center;"><a href="usuarios.php">⬅ Volver</a></p>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
