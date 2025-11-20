<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["status"=>"error","message"=>"No autenticado"]);
  exit();
}

$id = intval($_GET["id"] ?? 0);
$logs = [
  "[10:30] Servidor iniciado correctamente",
  "[10:35] Jugador Nico conectado",
  "[10:37] Jugador Steve desconectado",
  "[10:45] Backup completado",
];

echo json_encode(["status"=>"ok","logs"=>$logs]);
?>
