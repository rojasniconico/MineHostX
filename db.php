<?php
// config/db.php

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = ""; // cambia si tienes contraseña
$DB_NAME = "minehostx";

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die("❌ Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Establecer charset UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>
