<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$server_id = intval($_GET["id"]);
$user_id = $_SESSION["user_id"];

// comprobar servidor
$s = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT * FROM servers WHERE id=$server_id AND user_id=$user_id"
));

if ($s["software"] !== "Forge") {

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Mods no disponibles - MineHostX</title>
    <style>
        body {
            background:#121212;
            color:#fff;
            font-family:"Segoe UI";
            text-align:center;
            padding:0;
            margin:0;
        }
        .container {
            max-width:700px;
            margin:80px auto;
            background:#1E1E1E;
            padding:40px;
            border-radius:16px;
            box-shadow:0 6px 16px rgba(0,0,0,0.5);
            animation:fadeIn .4s ease;
        }
        @keyframes fadeIn {
            from { opacity:0; transform:scale(.92); }
            to   { opacity:1; transform:scale(1); }
        }
        .icon {
            font-size:80px;
            display:inline-block;
            margin-bottom:20px;
            animation:bounce 1.5s infinite;
        }
        @keyframes bounce {
            0%,100% { transform:translateY(0); }
            50% { transform:translateY(-10px); }
        }
        .title {
            color:#4FC3F7;
            font-size:26px;
            margin-bottom:10px;
        }
        .text {
            color:#ddd;
            font-size:18px;
            margin-bottom:25px;
        }
        .btn {
            background:#4FC3F7;
            color:#000;
            text-decoration:none;
            padding:12px 20px;
            font-weight:bold;
            border-radius:10px;
            display:inline-block;
            transition:.2s;
        }
        .btn:hover {
            background:#76d7ff;
            transform:scale(1.05);
        }
    </style>
    </head>
    <body>

    <div class="container">
        <div class="icon">🧩</div>
        <div class="title">Este servidor no usa Mods</div>
        <div class="text">
            Para añadir mods, el servidor debe estar configurado con <b>Forge</b>.<br>
            Actualmente está usando: <span style="color:#4FC3F7;">'.$s["software"].'</span>.
        </div>
        <a class="btn" href="ver_servidores.php?id='.$server_id.'">⬅ Volver al servidor</a>
    </div>

    </body>
    </html>
    ';
    exit;
}


// carpeta mods
$folder = "../../servidores/server_$server_id/mods/";

// subir mod
$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["mod"])) {
    $file = $_FILES["mod"];
    if (str_ends_with($file["name"], ".jar")) {
        $name = basename($file["name"]);
        move_uploaded_file($file["tmp_name"], $folder . $name);
        mysqli_query($conn, "INSERT INTO server_mods (server_id, filename) VALUES ($server_id, '$name')");

addPoints($conn, $user_id, 3, "Subió un mod");
giveAchievement($conn, $user_id, "MOD_LOVER");

        $msg = "✅ MOD subido correctamente";
    } else {
        $msg = "❌ Solo se permiten archivos .jar";
    }
}

$mods = mysqli_query($conn, "SELECT * FROM server_mods WHERE server_id=$server_id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mods - MineHostX</title>

<style>
body {
    background:#121212;
    color:#fff;
    font-family:'Segoe UI';
    text-align:center;
}
.container {
    max-width:900px;
    margin:40px auto;
}
h2 { color:#4FC3F7; }

/* DROPZONE bonito */
.dropzone {
    margin:20px auto;
    width:80%;
    padding:25px;
    border:3px dashed #4FC3F7;
    border-radius:12px;
    background:#1E1E1E;
    cursor:pointer;
    transition:0.2s;
}
.dropzone:hover {
    background:#262626;
    border-color:#76d7ff;
}
.dropzone input {
    display:none;
}

/* GRID de mods */
.grid {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
    gap:20px;
    margin-top:30px;
}

.mod-card {
    background:#1E1E1E;
    padding:15px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.4);
    transition:0.2s;
}
.mod-card:hover {
    transform:scale(1.03);
    background:#252525;
}

.mod-icon {
    width:70px;
    height:70px;
    background:#333;
    border-radius:10px;
    margin:10px auto;
    font-size:35px;
    display:flex;
    justify-content:center;
    align-items:center;
}

.mod-name {
    font-weight:bold;
    margin-top:5px;
}

.btn-delete {
    background:#e74c3c;
    padding:8px 12px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    color:#fff;
    font-weight:bold;
    margin-top:10px;
}

.btn-back {
    display:inline-block;
    margin-top:30px;
    background:#4FC3F7;
    color:#000;
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
}
</style>

</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="container">

<h2>🧩 Mods instalados en: <?php echo htmlspecialchars($s["name"]); ?><br></h2>

<!-- mensaje -->
<?php if ($msg): ?>
    <p style="color:#4FC3F7; font-weight:bold;"><?php echo $msg; ?></p>
<?php endif; ?>

<!-- DROPZONE -->
<form method="POST" enctype="multipart/form-data">
<label class="dropzone">
    <strong>📥 Arrastra aquí tus mods .jar o haz clic para seleccionar</strong>
    <input type="file" name="mod" id="modFile" onchange="uploadMod()">
</label>
</form>

<!-- GRID DE MODS -->
<div class="grid">

<?php 
$hay_mods = false;
while($m = mysqli_fetch_assoc($mods)): 
    $hay_mods = true;
?>
<div class="mod-card">
    <div class="mod-icon">🧩</div>
    <div class="mod-name"><?php echo htmlspecialchars($m["filename"]); ?></div>

    <a href="delete_mod.php?id=<?php echo $m["id"]; ?>&server=<?php echo $server_id; ?>">
        <button class="btn-delete">Eliminar</button>
    </a>
</div>
<?php endwhile; ?>

</div>

<?php if (!$hay_mods): ?>
    <p style="margin-top:30px; color:#aaa; font-size:1.1em;">
        ❗ Este servidor no tiene mods instalados.<br><br>
        ¡Sube un archivo .jar para añadir tu primer mod!
    </p>
<?php endif; ?>

<a class="btn-back" href="ver_servidores.php?id=<?php echo $server_id; ?>">⬅ Volver</a>

</div>

<script>
function uploadMod() {
    const form = new FormData();
    form.append("mod", document.getElementById("modFile").files[0]);

    fetch("", { method:"POST", body:form })
      .then(() => location.reload());
}
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include_once "../../comun/chatbot.php"; ?>

</body>
</html>
