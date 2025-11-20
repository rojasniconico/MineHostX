<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$plugin_id = intval($_GET["id"]);
$server_id = intval($_GET["server"]);
$user_id = $_SESSION["user_id"];

// comprobar que el plugin pertenece a un servidor del usuario
$plugin = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT server_plugins.filename, servers.user_id 
   FROM server_plugins 
   JOIN servers ON server_plugins.server_id = servers.id
   WHERE server_plugins.id=$plugin_id AND servers.id=$server_id"
));

if (!$plugin) die("❌ Plugin no encontrado.");
if ($plugin["user_id"] != $user_id) die("❌ No puedes borrar plugins de otros usuarios.");

$filepath = "../../servidores/server_$server_id/plugins/" . $plugin["filename"];

if (file_exists($filepath)) unlink($filepath);

mysqli_query($conn, "DELETE FROM server_plugins WHERE id=$plugin_id");

header("Location: plugins.php?id=$server_id");
exit();
?>
