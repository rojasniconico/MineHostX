<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// obtener mensajes recibidos
$inbox = mysqli_query($conn, "
    SELECT m.id, m.message, m.created_at, m.seen, u.username AS sender
    FROM private_messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = $user_id
    ORDER BY m.created_at DESC
");

// mensajes enviados
$sent = mysqli_query($conn, "
    SELECT m.id, m.message, m.created_at, u.username AS receiver
    FROM private_messages m
    JOIN users u ON m.receiver_id = u.id
    WHERE m.sender_id = $user_id
    ORDER BY m.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📬 Mensajes Privados — MineHostX</title>
<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; }
.container { max-width:900px; margin:50px auto; padding:20px; background:#1e1e1e; border-radius:12px; }
h2 { color:#4FC3F7; text-align:center; }
h3 {
    border-bottom: 2px solid #333;
    padding-bottom: 5px;
    margin-top: 40px;
    margin-bottom: 20px;
}
.btn { display:inline-block; padding:10px 16px; margin:10px 0; background:#4FC3F7; border-radius:8px; color:#000; text-decoration:none; font-weight:bold; }

.message-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.message-item {
    background: #222;
    padding: 15px;
    margin-bottom: 8px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left: 5px solid transparent; /* Para el estado */
    transition: background 0.2s;
}

.message-item:hover {
    background: #2a2a2a;
    cursor: pointer;
}

.message-details {
    flex-grow: 1; /* Ocupa el espacio central */
    padding-right: 20px;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.message-sender, .message-receiver {
    font-weight: bold;
    color: #4FC3F7; /* Color de MineHostX */
    font-size: 1.1em;
}

.message-date {
    font-size: 0.85em;
    color: #888;
}

.message-preview {
    color: #ccc;
    font-size: 0.9em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; /* Puntos suspensivos al final */
    max-width: 600px;
    display: block;
}

/* Estado del mensaje (no leído) */
.message-item.unread-msg {
    background: #2c343f; /* Fondo ligeramente diferente para destacar */
    border-left-color: #4FC3F7; /* Raya azul a la izquierda */
}

.message-item.unread-msg .message-sender {
    color: #fff;
}

.message-status {
    width: 60px; /* Espacio fijo para el estado */
    text-align: right;
    font-size: 0.8em;
    font-weight: bold;
}
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>
<div class="container">
<h2>📬 Mensajes Privados</h2>

<a class="btn" href="mensajes_enviar.php">✉️ Enviar mensaje</a>

<h3>📥 Recibidos</h3>
<ul class="message-list">
<?php 
if (mysqli_num_rows($inbox) == 0) {
    echo '<p style="text-align:center; color:#888;">No tienes mensajes en tu bandeja de entrada.</p>';
}
while($m = mysqli_fetch_assoc($inbox)): 
    // Clase para destacar si no está leído
    $class = $m['seen'] ? '' : 'unread-msg'; 
    
    // Aquí puedes añadir el enlace a la página de detalle del mensaje (si existe)
    // Ejemplo: <a href="mensaje_ver.php?id=<?= $m['id'] ?>


<li class="message-item <?= $class ?>">
    <div class="message-details">
        <div class="message-header">
            <span class="message-sender">De: <?= htmlspecialchars($m['sender']) ?></span>
            <span class="message-date"><?= date("d/m/Y H:i", strtotime($m["created_at"])) ?></span>



        </div>
        <span class="message-preview">
            <?= htmlspecialchars(substr($m['message'],0,100)) ?>
        </span>
    </div>
    <div class="message-status">
        <?= $m['seen'] ? "Leído" : "NUEVO" ?>
    </div>

</li>
<?php endwhile; ?>
</ul>

<h3>📤 Enviados</h3>
<ul class="message-list">
<?php 
if (mysqli_num_rows($sent) == 0) {
    echo '<p style="text-align:center; color:#888;">No has enviado ningún mensaje.</p>';
}
while($m = mysqli_fetch_assoc($sent)): ?>
<li class="message-item">
    <div class="message-details">
        <div class="message-header">
            <span class="message-receiver">Para: <?= htmlspecialchars($m['receiver']) ?></span>
            <span class="message-date"><?= date("d/m/Y H:i", strtotime($m["created_at"])) ?></span>
        </div>
        <span class="message-preview">
            <?= htmlspecialchars(substr($m['message'],0,100)) ?>
        </span>
    </div>

    </li>
<?php endwhile; ?>
</ul>
<ul class="message-list">
<?php 
if (mysqli_num_rows($inbox) == 0) {
    echo '<p style="text-align:center; color:#888;">No tienes mensajes en tu bandeja de entrada.</p>';
}
while($m = mysqli_fetch_assoc($inbox)): 
    $class = $m['seen'] ? '' : 'unread-msg'; 
?>

<?php endwhile; ?>
</ul>
  <p><a style="display: block; margin: 0 auto; text-align: center;" href="index.php">⬅ Volver</a></p>

</div>

<?php include "../../comun/chatbot.php"; ?>
</body>
</html>

</html>
