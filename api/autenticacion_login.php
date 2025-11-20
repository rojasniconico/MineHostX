<?php
session_start();
require_once "../db.php";
header('Content-Type: application/json');

$email = $_POST["email"] ?? '';
$password = $_POST["password"] ?? '';

if (!$email || !$password) {
  echo json_encode(["status" => "error", "message" => "Faltan datos"]);
  exit();
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1"));

if ($user && password_verify($password, $user["password_hash"])) {
  $_SESSION["user_id"] = $user["id"];
  $_SESSION["role"] = $user["role"];
  echo json_encode(["status" => "ok", "message" => "Inicio de sesión correcto", "role" => $user["role"]]);
} else {
  echo json_encode(["status" => "error", "message" => "Credenciales incorrectas"]);
}
?>
