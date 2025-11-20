<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$id = intval($_GET["id"]);

// verificar servidor
$server = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM servers WHERE id=$id AND user_id=$user_id
"));
if (!$server) die("❌ No tienes acceso a este servidor.");

// registrar comando
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cmd = trim($_POST["command"]);

    if ($cmd !== "") {
        // respuesta simulada
        $response = "Comando ejecutado: $cmd\n(Respuesta simulada del servidor)";

        mysqli_query($conn, "
            INSERT INTO server_console (server_id, user_id, command, response)
            VALUES ($id, $user_id, '".mysqli_real_escape_string($conn,$cmd)."', '".mysqli_real_escape_string($conn,$response)."')
        ");
    }

    header("Location: consola.php?id=$id");
    exit();
}

// obtener historial
$logs = mysqli_query($conn, "
    SELECT * FROM server_console WHERE server_id=$id ORDER BY created_at DESC LIMIT 50
");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Consola - MineHostX</title>

<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; text-align:center; }
.container { 
    width:80%; margin:auto; margin-top:30px; background:#1E1E1E; 
    padding:20px; border-radius:12px; 
}
.console-box {
    background:#000; color:#0f0; padding:15px; height:300px;
    overflow-y:auto; font-family:monospace; text-align:left;
    border-radius:6px; margin-bottom:15px;
}
input[type=text] {
    width:80%; padding:12px; border:none; border-radius:6px;
    background:#222; color:#fff;
}
button { padding:12px 20px; border:none; border-radius:8px; background:#4FC3F7; color:#000; font-weight:bold; cursor:pointer; }
a { color:#4FC3F7; }
</style>
</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="container">

<h2>🖥 Consola del servidor: <?php echo $server["name"]; ?></h2>

<div class="console-box">
<?php while ($l = mysqli_fetch_assoc($logs)): ?>
    <div>
        <span style="color:#4FC3F7">» <?php echo htmlspecialchars($l["command"]); ?></span><br>
        <span><?php echo nl2br(htmlspecialchars($l["response"])); ?></span>
        <hr style="border-color:#333">
    </div>
<?php endwhile; ?>
</div>

<form method="POST">
    <input type="text" name="command" placeholder="Escribe un comando...">
    <button type="submit">Enviar</button>
</form>

<p><a href="ver_servidores.php?id=<?php echo $id; ?>">⬅ Volver</a></p>

</div>

</body>
</html>
