<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$user_id = $_SESSION["user_id"];
$server_id = intval($_POST['server_id'] ?? 0);
$confirm = $_POST['confirm'] ?? '';

if (!$server_id) {
  header("Location: listado_servidores.php");
  exit();
}

// comprobar servidor y propiedad
$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$server_id LIMIT 1"));
if (!$sv) {
  $_SESSION['msg'] = "Servidor no encontrado.";
  header("Location: listado_servidores.php");
  exit();
}

// check permiso: owner o admin
if ($sv['user_id'] != $user_id && $_SESSION['role'] !== 'admin') {
  $_SESSION['msg'] = "No tienes permiso para eliminar este servidor.";
  header("Location: listado_servidores.php");
  exit();
}

// exigir confirmación (por si se accede por error)
if ($confirm !== 'ELIMINAR') {
  // mostrar formulario de confirmación sencillo
  ?>
  <!DOCTYPE html>
  <html lang="es">
  <head><meta charset="utf-8"><title>Eliminar servidor</title></head>
  <body style="font-family:Arial, sans-serif; background:#121212; color:#fff; text-align:center; padding:40px;">
    <h2>⚠️ Eliminar servidor: <?=htmlspecialchars($sv['name'])?></h2>
    <p>Para confirmar escribe <strong>ELIMINAR</strong> en el campo y pulsa Confirmar. Esto borrará todos los archivos del servidor y su registro en la base de datos.</p>
    <form method="POST" style="display:inline-block; margin-top:20px;">
      <input type="hidden" name="server_id" value="<?=$server_id?>">
      <input type="text" name="confirm" placeholder="Escribe ELIMINAR" required>
      <br><br>
      <button type="submit" style="padding:8px 12px; background:#f44336; border:none; color:#fff; border-radius:8px; cursor:pointer;">Confirmar eliminación</button>
      <a href="server_view.php?id=<?=$server_id?>" style="display:inline-block; margin-left:8px; color:#4FC3F7;">Cancelar</a>
    </form>
  </body>
  </html>
  <?php
  exit();
}

// Si llegó aquí, confirm == 'ELIMINAR' -> procedemos
// 1) Borrar carpetas del servidor (mods, plugins, backups, players, etc)
$base = __DIR__ . "/../../servidores/server_$server_id/";

// función recursiva segura
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object == "." || $object == "..") continue;
        $path = $dir . DIRECTORY_SEPARATOR . $object;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

if (is_dir($base)) {
    rrmdir($base);
}

// 2) Borrar entradas en BD: servers + server_mods + server_plugins + backups + server_logs (ON DELETE CASCADE should handle most)
mysqli_query($conn, "DELETE FROM servers WHERE id=$server_id");

// opcional: eliminar manualmente si cascada no configurada
mysqli_query($conn, "DELETE FROM server_mods WHERE server_id=$server_id");
mysqli_query($conn, "DELETE FROM server_plugins WHERE server_id=$server_id");
mysqli_query($conn, "DELETE FROM backups WHERE server_id=$server_id");
mysqli_query($conn, "DELETE FROM server_logs WHERE server_id=$server_id");

// 3) redirigir con mensaje
$_SESSION['msg'] = "Servidor eliminado correctamente.";
header("Location: servidores.php");
exit();
