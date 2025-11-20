<?php
session_start();
require_once "../db.php";
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["status"=>"error","message"=>"No autenticado"]);
  exit();
}

$name = trim($_POST["name"] ?? '');
$ram = intval($_POST["ram_gb"] ?? 1);
$software = trim($_POST["software"] ?? 'Vanilla');

if (!$name) {
  echo json_encode(["status"=>"error","message"=>"Falta el nombre del servidor"]);
  exit();
}

$sql = "INSERT INTO servers (user_id, name, status, ram_gb, software, port, created_at)
        VALUES ({$_SESSION["user_id"]}, '$name', 'stopped', $ram, '$software', 25565, NOW())";

if (mysqli_query($conn, $sql)) {
  echo json_encode(["status"=>"ok", "message"=>"Servidor creado correctamente."]);
} else {
  echo json_encode(["status"=>"error", "message"=>"Error al crear: ".mysqli_error($conn)]);
}
?>
