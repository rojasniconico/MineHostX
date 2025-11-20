<?php
// create_admin.php
require_once "../db.php";

// Datos del nuevo admin
$username = "admin";
$email = "admin@minehostx.local";
$password = "admin";
$hash = password_hash($password, PASSWORD_DEFAULT);

// Comprobar si ya existe un admin
$check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "UPDATE users SET password_hash='$hash', role='admin', plan_id=3 WHERE email='$email'");
    echo "✅ Usuario admin actualizado con éxito.<br>";
} else {
    $sql = "INSERT INTO users (username, email, password_hash, plan_id, role)
            VALUES ('$username', '$email', '$hash', 3, 'admin')";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Usuario admin creado correctamente.<br>";
    } else {
        echo "❌ Error: " . mysqli_error($conn);
    }
}

echo "<p>Usuario: <b>$email</b><br>Contraseña: <b>$password</b></p>";
echo "<p><b>IMPORTANTE:</b> elimina este archivo (create_admin.php) cuando hayas terminado por seguridad.</p>";
