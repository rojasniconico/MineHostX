<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$msg = "";

// --- Enviar una notificación ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mensaje"])) {
    $mensaje = mysqli_real_escape_string($conn, $_POST["mensaje"]);
    $target  = intval($_POST["target"]);

    if ($mensaje === "") {
        $msg = "⚠️ Escribe un mensaje antes de enviar.";
    } else {
        if ($target === 0) {
            // Enviar a todos los usuarios
            $resUsuarios = mysqli_query($conn, "SELECT id FROM users");
            if ($resUsuarios === false) {
                $msg = "Error DB: " . mysqli_error($conn);
            } else {
                while ($u = mysqli_fetch_assoc($resUsuarios)) {
                    mysqli_query($conn, "INSERT INTO user_notifications (user_id, message, created_at) VALUES ({$u['id']}, '$mensaje', NOW())");
                }
                $msg = "✅ Notificación enviada a todos los usuarios.";
            }
        } else {
            $q = "INSERT INTO user_notifications (user_id, message, created_at) VALUES ($target, '$mensaje', NOW())";
            if (mysqli_query($conn, $q)) {
                $msg = "✅ Notificación enviada al usuario seleccionado.";
            } else {
                $msg = "Error DB: " . mysqli_error($conn);
            }
        }
    }
}

// --- Obtener notificaciones recientes ---
// pedimos el resultset y COMPROBAMOS que sea mysqli_result antes de hacer fetch_assoc
$sqlNotifs = "
    SELECT n.id, u.username, n.message, n.created_at, n.seen
    FROM user_notifications n
    JOIN users u ON n.user_id = u.id
    ORDER BY n.created_at DESC
    LIMIT 100
";
$resNotifs = mysqli_query($conn, $sqlNotifs);
if ($resNotifs === false) {
    // Si hay error en la consulta, lo mostramos en $notifError (no romperá la página)
    $notifError = "Error al listar notificaciones: " . mysqli_error($conn);
    $notificacionesRows = []; // vacío para no romper el bucle
} else {
    // mantenemos el mysqli_result para usar mysqli_fetch_assoc correctamente
    $notificacionesRows = $resNotifs;
}

// --- Obtener lista de usuarios (para el selector del formulario) ---
$resUsuariosList = mysqli_query($conn, "SELECT id, username FROM users ORDER BY username ASC");
if ($resUsuariosList === false) {
    $usuariosList = [];
    $usersError = "Error al obtener usuarios: " . mysqli_error($conn);
} else {
    $usuariosList = $resUsuariosList;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📢 Administrar Notificaciones</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<style>
body { background:#121212; color:#fff; font-family:"Segoe UI", sans-serif; margin:0; padding:0; }
.container { max-width:1000px; margin:100px auto 40px; background:#1E1E1E; padding:25px; border-radius:16px; box-shadow:0 6px 18px rgba(0,0,0,0.5); }
h2 { text-align:center; color:#4FC3F7; }
form { background:#191919; padding:20px; border-radius:12px; margin-bottom:30px; }
label { color:#4FC3F7; display:block; margin-bottom:6px; }
select, textarea { width:100%; padding:10px; border:none; border-radius:8px; background:#222; color:#fff; margin-bottom:10px; }
button { background:#4FC3F7; color:#000; border:none; border-radius:8px; padding:10px 16px; font-weight:bold; cursor:pointer; }
button:hover { background:#82DAFF; }
.table { width:100%; border-collapse:collapse; }
.table th, .table td { padding:10px; border-bottom:1px solid #333; }
.table th { color:#4FC3F7; text-align:left; }
.status-dot { display:inline-block; width:10px; height:10px; border-radius:50%; }
.status-read { background:#888; }
.status-unread { background:#4FC3F7; }
.msg { text-align:center; color:#4FC3F7; font-weight:bold; }
.error { text-align:center; color:#f66; font-weight:bold; }
</style>
</head>
<body>
<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>📢 Administración de Notificaciones</h2>

    <?php if(!empty($msg)): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <?php if(!empty($notifError)): ?><p class="error"><?= htmlspecialchars($notifError) ?></p><?php endif; ?>
    <?php if(!empty($usersError)): ?><p class="error"><?= htmlspecialchars($usersError) ?></p><?php endif; ?>

    <form method="POST">
        <label>👤 Enviar a:</label>
        <select name="target" required>
            <option value="0">Todos los usuarios</option>
            <?php
            // iteramos sobre $usuariosList SOLO si es un mysqli_result
            if ($usuariosList instanceof mysqli_result) {
                while($u = mysqli_fetch_assoc($usuariosList)) {
                    echo '<option value="'.intval($u['id']).'">'.htmlspecialchars($u['username']).'</option>';
                }
            }
            ?>
        </select>

        <label>✉️ Mensaje:</label>
        <textarea name="mensaje" rows="4" placeholder="Escribe aquí el contenido de la notificación..." required></textarea>

        <button type="submit">📨 Enviar notificación</button>
    </form>

    <h3>🕒 Notificaciones recientes</h3>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Mensaje</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>

        <?php
        // iteramos sobre $notificacionesRows SOLO si es mysqli_result
        if ($notificacionesRows instanceof mysqli_result) {
            while($n = mysqli_fetch_assoc($notificacionesRows)) {
                echo '<tr>';
                echo '<td>'.intval($n["id"]).'</td>';
                echo '<td>'.htmlspecialchars($n["username"]).'</td>';
                echo '<td>'.htmlspecialchars($n["message"]).'</td>';
                echo '<td>'.htmlspecialchars(date("d/m/Y H:i", strtotime($n["created_at"]))).'</td>';
                echo '<td>'.(($n["seen"]) ? '<span class="status-dot status-read"></span> Leída' : '<span class="status-dot status-unread"></span> Nueva').'</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5" class="notif-empty">No hay notificaciones (o fallo en la consulta).</td></tr>';
        }
        ?>
    </table>

    <div style="text-align:center;margin-top:20px;">
        <a href="../admin/panel_admin.php" style="color:#4FC3F7;text-decoration:none;">⬅ Volver al panel admin</a>
    </div>
</div>

<?php include "../../comun/chatbot.php"; ?>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>

