<?php
// api/command.php
session_start();
header('Content-Type: application/json');
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status'=>'error','msg'=>'No autenticado']);
  exit();
}

$user_id = $_SESSION['user_id'];
$server_id = intval($_POST['server_id'] ?? 0);
$command = trim($_POST['command'] ?? '');

if (!$server_id || $command === '') {
  echo json_encode(['status'=>'error','msg'=>'Datos incompletos']);
  exit();
}

// verificar que el servidor pertenece al usuario (o admin)
$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$server_id"));
if (!$sv) { echo json_encode(['status'=>'error','msg'=>'Servidor no existe']); exit(); }
if ($sv['user_id'] != $user_id && $_SESSION['role'] !== 'admin') {
  echo json_encode(['status'=>'error','msg'=>'No autorizado']); exit();
}

// Registrar comando en logs
$cmd_safe = mysqli_real_escape_string($conn, $command);
mysqli_query($conn, "INSERT INTO server_logs (server_id, mensaje) VALUES ($server_id, 'CMD: $cmd_safe')");

// Simular respuesta (podrás conectar RCON más tarde)
$response = "[Simulado] Ejecutado: $command\nResultado: OK";

// También guardar respuesta como log
$resp_safe = mysqli_real_escape_string($conn, $response);
mysqli_query($conn, "INSERT INTO server_logs (server_id, mensaje) VALUES ($server_id, 'RSP: $resp_safe')");

echo json_encode(['status'=>'ok','reply'=>$response]);
