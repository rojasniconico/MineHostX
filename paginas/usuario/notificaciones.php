<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$uid = intval($_SESSION["user_id"]);

// Marcar como leídas todas las notificaciones
mysqli_query($conn, "UPDATE user_notifications SET seen=1 WHERE user_id=$uid");

// Obtener todas las notificaciones (más recientes primero)
$res = mysqli_query($conn, "
    SELECT message, seen, created_at 
    FROM user_notifications 
    WHERE user_id=$uid 
    ORDER BY created_at DESC
");
$notificaciones = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🔔 Notificaciones - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">

<style>
body {
    background: #121212;
    color: #fff;
    font-family: "Segoe UI", sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 900px;
    margin: 100px auto 40px;
    background: #1E1E1E;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.5);
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

h2 {
    text-align: center;
    color: #4FC3F7;
    margin-bottom: 20px;
}

.notif-item {
    background: #191919;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;
    border-left: 4px solid #4FC3F7;
    transition: 0.2s;
}
.notif-item:hover {
    background: #232323;
}
.notif-item.read {
    border-left-color: #666;
    opacity: 0.8;
}
.notif-msg {
    font-size: 1em;
}
.notif-date {
    font-size: 0.85em;
    color: #aaa;
    margin-top: 5px;
}
.empty-box {
    text-align: center;
    color: #aaa;
    font-style: italic;
    margin-top: 30px;
}
.back-btn {
    display: inline-block;
    background: #4FC3F7;
    color: #000;
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    margin-top: 20px;
    transition: 0.2s;
}
.back-btn:hover {
    background: #82DAFF;
}
</style>
</head>

<body>
<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>🔔 Tus Notificaciones</h2>

    <?php if (count($notificaciones) > 0): ?>
        <?php foreach ($notificaciones as $n): ?>
            <div class="notif-item <?= $n["seen"] ? "read" : "" ?>">
                <div class="notif-msg"><?= htmlspecialchars($n["message"]) ?></div>
                <div class="notif-date">📅 <?= date("d/m/Y H:i", strtotime($n["created_at"])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-box">No tienes notificaciones por ahora 🚀</div>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="../usuario/panel.php" class="back-btn">⬅ Volver al panel</a>
    </div>
</div>

<?php include "../../comun/chatbot.php"; ?>
</body>
</html>
