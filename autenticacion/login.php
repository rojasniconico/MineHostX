<?php
session_start();
require_once "../db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];
            header("Location: ../paginas/usuario/panel.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No existe una cuenta con ese correo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - MineHostX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; text-align:center; }
form {
  background:#1E1E1E;
  display:inline-block;
  margin-top:60px;
  padding:30px;
  border-radius:12px;
  box-shadow:0 4px 10px rgba(0,0,0,0.4);
}
input {
  display:block;
  width:250px;
  margin:10px auto;
  padding:10px;
  border:none;
  border-radius:6px;
}
button {
  background:#4FC3F7;
  border:none;
  padding:10px 20px;
  border-radius:8px;
  color:#000;
  font-weight:bold;
  cursor:pointer;
}
.error { color:#f77; margin-top:10px; }
a { color:#4FC3F7; text-decoration:none; }
</style>
</head>
<body>
  <h2>🔐 Iniciar sesión en MineHostX</h2>

  <form method="POST">
    <input type="email" name="email" placeholder="Correo electrónico" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
    <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
<p style="text-align: center; margin-top: 15px;">
    <a href="olvidar_contraseña.php" style="color:#4FC3F7; text-decoration:none;">¿Olvidaste tu contraseña?</a>
</p>
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
  </form>
</body>
<?php include_once "../comun/chatbot.php"; ?>
</html>
