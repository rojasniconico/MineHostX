<?php
session_start();
require_once "../../db.php";
require_once "../../comun/puntos.php";
require_once "../../comun/achievements.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);
$msg = "";

/* ============================================================
   Cargar plan actual
============================================================ */
$userPlanRow = mysqli_query($conn, "
    SELECT plans.* 
    FROM users
    JOIN plans ON users.plan_id = plans.id
    WHERE users.id = $user_id
");
$userPlan = mysqli_fetch_assoc($userPlanRow);

if (!$userPlan) {
    $userPlan = [
        "id" => 1,
        "name" => "Madera",
        "max_servers" => 1,
        "max_ram" => 2,
        "allow_mods" => 0,
        "allow_plugins" => 0
    ];
}

$maxServers = intval($userPlan["max_servers"]);
$maxRam = intval($userPlan["max_ram"]);
$allowMods = intval($userPlan["allow_mods"]);
$allowPlugins = intval($userPlan["allow_plugins"]);

$default_ram = intval($_GET["ram"] ?? 2);
$default_software = $_GET["software"] ?? "Vanilla";
$default_version = $_GET["version"] ?? "1.20.1";

/* ============================================================
   Utilidades
============================================================ */
function generarDominio($conn) {
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $sub = substr(str_shuffle(str_repeat($chars, 8)), 0, 8);
    $domain = "$sub.minehostx.es";
    $res = mysqli_query($conn, "SELECT id FROM servers WHERE ip='".mysqli_real_escape_string($conn,$domain)."' LIMIT 1");
    return (mysqli_num_rows($res) > 0) ? generarDominio($conn) : $domain;
}

function generarPuertoLibre($conn) {
    $port = rand(25000, 26000);
    $res = mysqli_query($conn, "SELECT id FROM servers WHERE port=$port LIMIT 1");
    return (mysqli_num_rows($res) > 0) ? generarPuertoLibre($conn) : $port;
}

/* ============================================================
   Crear servidor
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? '');
    $ram = intval($_POST["ram"] ?? 2);
    $software = $_POST["software"] ?? "Vanilla";
    $version = $_POST["version"] ?? "1.20.1";

    if ($name === "") {
        $msg = "❌ El nombre no puede estar vacío.";
    } else {
        $countRes = mysqli_query($conn, "SELECT COUNT(*) AS c FROM servers WHERE user_id=$user_id");
        $currentServers = intval(mysqli_fetch_assoc($countRes)["c"] ?? 0);

        if ($currentServers >= $maxServers) {
            $msg = "❌ Alcanzaste el límite de servidores ({$maxServers}).";
        } elseif ($ram > $maxRam) {
            $msg = "❌ Tu plan solo permite hasta {$maxRam} GB de RAM.";
        } elseif (($software === "Forge" && !$allowMods) || ($software === "Paper" && !$allowPlugins)) {
            $msg = "❌ Tu plan no permite este tipo de software.";
        } else {
            $port = generarPuertoLibre($conn);
            $ip = generarDominio($conn);

            $safe_name = mysqli_real_escape_string($conn, $name);
            $safe_software = mysqli_real_escape_string($conn, $software);
            $safe_version = mysqli_real_escape_string($conn, $version);
            $safe_ip = mysqli_real_escape_string($conn, $ip);

            $sql = "
                INSERT INTO servers (user_id, name, port, status, ram_gb, software, version, ip)
                VALUES ($user_id, '$safe_name', $port, 'stopped', $ram, '$safe_software', '$safe_version', '$safe_ip')
            ";
            $insert = mysqli_query($conn, $sql);

            if ($insert) {
                $sid = mysqli_insert_id($conn);

                $ROOT_PATH = realpath(__DIR__ . "/../../");
                $serverPath = $ROOT_PATH . "/servidores/server_$sid";
                @mkdir($serverPath . "/mods", 0777, true);
                @mkdir($serverPath . "/plugins", 0777, true);
                @mkdir($serverPath . "/data", 0777, true);

                $compose = <<<YAML
version: '3.9'
services:
  minecraft_$sid:
    image: itzg/minecraft-server
    container_name: server_$sid
    ports:
      - '$port:25565'
    environment:
      EULA: 'TRUE'
      TYPE: '$safe_software'
      VERSION: '$safe_version'
      MEMORY: '{$ram}G'
    volumes:
      - '$serverPath/data:/data'
      - '$serverPath/mods:/data/mods'
      - '$serverPath/plugins:/data/plugins'
    restart: unless-stopped
YAML;

                file_put_contents("$serverPath/docker-compose.yml", $compose);

                $composeArg = escapeshellarg("$serverPath/docker-compose.yml");
                exec("docker compose -f $composeArg up -d 2>&1", $out, $rc);

                if ($rc !== 0) {
                    $_SESSION["success_msg"] = "⚠ Servidor creado pero NO iniciado. Docker error: " . htmlspecialchars(implode("\n", $out));
                } else {
                    mysqli_query($conn, "UPDATE servers SET status='running' WHERE id=$sid");
                    $_SESSION["success_msg"] = "✅ Servidor creado y arrancado correctamente.";
                }

                addPoints($conn, $user_id, 20, "Creó un servidor");
                giveAchievement($conn, $user_id, "FIRST_SERVER");

                header("Location: ver_servidores.php");
                exit();
            } else {
                $msg = "❌ Error al crear servidor: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Servidor — MineHostX</title>

<style>
/* ============================================================
   ESTILO GLASS UI PREMIUM
============================================================ */
body {
    background: url('https://images.unsplash.com/photo-1535223289827-42f1e9919769?auto=format&fit=crop&w=1600&q=80') no-repeat center/cover fixed;
    backdrop-filter: blur(4px);
    color: #fff;
    font-family: 'Segoe UI', sans-serif;
    text-align: center;
    padding-top: 70px;
    margin: 0;
}

/* Fondo difuminado oscuro */
.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(8px);
    z-index: -1;
}

