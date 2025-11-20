<?php
require_once "../db.php";
header('Content-Type: application/json');

$username = $_POST["username"] ?? '';
$email = $_POST["email"] ?? '';
$password = $_POST["password"] ?? '';

if (!$username || !$email || !$password) {
  echo json_encode(["status"=>"error", "message"=>"Todos los campos son obligatorios."]);
  exit();
}

$exists = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
if (mysqli_num_rows($exists) > 0) {
  echo json_encode(["status"=>"error", "message"=>"El email ya está registrado."]);
  exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password_hash, role, plan_id)
        VALUES ('$username', '$email', '$hash', 'user', 1)";
if (mysqli_query($conn, $sql)) {
  echo json_encode(["status"=>"ok", "message"=>"Registro completado."]);
} else {
  echo json_encode(["status"=>"error", "message"=>"Error: ".mysqli_error($conn)]);
}
?>
