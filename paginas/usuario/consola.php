<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$server_id = intval($_GET["id"]);

// verificar servidor
$server = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT * FROM servers WHERE id=$server_id AND user_id=$user_id
"));
if (!$server) die("❌ No tienes acceso a este servidor.");

// simular respuesta
function simularRespuesta($cmd) {
    $cmd = strtolower($cmd);

    if (strpos($cmd, "say") === 0)
        return "[Servidor] Mensaje enviado al chat.";

    if (strpos($cmd, "list") === 0)
        return "Jugadores conectados: 0";

    if (strpos($cmd, "time set") === 0)
        return "Tiempo establecido correctamente.";

    if (strpos($cmd, "stop") === 0)
        return "¡Servidor deteniéndose! (simulación)";

    return "Comando ejecutado (simulado).";
}

// procesar comando enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cmd = trim($_POST["command"]);
    if ($cmd !== "") {
        $respuesta = simularRespuesta($cmd);

        mysqli_query($conn,"
            INSERT INTO server_console (server_id, user_id, command, response)
            VALUES ($server_id, $user_id, '$cmd', '$respuesta')
        ");
    }

    header("Location: consola.php?id=$server_id");
    exit();
}

$logs = mysqli_query($conn,"
    SELECT * FROM server_console WHERE server_id=$server_id ORDER BY id DESC LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Consola — <?php echo $server["name"]; ?></title>

<style>
body {
    background:#0d0d0d; color:#fff; font-family:Consolas, monospace; margin:0;
}

.terminal {
    width:80%;
    margin:30px auto;
    background:#1b1b1b;
    padding:20px;
    border-radius:12px;
    height:500px;
    overflow-y:auto;
    font-size:14px;
    line-height:1.4em;
    box-shadow:0 0 10px #000;
}

.input-area {
    margin:20px auto;
    width:80%;
    display:flex;
}

.input-area input {
    flex:1;
    background:#101010;
    border:none;
    padding:12px;
    color:#4FC3F7;
    border-radius:8px 0 0 8px;
    font-family:Consolas;
}

.input-area button {
    background:#4FC3F7;
    border:none;
    padding:12px 20px;
    border-radius:0 8px 8px 0;
    cursor:pointer;
    font-weight:bold;
    color:#000;
}

.line {
    margin-bottom:5px;
}
.cmd { color:#4FC3F7; }
.response { color:#a0ffa0; }
</style>

</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<h2 style="text-align:center; color:#4FC3F7;">🖥 Consola — <?php echo $server["name"]; ?></h2>

<div class="terminal" id="terminal">
<?php while($l = mysqli_fetch_assoc($logs)): ?>
    <div class="line">
        <span class="cmd">> <?php echo htmlspecialchars($l["command"]); ?></span><br>
        <span class="response"><?php echo htmlspecialchars($l["response"]); ?></span>
    </div>
<?php endwhile; ?>
</div>

<form method="POST" class="input-area">
    <input type="text" name="command" placeholder="Escribe un comando..." autofocus>
    <button type="submit">Enviar</button>
</form>

<script>
// auto scroll
var term = document.getElementById("terminal");
term.scrollTop = term.scrollHeight;
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

</body>
</html>

