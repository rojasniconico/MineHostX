<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$id = intval($_GET["id"]);

// Validar servidor
$server = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM servers WHERE id=$id AND user_id=$user_id
"));
if (!$server) die("❌ No tienes acceso a este servidor");

$serverFolder = "../../servidores/server_$id/";
$minimapPath = $serverFolder . "minimap.png";

// ✅ Crear minimapa si no existe
if (!file_exists($minimapPath)) {
    $_GET["id"] = $id;
    include "minimapa_generar.php";
}

// ✅ Regenerar minimapa
if (isset($_POST["regen"])) {
    $_GET["id"] = $id;
    include "minimapa_generar.php";
    header("Location: mundo.php?id=$id");
    exit();
}

// Datos ficticios hasta Docker
$fake_world = [
    "size" => rand(80, 300) . " MB",
    "seed" => strtoupper(substr(md5($id), 0, 12)),
    "modified" => date("Y-m-d H:i:s"),
    "chunks" => rand(1000, 3500),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mundo del Servidor - MineHostX</title>

<style>
body {
    background:#121212;
    color:#fff;
    font-family:'Segoe UI';
    padding:0;
    margin:0;
}
.container {
    max-width:900px;
    margin:40px auto;
    background:#1E1E1E;
    padding:20px;
    border-radius:12px;
}
h2 {
    text-align:center;
    color:#4FC3F7;
}
.section {
    background:#191919;
    padding:15px;
    border-radius:10px;
    margin-top:20px;
}
.label {
    font-weight:bold;
    color:#4FC3F7;
}
.value {
    color:#ddd;
}
.btn {
    background:#4FC3F7;
    border:none;
    padding:10px 16px;
    color:#000;
    font-weight:bold;
    border-radius:8px;
    cursor:pointer;
    margin:5px;
}
.btn:disabled {
    background:#2c2c2c;
    color:#777;
}
.world-map {
    width:100%;
    max-width:600px;
    background:#111;
    margin:auto;
    display:block;
    border-radius:10px;
    border:3px solid #333;
}
.note {
    color:#aaa;
    font-size:0.9em;
    margin-top:8px;
    text-align:center;
}
</style>

</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="container">

<h2>🌍 Mundo del Servidor: <?php echo htmlspecialchars($server["name"]); ?></h2>

<div class="section">
    <p><span class="label">Tamaño del mundo:</span> <span class="value"><?php echo $fake_world["size"]; ?></span></p>
    <p><span class="label">Seed:</span> <span class="value"><?php echo $fake_world["seed"]; ?></span></p>
    <p><span class="label">Última modificación:</span> <span class="value"><?php echo $fake_world["modified"]; ?></span></p>
    <p><span class="label">Chunks generados:</span> <span class="value"><?php echo $fake_world["chunks"]; ?></span></p>
</div>

<!-- ✅ Minimapa -->
<div class="section">
    <h3>🗺️ Minimapa del Mundo</h3>

    <img class="world-map" src="<?php echo $minimapPath . '?v=' . time(); ?>">

    <form method="POST" style="text-align:center; margin-top:15px;">
        <button class="btn" name="regen">🔄 Regenerar minimapa</button>
    </form>

    <p class="note">El minimapa real se activará cuando Docker esté integrado.</p>
</div>

<!-- ✅ Opciones -->
<div class="section">
    <h3>⚙ Opciones del mundo</h3>

    <button class="btn" disabled>📦 Descargar mundo</button>
    <button class="btn" disabled>🔧 Reparar mundo</button>
    <button class="btn" disabled>⏳ Optimizar chunks</button>
    <a href="editor_archivos.php?id=<?php echo $id; ?>"><button>📄 Editor de archivos</button></a>


    <p class="note">Estas funciones estarán disponibles cuando la integración con Docker esté activa.</p>
</div>

<p style="text-align:center;">
    <a class="btn" href="ver_servidores.php?id=<?php echo $id; ?>">⬅ Volver</a>
</p>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include_once "../../comun/chatbot.php"; ?>

</body>
</html>
