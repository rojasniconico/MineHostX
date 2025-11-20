<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$categoria = $_GET["categoria"] ?? "todas";

// Filtro de categorías
$where = ($categoria !== "todas") ? "WHERE categoria='" . mysqli_real_escape_string($conn, $categoria) . "'" : "";

// Obtener temas
$temas = mysqli_query($conn, "
    SELECT t.id, t.titulo, t.categoria, t.created_at, u.username
    FROM foro_temas t
    JOIN users u ON u.id = t.user_id
    $where
    ORDER BY t.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>💬 Foro - Comunidad MineHostX</title>
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
    max-width:1000px;
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
.actions {
    text-align:center;
    margin-bottom:20px;
}
.actions a {
    background:#4FC3F7;
    color:#000;
    text-decoration:none;
    padding:10px 14px;
    border-radius:8px;
    font-weight:bold;
}
.actions a:hover { background:#82DAFF; }

.filtros {
    text-align:center;
    margin-bottom:20px;
}
.filtros a {
    margin:0 8px;
    color:#4FC3F7;
    text-decoration:none;
}
.filtros a.active {
    text-decoration:underline;
    font-weight:bold;
}

.table {
    width:100%;
    border-collapse:collapse;
}
.table th, .table td {
    padding:12px;
    border-bottom:1px solid #333;
}
.table th {
    color:#4FC3F7;
    text-align:left;
}
.table tr:hover {
    background:#1c1c1c;
}
.categoria {
    background:#333;
    color:#4FC3F7;
    padding:4px 8px;
    border-radius:6px;
    font-size:0.85em;
}
.empty {
    text-align:center;
    color:#aaa;
    margin-top:20px;
}
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>💬 Foro de la Comunidad</h2>

    <div class="actions">
        <a href="publicar_tema.php">➕ Publicar nuevo tema</a>
    </div>

    <div class="filtros">
        <a href="?categoria=todas" class="<?= ($categoria=='todas'?'active':'') ?>">🌍 Todas</a>
        <a href="?categoria=general" class="<?= ($categoria=='general'?'active':'') ?>">💬 General</a>
        <a href="?categoria=soporte" class="<?= ($categoria=='soporte'?'active':'') ?>">🛠️ Soporte</a>
        <a href="?categoria=plugins" class="<?= ($categoria=='plugins'?'active':'') ?>">🔌 Plugins / Mods</a>
        <a href="?categoria=servidores" class="<?= ($categoria=='servidores'?'active':'') ?>">🖥️ Servidores</a>
        <a href="?categoria=ideas" class="<?= ($categoria=='ideas'?'active':'') ?>">💡 Ideas</a>
    </div>

    <table class="table">
        <tr>
            <th>Título</th>
            <th>Categoría</th>
            <th>Autor</th>
            <th>Fecha</th>
        </tr>
        <?php if (mysqli_num_rows($temas) > 0): ?>
            <?php while($t = mysqli_fetch_assoc($temas)): ?>
                <tr>
                    <td><a href="ver_tema.php?id=<?= $t["id"] ?>" style="color:#fff;"><?= htmlspecialchars($t["titulo"]) ?></a></td>
                    <td><span class="categoria"><?= ucfirst($t["categoria"]) ?></span></td>
                    <td><?= htmlspecialchars($t["username"]) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($t["created_at"])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="empty">No hay temas publicados todavía.</td></tr>
        <?php endif; ?>
    </table>

  <p><a style="display: block; margin: 0 auto; text-align: center;" href="index.php">⬅ Volver</a></p>
</div>



<?php include "../../comun/chatbot.php"; ?>

</body>
</html>

