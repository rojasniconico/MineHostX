<?php
session_start();
require_once "../db.php";
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["status"=>"error", "message"=>"No autenticado"]);
  exit();
}

$id = intval($_POST["server_id"] ?? 0);
$action = $_POST["action"] ?? '';

if (!$id || !$action) {
  echo json_encode(["status"=>"error", "message"=>"Datos incompletos"]);
  exit();
}

switch ($action) {
  case "start":
    mysqli_query($conn, "UPDATE servers SET status='running' WHERE id=$id AND user_id={$_SESSION["user_id"]}");
    echo json_encode(["status"=>"ok", "message"=>"Servidor iniciado."]);
    break;
  case "stop":
    mysqli_query($conn, "UPDATE servers SET status='stopped' WHERE id=$id AND user_id={$_SESSION["user_id"]}");
    echo json_encode(["status"=>"ok", "message"=>"Servidor detenido."]);
    break;
  case "restart":
    echo json_encode(["status"=>"ok", "message"=>"Servidor reiniciado (simulado)."]);
    break;
  default:
    echo json_encode(["status"=>"error", "message"=>"Acción no válida"]);
}
?>
