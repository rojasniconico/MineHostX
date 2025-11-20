<?php
session_start();
require_once "../../db.php";
require_once "../../comun/navbar.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$user_id = intval($_SESSION["user_id"]);
$id = intval($_GET["id"]);

// Obtener servidor
$server = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT * FROM servers WHERE id=$id AND user_id=$user_id
"));

if (!$server) {
  die("❌ Servidor no encontrado o no tienes permiso.");
}

// Encender / apagar (control real de Docker)
if (isset($_POST["action"])) {
  $action = $_POST["action"];

  // Rutas dinámicas
  $ROOT_PATH = realpath(__DIR__ . "/../../");
  $composePath = $ROOT_PATH . "/servidores/server_$id/docker-compose.yml";
  $composePathEsc = escapeshellarg($composePath);
  $containerName = "server_$id";
  $containerEsc = escapeshellarg($containerName);

  if ($action === "start") {
      // Arrancar mediante docker compose (asegura crear/actualizar volumenes)
      exec("docker compose -f $composePathEsc up -d 2>&1", $out, $rc);
      if ($rc === 0) {
          mysqli_query($conn, "UPDATE servers SET status='running' WHERE id=$id");
      } else {
          // opcional: log
          $_SESSION["docker_error"] = "Error al arrancar: " . implode("\n", $out);
      }
  }
  elseif ($action === "stop") {
      // Parar contenedor
      exec("docker stop $containerEsc 2>&1", $out, $rc);
      if ($rc === 0) {
          mysqli_query($conn, "UPDATE servers SET status='stopped' WHERE id=$id");
      } else {
          $_SESSION["docker_error"] = "Error al detener: " . implode("\n", $out);
      }
  }

  header("Location: ver_servidores.php?id=$id");
  exit();
}

// Recargar servidor actualizado
$server = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$id"));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Servidor <?php echo htmlspecialchars($server["name"]); ?> - MineHostX</title>

<style>
body {
  background:#121212;
  color:#fff;
  font-family:'Segoe UI';
  text-align:center;
}
.container {
  max-width:700px;
  margin:auto;
  background:#1E1E1E;
  padding:20px;
  border-radius:12px;
  margin-top:30px;
  box-shadow:0 4px 12px rgba(0,0,0,0.4);
}
h2 { color:#4FC3F7; }

.btn {
  background:#4FC3F7;
  border:none;
  color:#000;
  padding:10px 20px;
  border-radius:8px;
  margin:10px;
  cursor:pointer;
  font-weight:bold;
}
.btn-red {
  background:#e74c3c;
  color:#fff;
}
.link {
  color:#4FC3F7;
  text-decoration:none;
}
.section {
  margin-top:20px;
  background:#191919;
  padding:15px;
  border-radius:8px;
}
.notice { color:#ffcc00; }
</style>

</head>
<body>

<div class="container">

<h2>🖥️ Servidor: <?php echo htmlspecialchars($server["name"]); ?></h2>

<div class="section">
  <p><b>Estado:</b> <?php echo strtoupper($server["status"]); ?></p>
  <p><b>IP:</b> <code><?php echo htmlspecialchars($server["ip"]); ?></code></p>
  <p><b>Puerto:</b> <code><?php echo htmlspecialchars($server["port"]); ?></code></p>
  <p><b>Conexión:</b> <code><?php echo htmlspecialchars($server["ip"]); ?>:<?php echo htmlspecialchars($server["port"]); ?></code></p>
  <p><b>RAM asignada:</b> <?php echo htmlspecialchars($server["ram_gb"]); ?> GB</p>
  <p><b>Software:</b> <?php echo htmlspecialchars($server["software"]); ?></p>

  <?php if (isset($_SESSION["docker_error"])): ?>
    <p class="notice">⚠️ <?php echo nl2br(htmlspecialchars($_SESSION["docker_error"])); unset($_SESSION["docker_error"]); ?></p>
  <?php endif; ?>

  <form method="POST">
    <?php if ($server["status"] === "stopped"): ?>
      <button class="btn" name="action" value="start">Iniciar</button>
    <?php else: ?>
      <button class="btn" name="action" value="stop">Detener</button>
    <?php endif; ?>
  </form>
</div>

<?php
// justo después de obtener $server y $id
// Si tienes mapa_servidor.php, mantenlo; si no, el include puede omitirse
$mapaFile = __DIR__ . "/mapa_servidor.php";
if (file_exists($mapaFile)) include_once "mapa_servidor.php";
?>

<div class="section">
  <form method="POST" action="eliminar_servidor.php" onsubmit="return confirmarEliminacion();">
    <input type="hidden" name="server_id" value="<?php echo $id; ?>">
    <button type="submit" class="btn btn-red">🗑️ Eliminar servidor</button>
  </form>
</div>

<p>
  <a class="link" href="servidores.php">⬅ Volver a servidores</a>
</p>

</div>

<script>
function confirmarEliminacion() {
  return confirm("⚠️ ¿Seguro que quieres eliminar este servidor? Tendrás que escribir ELIMINAR para confirmar.");
}
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
