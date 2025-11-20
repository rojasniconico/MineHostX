<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../usuario/panel.php");
    exit();
}

$id = intval($_GET["id"]);

mysqli_query($conn, "DELETE FROM faq WHERE id=$id");

header("Location: faq.php?deleted=1");
exit();
