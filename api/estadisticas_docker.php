<?php
session_start();
header('Content-Type: application/json');

if ($_SESSION["role"] !== "admin") {
  echo json_encode(["status"=>"error","message"=>"No autorizado"]);
  exit();
}

$stats = [
  "cpu_total" => "37%",
  "mem_total" => "3.2 GB / 8 GB",
  "containers" => 5,
  "running" => 3,
];

echo json_encode(["status"=>"ok","data"=>$stats]);
?>
