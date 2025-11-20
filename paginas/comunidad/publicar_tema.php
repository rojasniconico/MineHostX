<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$msg = "";

// Publicar tema
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = mysqli_real_escape_string($conn, $_POST["titulo"]);
    $contenido = mysqli_real_escape_string($conn, $_POST["contenido"]);
    $categoria = mysqli_real_escape_string($conn, $_POST["categoria"]);

    if ($titulo && $contenido) {
        mysqli_query($conn, "
            INSERT INTO foro_temas (user_id, titulo, contenido, categoria, created_at)
            VALUES ($user_id, '$titulo', '$contenido', '$categoria', NOW())
        ");
        $msg = "✅ Tema publicado correctamente.";
    } else {
        $msg = "❌ Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📝 Publicar Tema - Comunidad MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<style>
body {
    background:#121212;
    color:#fff;
    font-family:'Segoe UI', sans-serif;
    margin:0;
    padding:0;
}
.container {
    max-width:800px;
    margin:100px auto;
    background:#1E1E1E;
    padding:25px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.5);
}
h2 {
    text-align:center;
    color:#4FC3F7;
}
label {
    display:block;
    margin-top:10px;
    color:#4FC3F7;
}
input[type=text], select, textarea {
    width:100%;
    padding:10px;
    border:none;
    border-radius:8px;
    background:#222;
    color:#fff;
    margin-top:5px;
}
button {
    margin-top:15px;
    background:#4FC3F7;
    color:#000;
    border:none;
    border-radius:8px;
    padding:10px 16px;
    font-weight:bold;
    cursor:pointer;
}
button:hover { background:#82DAFF; }
.msg {
    text-align:center;
    font-weight:bold;
    color:#4FC3F7;
    margin-bottom:10px;
}
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>📝 Publicar nuevo tema</h2>

    <?php if ($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>

    <form method="POST">
        <label for="titulo">Título del tema:</label>
        <input type="text" id="titulo" name="titulo" required>

        <label for="categoria">Categoría:</label>
        <select id="categoria" name="categoria" required>
            <option value="general">💬 General</option>
            <option value="soporte">🛠️ Soporte</option>
            <option value="plugins">🔌 Plugins / Mods</option>
            <option value="servidores">🖥️ Servidores</option>
            <option value="ideas">💡 Ideas y sugerencias</option>
        </select>

        <label for="contenido">Contenido:</label>
        <textarea id="contenido" name="contenido" rows="6" required placeholder="Escribe aquí tu mensaje..."></textarea>

        <button type="submit">📢 Publicar tema</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        <a href="foro.php" style="color:#4FC3F7;">⬅ Volver al foro</a>
    </p>
</div>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>
