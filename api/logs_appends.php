<?php
// api/logs_append.php
session_start();
header('Content-Type: application/json');
require_once "../db.php";

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error']); exit(); }

$server_id = intval($_POST['server_id'] ?? 0);
$msg = trim($_POST['message'] ?? '');

if (!$server_id || $msg === '') { echo json_encode(['status'=>'error']); exit(); }

// verificar permisos
$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$server_id"));
if (!$sv) { echo json_encode(['status'=>'error']); exit(); }
if ($sv['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') { echo json_encode(['status'=>'error']); exit(); }

$msg_safe = mysqli_real_escape_string($conn, $msg);
mysqli_query($conn, "INSERT INTO server_logs (server_id, mensaje) VALUES ($server_id, '$msg_safe')");

echo json_encode(['status'=>'ok']);
