<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$me = intval($_SESSION["user_id"]);
$msg = "";

// Procesar envío tradicional
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $to = intval($_POST["to"]);
    $message = trim($_POST["message"]);

    if ($to && $message !== "") {
        $safe = mysqli_real_escape_string($conn, $message);

        mysqli_query($conn, "INSERT INTO private_messages (sender_id, receiver_id, message)
                             VALUES ($me, $to, '$safe')");

        // Crear notificaciónLIN
       // mysqli_query($conn, "INSERT INTO user_notifications (user_id, message, link)
         //                    VALUES ($to, 'Nuevo mensaje recibido', '/paginas/comunidad/mensajes.php')");

        $msg = "✅ Mensaje enviado con éxito";
        $_POST = [];
    } else {
        $msg = "❌ Debes seleccionar usuario y escribir un mensaje.";
    }
}

$users = mysqli_query($conn, "SELECT id, username FROM users WHERE id != $me ORDER BY username");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Enviar Mensaje — Comunidad</title>
<style>
body { background:#121212; color:#fff; font-family:"Segoe UI"; margin:0; }
.container {
    max-width:700px; margin:80px auto; background:#1E1E1E; padding:25px;
    border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.45);
}
h2 { text-align:center; color:#4FC3F7; margin-bottom:18px; }
select, textarea {
    width:100%; padding:12px; border:none; border-radius:10px;
    background:#111; color:#fff; margin-top:10px; font-size:15px;
}
textarea { resize:none; min-height:130px; }
.btn {
    background:#4FC3F7; border:none; margin-top:12px; width:100%;
    padding:12px; border-radius:10px; color:#000; font-weight:bold;
    cursor:pointer; font-size:17px;
}
.btn:hover { background:#82DAFF; }

.user-preview {
    display:flex; align-items:center; margin-top:12px; gap:12px;
    background:#111; padding:12px; border-radius:10px;
}
.avatar {
    width:42px; height:42px; border-radius:50%; display:flex;
    align-items:center; justify-content:center;
    font-weight:bold; background:#4FC3F7; color:#000; font-size:20px;
}

.status-box { display:flex; justify-content:space-between; margin-top:10px; }
.counter { color:#aaa; font-size:0.9em; }

.attach-btn {
    background:#2a2a2a; border:1px solid #444; color:#4FC3F7;
    padding:8px 12px; border-radius:8px; cursor:pointer; font-size:0.9em;
}
.error { color:#ff6b6b; text-align:center; font-weight:bold; }
.success { color:#4FC3F7; text-align:center; font-weight:bold; }
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>📨 Enviar mensaje privado</h2>

    <?php if($msg): ?>
        <p class="<?= str_starts_with($msg,'❌') ? 'error':'success' ?>"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>👤 Elegir usuario:</label>
        <select name="to" id="toSelect" required onchange="updatePreview()">
            <option value="">— Selecciona usuario —</option>
            <?php while($u = mysqli_fetch_assoc($users)): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
            <?php endwhile; ?>
        </select>

        <div id="preview" class="user-preview" style="display:none;">
            <div class="avatar" id="avatar"></div>
            <div>
                <div id="uname" style="font-weight:bold;font-size:1.1em;"></div>
                <div id="ublock" style="font-size:0.9em;color:#aaa;">Estado: disponible</div>
            </div>
        </div>

        <label>✏️ Mensaje:</label>
        <textarea name="message" id="msgBox" maxlength="2000" placeholder="Escribe tu mensaje aquí..." required></textarea>

        <div class="status-box">
            <span class="counter"><span id="charCount">0</span>/2000</span>
            <button type="button" class="attach-btn" onclick="alert('Función de adjuntar próximamente ✔')">📎 Adjuntar archivo</button>
        </div>

        <button class="btn" type="submit">Enviar mensaje</button>
    </form>
  <p><a style="display: block; margin: 0 auto; text-align: center;" href="mensajes.php">⬅ Volver</a></p>

</div>

<script>
// Actualizar preview usuario
function updatePreview() {
    const select = document.getElementById("toSelect");
    const name = select.options[select.selectedIndex].text;
    if (!name || name.includes("Selecciona")) { document.getElementById("preview").style.display="none"; return; }

    document.getElementById("avatar").innerText = name[0].toUpperCase();
    document.getElementById("uname").innerText = name;
    document.getElementById("preview").style.display = "flex";
}

// Contador de caracteres
const msgBox = document.getElementById("msgBox");
const charCount = document.getElementById("charCount");
msgBox.addEventListener("input", () => {
    charCount.innerText = msgBox.value.length;
    msgBox.style.height = "auto";
    msgBox.style.height = msgBox.scrollHeight + "px";
});
</script>

<?php include "../../comun/chatbot.php"; ?>
</body>
</html>


