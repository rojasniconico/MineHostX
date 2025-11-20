<?php
session_start();
require "../../db.php";

$user_id = $_SESSION["user_id"];
$server_id = intval($_GET["id"]);

$s = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM servers WHERE id=$server_id AND user_id=$user_id"
));

$file = $_POST["file"];
$content = $_POST["contenido"];

$base = realpath("../../servidores/server_$server_id");
$full = realpath($base . "/" . $file);

if (!$full || strpos($full, $base) !== 0) die("Bloqueado");

file_put_contents($full, $content);

header("Location: ver_archivo.php?id=$server_id&file=" . urlencode($file));
