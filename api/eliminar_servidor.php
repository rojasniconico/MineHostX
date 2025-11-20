<?php
session_start();
require_once "../db.php";
header('Content-Type: application/json');

$id = intval($_POST["server_id"] ?? 0);
if (!isset($_SESSION["user_id"]) || !$id) {
  echo json_encode(["status"=>"error","message"=>"Acceso denegado"]);
  exit();
}

mysqli_query($conn, "DELETE FROM servers WHERE id=$id AND user_id={$_SESSION["user_id"]}");
echo json_encode(["status"=>"ok","message"=>"Servidor eliminado correctamente."]);
?>
