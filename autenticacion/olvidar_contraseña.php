<?php
session_start();
require_once "../db.php"; 

$msg = '';
$msg_class = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    if (empty($email)) {
        $msg = "❌ Por favor, ingresa tu dirección de correo electrónico.";
        $msg_class = 'error-msg';
    } else {
        // 1. Verificar si el correo existe
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
        
        if (mysqli_num_rows($check_email) > 0) {
            
            // SIMULACIÓN: En un entorno real, aquí se generaría un token único,
            // se almacenaría en una tabla temporal y se enviaría por correo.
            
            $msg = "✅ Si la dirección de correo electrónico existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.";
            $msg_class = 'success-msg';
            
            // Redirigir al login después de 5 segundos
            header("Refresh: 5; url=login.php");
        } else {
            // Por seguridad, siempre mostramos un mensaje genérico incluso si el correo no existe
            // para no revelar cuentas válidas.
            $msg = "✅ Si la dirección de correo electrónico existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.";
            $msg_class = 'success-msg';
            
            header("Refresh: 5; url=login.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña — MineHostX</title>
    <style>
        body { 
            background:#121212; 
            color:#fff; 
            font-family:'Segoe UI'; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container { 
            max-width:450px; 
            padding:40px; 
            background:#1e1e1e; 
            border-radius:12px; 
            text-align: center;
        }
        h2 { 
            color:#4FC3F7; 
            margin-bottom: 25px; 
        }
        form div { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold; 
            text-align: left;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #272727;
            color: #fff;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background:#4FC3F7; 
            padding:12px; 
            border-radius:6px; 
            border:none; 
            cursor:pointer; 
            font-weight:bold;
            color: #000;
            font-size: 1.1em;
            margin-top: 10px;
        }
        button:hover { background: #81D4FA; }
        .error-msg { color: #FF4D4D; margin-bottom: 20px; font-weight: bold; }
        .success-msg { color: #A5D6A7; margin-bottom: 20px; font-weight: bold; }
        a { color:#4FC3F7; text-decoration: none; display: block; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🔑 Recuperar Contraseña</h2>

    <?php if ($msg): ?>
        <p class="<?= $msg_class ?>"><?= $msg ?></p>
        <?php if ($msg_class == 'success-msg'): ?>
            <p style="color: #ccc; font-size: 0.9em;">Serás redirigido al inicio de sesión en 5 segundos.</p>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="email">Ingresa tu correo electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <button type="submit">Enviar Enlace de Recuperación</button>
    </form>
    
    <a href="login.php">⬅ Volver al Login</a>

</div>

</body>
</html>