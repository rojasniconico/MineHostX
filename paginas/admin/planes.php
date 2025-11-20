<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if ($_SESSION["role"] !== "admin") {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$msg = "";

// 🟢 Crear nuevo plan
if (isset($_POST["create"])) {
  $name = $_POST["name"];
  $desc = $_POST["description"];
  $max_servers = intval($_POST["max_servers"]);
  $max_ram = intval($_POST["max_ram"]);
  $mods = isset($_POST["allow_mods"]) ? 1 : 0;
  $plugins = isset($_POST["allow_plugins"]) ? 1 : 0;
  $backups = isset($_POST["allow_backups"]) ? 1 : 0;
  $price = floatval($_POST["price"]);

  $sql = "INSERT INTO plans (name, description, max_servers, max_ram, allow_mods, allow_plugins, allow_backups, price)
          VALUES ('$name', '$desc', $max_servers, $max_ram, $mods, $plugins, $backups, $price)";
  mysqli_query($conn, $sql);
  $msg = "✅ Plan creado correctamente.";
}

// 🔴 Eliminar plan
if (isset($_GET["delete"])) {
  $id = intval($_GET["delete"]);
  mysqli_query($conn, "DELETE FROM plans WHERE id=$id");
  $msg = "❌ Plan eliminado.";
}

$plans = mysqli_query($conn, "SELECT * FROM plans ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de planes - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
</head>
<body>
<div class="container">
  <h2>💼 Gestión de planes</h2>
  <?php if($msg) echo "<p>$msg</p>"; ?>

  <table>
    <tr><th>ID</th><th>Nombre</th><th>Servidores</th><th>RAM</th><th>Mods</th><th>Backups</th><th>Precio</th><th>Acción</th></tr>
    <?php while($p = mysqli_fetch_assoc($plans)): ?>
    <tr>
      <td><?php echo $p["id"]; ?></td>
      <td><?php echo htmlspecialchars($p["name"]); ?></td>
      <td><?php echo $p["max_servers"]; ?></td>
      <td><?php echo $p["max_ram"]; ?> GB</td>
      <td><?php echo $p["allow_mods"] ? "✅" : "❌"; ?></td>
      <td><?php echo $p["allow_backups"] ? "✅" : "❌"; ?></td>
      <td><?php echo $p["price"]; ?> €</td>
      <td><a href="?delete=<?php echo $p["id"]; ?>" onclick="return confirm('¿Eliminar plan?')">🗑️ Eliminar</a></td>
    </tr>
    <?php endwhile; ?>
  </table>

  <h3>➕ Crear nuevo plan</h3>
  <form method="POST">
    <input type="text" name="name" placeholder="Nombre" required>
    <textarea name="description" placeholder="Descripción"></textarea>
    <input type="number" name="max_servers" placeholder="Máx. servidores" min="1" required>
    <input type="number" name="max_ram" placeholder="RAM (GB)" min="1" required>
    <label><input type="checkbox" name="allow_mods"> Permitir mods</label>
    <label><input type="checkbox" name="allow_plugins"> Permitir plugins</label>
    <label><input type="checkbox" name="allow_backups"> Permitir backups</label>
    <input type="number" step="0.01" name="price" placeholder="Precio (€)">
    <button type="submit" name="create">Crear plan</button>
  </form>

  <p><a href="panel_admin.php">⬅ Volver al panel</a></p>
</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
