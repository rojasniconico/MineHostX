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
    $res = mysqli_query($conn, "SELECT id FROM servers WHERE ip='$domain' LIMIT 1");
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
    $name = trim($_POST["name"]);
    $ram = intval($_POST["ram"]);
    $software = $_POST["software"];
    $version = $_POST["version"];

    $countRes = mysqli_query($conn, "SELECT COUNT(*) AS c FROM servers WHERE user_id=$user_id");
    $currentServers = mysqli_fetch_assoc($countRes)["c"] ?? 0;

    if ($currentServers >= $maxServers) {
        $msg = "❌ Has alcanzado el límite de servidores de tu plan ({$maxServers}).";
    } elseif ($ram > $maxRam) {
        $msg = "❌ Tu plan no permite tanta RAM (máximo {$maxRam} GB).";
    } elseif (($software === "Forge" && !$allowMods) || ($software === "Paper" && !$allowPlugins)) {
        $msg = "❌ Tu plan no permite este tipo de software.";
    } else {
        $port = generarPuertoLibre($conn);
        $ip = generarDominio($conn);

        $insert = mysqli_query($conn, "
            INSERT INTO servers (user_id, name, port, status, ram_gb, software, version, ip)
            VALUES ($user_id, '".mysqli_real_escape_string($conn,$name)."', $port, 'stopped', $ram,
                    '".mysqli_real_escape_string($conn,$software)."', '".mysqli_real_escape_string($conn,$version)."',
                    '".mysqli_real_escape_string($conn,$ip)."')
        ");

        if ($insert) {
            $sid = mysqli_insert_id($conn);
            @mkdir("../../servidores/server_$sid/mods", 0777, true);
            @mkdir("../../servidores/server_$sid/plugins", 0777, true);

            addPoints($conn, $user_id, 20, "Creó un servidor");
            giveAchievement($conn, $user_id, "FIRST_SERVER");

            // redirigir a listado tras éxito
            $_SESSION["success_msg"] = "✅ Servidor creado correctamente: <b>$ip:$port</b>";
            header("Location: servidores.php");
            exit();
        } else {
            $msg = "❌ Error al crear servidor: " . mysqli_error($conn);
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
body { background:#121212; color:#fff; font-family:'Segoe UI'; text-align:center; }
form { background:#1E1E1E; padding:30px; margin-top:40px; display:inline-block; border-radius:12px; width:350px; }
input, select { padding:10px; margin:10px; width:90%; border:none; border-radius:6px; background:#222; color:#fff; }
button { background:#4FC3F7; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold; }
.toast {
  position:fixed; top:20px; right:20px;
  background:#1e1e1e; color:#fff; border-left:4px solid #4FC3F7;
  padding:12px 18px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.4);
  opacity:0; transform:translateY(-10px);
  transition:.4s;
  z-index:9999;
}
.toast.show { opacity:1; transform:translateY(0); }
.toast.error { border-color:#ff6b6b; }
</style>
<script>
function checkSoftware(){
  const s=document.querySelector("select[name='software']").value;
  document.getElementById("modsBox").style.display=(s==="Forge")?"block":"none";
  document.getElementById("pluginsBox").style.display=(s==="Paper")?"block":"none";
}
</script>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<h2>➕ Crear Nuevo Servidor</h2>

<form method="POST">
  <input type="text" name="name" placeholder="Nombre del servidor" required>
  <label>RAM:</label>
  <input type="number" name="ram" min="1" max="<?= $maxRam ?>" value="<?= $default_ram ?>">

  <label>Software:</label>
  <select name="software" onchange="checkSoftware()" <?= (!$allowMods && !$allowPlugins) ? "disabled" : "" ?>>
    <option value="Vanilla" <?= $default_software=="Vanilla"?"selected":"" ?>>Vanilla</option>
    <?php if($allowPlugins): ?><option value="Paper">Paper</option><?php endif; ?>
    <?php if($allowMods): ?><option value="Forge">Forge</option><?php endif; ?>
  </select>

  <label>Versión:</label>
  <select name="version">
    <option>1.20.1</option>
    <option>1.19.4</option>
    <option>1.18.2</option>
  </select>

  <div id="modsBox" style="display:none;">✅ Este servidor podrá usar <b>MODS</b>.</div>
  <div id="pluginsBox" style="display:none;">✅ Este servidor podrá usar <b>PLUGINS</b>.</div>

  <button type="submit">Crear Servidor</button>
<br><p><a href="panel.php">Volver al panel</a></p>
</form>

<?php if($msg): ?>
<div class="toast <?= str_starts_with($msg,'❌')?'error':'' ?> show" id="toast"><?= $msg ?></div>
<script>
setTimeout(()=>document.getElementById("toast").classList.remove("show"),5000);
</script>
<?php endif; ?>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
<?php include_once "../../comun/chatbot.php"; ?>
</body>
</html>

