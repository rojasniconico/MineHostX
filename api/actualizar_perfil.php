<?php
session_start();
require_once "../db.php";
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["status"=>"error","message"=>"No autenticado"]);
  exit();
}

$username = trim($_POST["username"] ?? '');
$email = trim($_POST["email"] ?? '');
$pass = trim($_POST["password"] ?? '');

if ($username) mysqli_query($conn, "UPDATE users SET username='$username' WHERE id={$_SESSION["user_id"]}");
if ($email) mysqli_query($conn, "UPDATE users SET email='$email' WHERE id={$_SESSION["user_id"]}");
if ($pass) {
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  mysqli_query($conn, "UPDATE users SET password_hash='$hash' WHERE id={$_SESSION["user_id"]}");
}

echo json_encode(["status"=>"ok","message"=>"Perfil actualizado correctamente"]);
?>
