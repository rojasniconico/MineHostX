<?php
session_start();
require_once "../db.php";
require_once "notificaciones.php";

if (!isset($_SESSION["user_id"])) exit;
$user_id = intval($_SESSION["user_id"]);

markNotificationsAsRead($conn, $user_id);
echo "OK";
?>
