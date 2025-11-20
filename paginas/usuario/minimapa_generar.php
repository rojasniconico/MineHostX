<?php
// No usar session_start() aquí (ya está iniciado en mundo.php)

if (!isset($_GET["id"])) return;

$id = intval($_GET["id"]);
$folder = "../../servidores/server_$id/";

// crear carpeta si no existe
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$minimap = $folder . "minimap.png";

// PNG placeholder en base64
$png_base64 =
"iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACt..."
."AAAAAElFTkSuQmCC";

file_put_contents($minimap, base64_decode($png_base64));

return;
?>
