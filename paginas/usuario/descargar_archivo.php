<?php
session_start();
require "../../db.php";

$server_id = intval($_GET["id"]);
$file = $_GET["file"];

$user_id = $_SESSION["user_id"];

$s = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM servers WHERE id=$server_id AND user_id=$user_id"
));

$base = realpath("../../servidores/server_$server_id");
$full = realpath($base . "/" . $file);

if (!$full || strpos($full, $base) !== 0) die("No permitido");

header("Content-Disposition: attachment; filename=" . basename($full));
header("Content-Type: application/octet-stream");
readfile($full);
