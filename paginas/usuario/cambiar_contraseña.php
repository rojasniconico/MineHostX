<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);
$msg = '';
$msg_class = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Verificar que las contraseñas coincidan
    if ($new_password !== $confirm_password) {
        $msg = "❌ Error: La nueva contraseña y su confirmación no coinciden.";
        $msg_class = 'error-msg';
    } 
    // 2. Verificar longitud mínima (opcional, pero buena práctica)
    elseif (strlen($new_password) < 8) {
        $msg = "❌ Error: La nueva contraseña debe tener al menos 8 caracteres.";
        $msg_class = 'error-msg';
    }
    else {
        // 3. Obtener el hash de la contraseña actual del usuario
        $result = mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id");
        $user = mysqli_fetch_assoc($result);
        $hashed_password = $user['password'];

        // 4. Verificar que la contraseña actual sea correcta
        if (password_verify($current_password, $hashed_password)) {
            
            // 5. Hashear la nueva contraseña
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // 6. Actualizar la base de datos
            $update_query = "UPDATE users SET password = '$new_hashed_password' WHERE id = $user_id";
            
            if (mysqli_query($conn, $update_query)) {
                $msg = "✅ ¡Contraseña actualizada con éxito! Serás redirigido en 3 segundos.";
                $msg_class = 'success-msg';
                // Redirigir después de un pequeño retraso
                header("Refresh: 3; url=perfil.php");
            } else {
                $msg = "❌ Error al actualizar la contraseña: " . mysqli_error($conn);
                $msg_class = 'error-msg';
            }
        } else {
            $msg = "❌ Error: La contraseña actual es incorrecta.";
            $msg_class = 'error-msg';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🔐 Cambiar Contraseña — MineHostX</title>
    <style>
        body { background:#121212; color:#fff; font-family:'Segoe UI'; padding-top: 60px; }
        .container { max-width:500px; margin:50px auto; padding:30px; background:#1e1e1e; border-radius:12px; }
        h2 { color:#FF9800; text-align:center; margin-bottom: 25px; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #272727;
            color: #fff;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background:#FF9800; 
            padding:12px; 
            border-radius:6px; 
            border:none; 
            cursor:pointer; 
            font-weight:bold;
            color: #000;
            font-size: 1.1em;
        }
        button:hover { background: #FFB74D; }
        .error-msg { color: #FF4D4D; text-align: center; font-weight: bold; margin-bottom: 20px; }
        .success-msg { color: #A5D6A7; text-align: center; font-weight: bold; margin-bottom: 20px; }
        a { color:#4FC3F7; text-decoration: none; display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>🔐 Cambiar Contraseña</h2>

    <?php if ($msg): ?>
        <p class="<?= $msg_class ?>"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="current_password">Contraseña Actual:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div>
            <label for="new_password">Nueva Contraseña:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div>
            <label for="confirm_password">Confirmar Nueva Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit">Actualizar Contraseña</button>
    </form>
    
<p style="text-align: center; margin-top: 15px;">
    <a href="olvidar_contraseña2.php" style="color:#4FC3F7; text-decoration:none;">¿Olvidaste tu contraseña?</a>
</p>
    <a href="perfil.php">⬅ Volver al Perfil</a>

</div>

<?php include "../../comun/chatbot.php"; ?>
</body>
</html>