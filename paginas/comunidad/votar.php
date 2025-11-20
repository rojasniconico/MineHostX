<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) exit("No logueado");

$user_id = $_SESSION["user_id"];
$server_id = intval($_POST["server_id"]);

$existe = mysqli_query($conn, "SELECT id FROM server_votes WHERE user_id=$user_id AND server_id=$server_id");
if (mysqli_num_rows($existe) == 0) {
    mysqli_query($conn, "INSERT INTO server_votes (user_id, server_id) VALUES ($user_id, $server_id)");
}
header("Location: servidores.php");
exit();
