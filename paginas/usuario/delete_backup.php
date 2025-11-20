<?php
// paginas/usuario/delete_backup.php
session_start();
require_once "../../db.php";
if (!isset($_SESSION['user_id'])) exit();

$server = intval($_GET['server'] ?? 0);
$file = basename($_GET['file'] ?? '');
$path = __DIR__ . "/../../servidores/server_$server/backups/$file";

if (file_exists($path)) unlink($path);
mysqli_query($conn, "DELETE FROM backups WHERE server_id=$server AND file_path='".mysqli_real_escape_string($conn,$file)."'");
header("Location: backups.php?id=$server");
exit();
