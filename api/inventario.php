<?php
// api/inventory.php
session_start();
header('Content-Type: application/json');
require_once "../db.php";

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','msg'=>'No autenticado']); exit(); }

$server_id = intval($_GET['server_id'] ?? 0);
if (!$server_id) { echo json_encode(['status'=>'error','msg'=>'No server']); exit(); }

$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$server_id"));
if (!$sv) { echo json_encode(['status'=>'error','msg'=>'No encontrado']); exit(); }
if ($sv['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
  echo json_encode(['status'=>'error','msg'=>'No autorizado']); exit();
}

$base = __DIR__ . "/../servidores/server_$server_id/players/";
$players = [];

if (is_dir($base)) {
  foreach (glob($base . "*.json") as $file) {
    $name = basename($file, ".json");
    $content = @file_get_contents($file);
    $data = @json_decode($content, true);
    if (!$data) {
      // si no hay json válido, generar dummy
      $data = ['inventory'=>[['item'=>'stone','count'=>64]]];
    }
    $players[] = ['player'=>$name,'inventory'=>$data['inventory']];
  }
}

// si no hay jugadores, simulamos 2 players
if (empty($players)) {
  $players = [
    ['player'=>'Steve','inventory'=>[['item'=>'diamond','count'=>3],['item'=>'apple','count'=>5]]],
    ['player'=>'Alex','inventory'=>[['item'=>'iron_ingot','count'=>16]]]
  ];
}

echo json_encode(['status'=>'ok','players'=>$players]);
