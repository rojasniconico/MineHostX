<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// ✅ Verificar que existe el tema
if (!isset($_GET["id"])) {
    header("Location: foro.php");
    exit();
}

$tema_id = intval($_GET["id"]);

// Obtener datos del tema
$tema = mysqli_query($conn, "
    SELECT t.*, u.username 
    FROM foro_temas t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = $tema_id
");
if (mysqli_num_rows($tema) == 0) {
    echo "<p style='color:white;text-align:center;margin-top:100px;'>❌ Tema no encontrado.</p>";
    exit();
}
$tema = mysqli_fetch_assoc($tema);

// ✅ Insertar nuevo comentario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["comentario"])) {
    $comentario = trim($_POST["comentario"]);
    if ($comentario !== "") {
        $comentario = mysqli_real_escape_string($conn, $comentario);
        mysqli_query($conn, "INSERT INTO foro_respuestas (tema_id, user_id, contenido) VALUES ($tema_id, $user_id, '$comentario')");
    }
}

// Obtener comentarios
$respuestas = mysqli_query($conn, "
    SELECT r.contenido, r.created_at, u.username 
    FROM foro_respuestas r
    JOIN users u ON u.id = r.user_id
    WHERE r.tema_id = $tema_id
    ORDER BY r.created_at ASC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>💬 <?= htmlspecialchars($tema["titulo"]) ?> - Foro MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<style>
body {
    background:#121212;
    color:#fff;
    font-family:'Segoe UI',sans-serif;
    margin:0;
    padding:0;
}
.container {
    max-width:900px;
    margin:100px auto;
    background:#1E1E1E;
    padding:25px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.5);
}
h2 {
    color:#4FC3F7;
    text-align:center;
}
.tema {
    background:#191919;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}
.tema-header {
    display:flex;
    justify-content:space-between;
    margin-bottom:10px;
}
.tema-header span {
    color:#aaa;
    font-size:0.9em;
}
.respuestas {
    margin-top:20px;
}
.respuesta {
    background:#222;
    padding:15px;
    border-radius:10px;
    margin-bottom:10px;
}
.respuesta small {
    color:#999;
    display:block;
    margin-bottom:5px;
}
form {
    margin-top:20px;
}
textarea {
    width:100%;
    padding:10px;
    background:#222;
    border:none;
    border-radius:8px;
    color:#fff;
    resize:none;
    height:100px;
}
button {
    background:#4FC3F7;
    color:#000;
    border:none;
    border-radius:8px;
    padding:10px 16px;
    font-weight:bold;
    margin-top:10px;
    cursor:pointer;
}
button:hover { background:#82DAFF; }
.volver {
    display:inline-block;
    margin-bottom:15px;
    color:#4FC3F7;
    text-decoration:none;
}
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">

    <a href="foro.php" class="volver">⬅ Volver al foro</a>

    <div class="tema">
        <div class="tema-header">
            <h2><?= htmlspecialchars($tema["titulo"]) ?></h2>
            <span><?= htmlspecialchars($tema["username"]) ?> • <?= date("d/m/Y H:i", strtotime($tema["created_at"])) ?></span>
        </div>
        <p><?= nl2br(htmlspecialchars($tema["contenido"])) ?></p>
        <p style="color:#4FC3F7;font-size:0.9em;margin-top:10px;">Categoría: <?= ucfirst($tema["categoria"]) ?></p>
    </div>

    <h3>💬 Respuestas</h3>
    <div class="respuestas">
        <?php if (mysqli_num_rows($respuestas) > 0): ?>
            <?php while($r = mysqli_fetch_assoc($respuestas)): ?>
                <div class="respuesta">
                    <small>👤 <?= htmlspecialchars($r["username"]) ?> • <?= date("d/m/Y H:i", strtotime($r["created_at"])) ?></small>
                    <p><?= nl2br(htmlspecialchars($r["contenido"])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:#aaa;">Aún no hay respuestas en este tema.</p>
        <?php endif; ?>
    </div>

    <form method="POST">
        <h3>✍️ Responder</h3>
        <textarea name="comentario" placeholder="Escribe tu respuesta aquí..." required></textarea>
        <button type="submit">Responder</button>
    </form>

</div>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>
