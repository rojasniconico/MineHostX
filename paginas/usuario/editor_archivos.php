<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$server_id = intval($_GET["id"]);

// Validar servidor
$server = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM servers WHERE id=$server_id AND user_id=$user_id"
));

if (!$server) {
    die("❌ Servidor no encontrado.");
}

// RUTA BASE
$basePath = realpath("../../servidores/server_$server_id");

// CARPETA ACTUAL
$path = isset($_GET["path"]) ? $_GET["path"] : "";
$currentPath = realpath($basePath . "/" . $path);

// Seguridad: evitar salir del servidor
if (strpos($currentPath, $basePath) !== 0) {
    die("❌ Acceso no permitido.");
}

// Listar archivos
$items = scandir($currentPath);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editor de Archivos</title>
<style>
body { background:#121212; color:white; font-family:'Segoe UI'; }
.container { max-width:900px; margin:30px auto; }
h2 { color:#4FC3F7; text-align:center; }
.file-box {
    background:#1E1E1E; padding:15px; border-radius:10px;
    margin:10px 0; display:flex; justify-content:space-between;
    align-items:center;
}
.file-box:hover { background:#272727; }
a { color:#4FC3F7; text-decoration:none; }
.btn {
    background:#4FC3F7; color:black; border:none; padding:8px 12px;
    border-radius:8px; cursor:pointer; font-weight:bold;
}
.btn-red { background:#e74c3c; color:white; }
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">

<h2>📁 Archivos del servidor: <?php echo htmlspecialchars($server["name"]); ?></h2>

<p>Ruta actual: <code><?php echo "/$path"; ?></code></p>

<?php if ($path != ""): ?>
    <p><a href="editor_archivos.php?id=<?php echo $server_id; ?>&path=<?php echo urlencode(dirname($path)); ?>">⬅ Subir</a></p>
<?php endif; ?>

<!-- LISTADO -->
<?php
foreach ($items as $item):
    if ($item === "." || $item === "..") continue;

    $full = $currentPath . "/" . $item;
    $relative = ($path === "" ? $item : "$path/$item");
?>
<div class="file-box">
    <strong><?php echo $item; ?></strong>

    <div>
        <?php if (is_dir($full)): ?>
            <a href="editor_archivos.php?id=<?php echo $server_id; ?>&path=<?php echo urlencode($relative); ?>" class="btn">📁 Abrir</a>
        <?php else: ?>
            <a class="btn" href="ver_archivo.php?id=<?php echo $server_id; ?>&file=<?php echo urlencode($relative); ?>">✏ Editar</a>
            <a class="btn" href="descargar_archivo.php?id=<?php echo $server_id; ?>&file=<?php echo urlencode($relative); ?>">⬇ Descargar</a>
        <?php endif; ?>
        <a class="btn-red" href="delete_archivo.php?id=<?php echo $server_id; ?>&file=<?php echo urlencode($relative); ?>" onclick="return confirm('¿Eliminar?');">🗑</a>
    </div>
</div>
<?php endforeach; ?>

<h3>⬆ Subir archivo</h3>
<form method="POST" action="upload_archivo.php?id=<?php echo $server_id; ?>&path=<?php echo urlencode($path); ?>" enctype="multipart/form-data">
    <input type="file" name="archivo" required>
    <button class="btn">Subir</button>
</form>

<a class="btn" href="ver_servidores.php?id=<?php echo $server_id; ?>">⬅ Volver</a>

</div>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>

</html>
