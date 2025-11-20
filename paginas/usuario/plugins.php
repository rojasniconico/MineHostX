<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$server_id = intval($_GET["id"]);
$user_id = $_SESSION["user_id"];

// verificar servidor
$s = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM servers WHERE id=$server_id AND user_id=$user_id"
));

if (!$s) {
    die("❌ Servidor no encontrado");
}

// si NO usa Paper -> mensaje visual
if ($s["software"] !== "Paper") {

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Plugins no disponibles - MineHostX</title>
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
            margin-bottom:20px;
            animation:float 1.8s infinite ease-in-out;
        }
        @keyframes float {
            0%,100% { transform:translateY(0); }
            50%     { transform:translateY(-8px); }
        }
        .title {
            color:#4FC3F7;
            font-size:26px;
            margin-bottom:10px;
        }
        .text {
            color:#ccc;
            font-size:18px;
            line-height:1.5em;
            margin-bottom:30px;
        }
        .btn {
            background:#4FC3F7;
            padding:12px 20px;
            color:#000;
            font-weight:bold;
            text-decoration:none;
            border-radius:10px;
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
        <div class="icon">🔌</div>
        <div class="title">Este servidor no usa Plugins</div>
        <div class="text">
            Para usar plugins el servidor debe estar configurado con <b>Paper</b>.<br><br>
            Actualmente está usando: <span style="color:#4FC3F7;">'.$s["software"].'</span>.
        </div>
        <a class="btn" href="ver_servidores.php?id='.$server_id.'">⬅ Volver al servidor</a>
    </div>

    </body>
    </html>
    ';
    exit;
}


// subir plugin
$msg = "";
$folder = "../../servidores/server_$server_id/plugins/";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["plugin"])) {

    $file = $_FILES["plugin"];

    if ($file["type"] === "application/java-archive") {
        $name = basename($file["name"]);
        move_uploaded_file($file["tmp_name"], $folder . $name);

        mysqli_query($conn, 
            "INSERT INTO server_plugins (server_id, filename) 
             VALUES ($server_id, '$name')"
        );

        $msg = "✅ Plugin subido correctamente";
addPoints($conn, $user_id, 3, "Subió un plugin");
giveAchievement($conn, $user_id, "PLUGIN_MASTER");


    } else {
        $msg = "❌ Solo se permiten archivos .jar";
    }
}

$plugins = mysqli_query($conn, 
    "SELECT * FROM server_plugins WHERE server_id=$server_id"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Plugins - MineHostX</title>

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

.dropzone {
    margin:20px auto;
    width:80%;
    padding:25px;
    border:3px dashed #4FC3F7;
    border-radius:12px;
    background:#1E1E1E;
    cursor:pointer;
    transition:.2s;
}
.dropzone:hover {
    background:#262626;
    border-color:#76d7ff;
}
.dropzone input {
    display:none;
}

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
    transition:.2s;
}
.mod-card:hover {
    transform:scale(1.03);
    background:#252525;
}

.plugin-icon {
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

.plugin-name {
    font-weight:bold;
    margin-top:5px;
}

.btn-delete {
    background:#e74c3c;
    padding:8px 12px;
    border:none;
    border-radius:8px;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
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

<h2>🔌 Plugins instalados en: <?php echo htmlspecialchars($s["name"]); ?></h2>

<?php if ($msg): ?>
    <p style="color:#4FC3F7; font-weight:bold;"><?php echo $msg; ?></p>
<?php endif; ?>

<!-- DROPZONE -->
<form method="POST" enctype="multipart/form-data">
<label class="dropzone">
    📥 Arrastra o haz clic para subir un plugin .jar
    <input type="file" name="plugin" onchange="this.form.submit()">
</label>
</form>

<!-- GRID -->
<div class="grid">

<?php 
$hay_plugins = false;
while($p = mysqli_fetch_assoc($plugins)): 
    $hay_plugins = true;
?>
<div class="mod-card">
    <div class="plugin-icon">🔌</div>
    <div class="plugin-name"><?php echo htmlspecialchars($p["filename"]); ?></div>

    <a href="delete_plugin.php?id=<?php echo $p["id"]; ?>&server=<?php echo $server_id; ?>">
        <button class="btn-delete">Eliminar</button>
    </a>
</div>
<?php endwhile; ?>

</div>

<?php if (!$hay_plugins): ?>
    <p style="margin-top:30px; color:#aaa;">
        ❗ Este servidor no tiene plugins instalados.<br><br>
        ¡Sube un archivo .jar para añadir el primero!
    </p>
<?php endif; ?>

<a class="btn-back" href="ver_servidores.php?id=<?php echo $server_id; ?>">⬅ Volver</a>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include_once "../../comun/chatbot.php"; ?>

</body>
</html>

