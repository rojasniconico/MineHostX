<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../autenticacion/login.php");
  exit();
}

$mod_id = intval($_GET["id"]);
$server_id = intval($_GET["server"]);
$user_id = $_SESSION["user_id"];

// comprobar que el mod pertenece a un servidor del usuario
$mod = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT server_mods.filename, servers.user_id 
   FROM server_mods 
   JOIN servers ON server_mods.server_id = servers.id
   WHERE server_mods.id=$mod_id AND servers.id=$server_id"
));

if (!$mod) die("❌ Mod no encontrado.");
if ($mod["user_id"] != $user_id) die("❌ No puedes borrar mods de otros usuarios.");

$filepath = "../../servidores/server_$server_id/mods/" . $mod["filename"];

if (file_exists($filepath)) unlink($filepath);

mysqli_query($conn, "DELETE FROM server_mods WHERE id=$mod_id");

header("Location: mods.php?id=$server_id");
exit();
?>
