<?php
session_start();
require_once "../db.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    if ($password !== $confirm) {
        $msg = "Las contraseñas no coinciden.";
    } else {
        // Verificar duplicados
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' OR username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $msg = "Ya existe un usuario con ese nombre o correo.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password_hash, plan_id, role)
                    VALUES ('$username', '$email', '$hash', 1, 'user')";
            if (mysqli_query($conn, $sql)) {
// suponiendo $new_user_id = mysqli_insert_id($conn);
$ref = isset($_GET['ref']) ? intval($_GET['ref']) : (isset($_POST['ref']) ? intval($_POST['ref']) : 0);
if ($ref > 0 && $ref != $new_user_id) {
    // almacenar referencia
    $ref_safe = intval($ref);
    mysqli_query($conn, "INSERT INTO referrals (referrer_id, referred_id) VALUES ($ref_safe, $new_user_id)");
    // recompensas: ejemplo: invitado +100 pts, invitador +150 pts
    require_once "../comun/puntos.php";
    addPoints($conn, $new_user_id, 100, "Recompensa por registro con referido");
    addPoints($conn, $ref_safe, 150, "Recompensa por invitar a usuario ID $new_user_id");
}

                $msg = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
		header("Location: ../paginas/usuario/panel.php");
            } else {
                $msg = "❌ Error al registrar el usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - MineHostX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; text-align:center; }
form {
  background:#1E1E1E;
  display:inline-block;
  margin-top:40px;
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
.message { color:#4FC3F7; margin-top:10px; }
a { color:#4FC3F7; text-decoration:none; }
</style>
</head>
<body>


  <h2>📝 Crear cuenta en MineHostX</h2>

  <form method="POST">
    <input type="text" name="username" placeholder="Nombre de usuario" required>
    <input type="email" name="email" placeholder="Correo electrónico" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <input type="password" name="confirm" placeholder="Repetir contraseña" required>
<input type="hidden" name="ref" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">
    <button type="submit">Registrarse</button>
    <?php if ($msg): ?><div class="message"><?php echo $msg; ?></div><?php endif; ?>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
  </form>
</body>
<?php include_once "../comun/chatbot.php"; ?>
</html>