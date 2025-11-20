<?php
// paginas/usuario/download_backup.php
session_start();
require_once "../../db.php";
if (!isset($_SESSION['user_id'])) exit();

$server = intval($_GET['server'] ?? 0);
$file = basename($_GET['file'] ?? '');
$path = __DIR__ . "/../../servidores/server_$server/backups/$file";

if (!file_exists($path)) die("No existe");

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$file.'"');
readfile($path);
exit();