/* Contenedor principal */
.container {
    max-width: 520px;
    margin: auto;
}

/* Caja glass */
.glass-box {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    box-shadow: 0 0 30px rgba(0,0,0,0.45);
    backdrop-filter: blur(15px);
    border-radius: 18px;
    padding: 40px;
    margin-bottom: 50px;
    animation: fadeIn .6s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to   { opacity: 1; transform: translateY(0); }
}

h2 {
    font-size: 1.8em;
    color: #4FC3F7;
    margin-bottom: 10px;
}

/* Labels */
label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
    color: #AEE8FF;
}

/* Inputs */
input, select {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border-radius: 10px;
    border: none;
    background: rgba(255,255,255,0.08);
    color: #fff;
    font-size: 1em;
    outline: none;
    backdrop-filter: blur(4px);
    transition: .2s;
}

input:focus, select:focus {
    background: rgba(255,255,255,0.15);
}

/* Botón */
button {
    width: 100%;
    margin-top: 25px;
    padding: 14px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4FC3F7, #0288D1);
    border: none;
    color: #000;
    font-weight: bold;
    font-size: 1.2em;
    cursor: pointer;
    transition: .25s;
}

button:hover {
    transform: scale(1.03);
    background: linear-gradient(135deg, #81D4FA, #03A9F4);
}

/* Notas de software */
.software-note {
    display: none;
    padding: 12px;
    margin-top: 15px;
    border-radius: 12px;
    font-size: .9em;
    backdrop-filter: blur(6px);
    background: rgba(255,255,255,0.12);
}

#modsBox { border-left: 4px solid #4CAF50; color: #A5E6A5; }
#pluginsBox { border-left: 4px solid #4FC3F7; color: #AEE8FF; }

/* Toast */
.toast {
    position: fixed;
    top: 25px;
    right: 25px;
    padding: 15px 25px;
    border-radius: 12px;
    background: rgba(30,30,30,0.85);
    color: #fff;
    border-left: 4px solid #4FC3F7;
    backdrop-filter: blur(10px);
    box-shadow: 0 0 15px rgba(0,0,0,0.45);
    opacity: 1;
    animation: slideFade 5s forwards;
}

.toast.error { border-color: #F44336; color: #F44336; }

@keyframes slideFade {
    0% { opacity: 0; transform: translateX(20px); }
    10% { opacity: 1; transform: translateX(0); }
    90% { opacity: 1; }
    100% { opacity: 0; transform: translateX(20px); }
}
</style>

<script>
function checkSoftware(){
    const s = document.querySelector("select[name='software']").value;
    const mods = document.querySelector("select[name='software']").dataset.allowMods;
    const plugins = document.querySelector("select[name='software']").dataset.allowPlugins;

    document.getElementById("modsBox").style.display = (s === "Forge" && mods === "1") ? "block" : "none";
    document.getElementById("pluginsBox").style.display = (s === "Paper" && plugins === "1") ? "block" : "none";
}

window.onload = checkSoftware;
</script>
</head>
<body>

<div class="overlay"></div>

<?php include "../../comun/navbar.php"; ?>

<div class="container">
    
    <div class="glass-box">

        <h2>✨ Crear Nuevo Servidor</h2>
        <p style="color:#ccc;margin-top:0">
            Plan: <b><?= htmlspecialchars($userPlan["name"]) ?></b>  
            &nbsp; | &nbsp; LIMITE RAM: <b><?= $maxRam ?> GB</b>
        </p>

        <form method="POST">

            <label>Nombre del servidor</label>
            <input type="text" name="name" required placeholder="Ej: Mundo de Aventuras">

            <label>RAM asignada (GB)</label>
            <input type="number" name="ram" min="1" max="<?= $maxRam ?>" value="<?= $default_ram ?>">

            <label>Software</label>
            <select name="software" onchange="checkSoftware()" 
                    data-allow-mods="<?= $allowMods ?>" 
                    data-allow-plugins="<?= $allowPlugins ?>">
                <option value="Vanilla">Vanilla</option>
                <?php if($allowPlugins): ?><option value="Paper">Paper (Plugins)</option><?php endif; ?>
                <?php if($allowMods): ?><option value="Forge">Forge (Mods)</option><?php endif; ?>
            </select>

            <label>Versión</label>
            <select name="version">
                <option value="1.20.1">1.20.1</option>
                <option value="1.19.4">1.19.4</option>
                <option value="1.18.2">1.18.2</option>
            </select>

            <div id="modsBox" class="software-note">🔧 Con Forge podrás usar <b>MODS</b>.</div>
            <div id="pluginsBox" class="software-note">⚙ Con Paper podrás usar <b>PLUGINS</b>.</div>

            <button type="submit">🚀 Crear Servidor</button>
        </form>

        <p style="margin-top:20px;">
            <a href="ver_servidores.php" style="color:#81D4FA;">Volver atrás</a>
        </p>

    </div>
</div>

<?php if($msg): ?>
<div class="toast <?= str_starts_with($msg,'❌')?'error':'' ?>"><?= $msg ?></div>
<?php endif; ?>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>
