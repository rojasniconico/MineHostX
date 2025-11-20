<?php
session_start();
header('Content-Type: application/json');

if ($_SESSION["role"] !== "admin") {
  echo json_encode(["status"=>"error", "message"=>"Acceso denegado"]);
  exit();
}

$data = [
  ["name"=>"srv_nico", "status"=>"running", "cpu"=>"15%", "ram"=>"1.2 GB"],
  ["name"=>"srv_steve", "status"=>"stopped", "cpu"=>"0%", "ram"=>"0 GB"],
  ["name"=>"srv_alex", "status"=>"running", "cpu"=>"22%", "ram"=>"2.0 GB"]
];

echo json_encode(["status"=>"ok", "containers"=>$data]);
?>
