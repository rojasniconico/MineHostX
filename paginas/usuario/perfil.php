
<?php
session_start();
require_once "../../db.php";
require_once "../../comun/puntos.php";
include "../../comun/referrals.php";



if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// obtener datos del usuario
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT username, email, points, plan_id, role 
    FROM users 
    WHERE id = $user_id
"));

// obtener o generar código de invitación
$ref_code = getOrCreateReferralCode($conn, $user_id);
$invite_link = "http://" . $_SERVER["HTTP_HOST"] . "/autenticacion/registro.php?ref=" . $ref_code;

// obtener historial de puntos
$history = mysqli_query($conn, "
    SELECT descripcion, puntos, fecha 
    FROM puntos_historial 
    WHERE user_id = $user_id 
    ORDER BY fecha DESC LIMIT 50
");

// calcular progreso hacia siguiente nivel
$next_level = ceil(($user["points"] + 1) / 100) * 100;
$progress = ($user["points"] / $next_level) * 100;
if ($progress > 100) $progress = 100;

// PUNTOS DEL USUARIO
$mis_puntos = $user["points"] ?? 0;

// INSIGNIAS DEL USUARIO
$badges = mysqli_query($conn, "
    SELECT b.name, b.icon, b.description, ub.received_at
    FROM user_badges ub
    INNER JOIN badges b ON b.id = ub.badge_id
    WHERE ub.user_id = $user_id
    ORDER BY ub.received_at DESC
");


// logros (solo lectura)
$achievements = mysqli_query($conn, "
    SELECT name, description, progress, goal 
    FROM logros 
    WHERE user_id = $user_id
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil - MineHostX</title>

<style>
body {
    background:#121212;
    color:#fff;
    font-family:"Segoe UI";
    margin:0;
    padding:0;
}

.container {
    max-width:1000px;
    margin:40px auto;
    background:#1E1E1E;
    padding:25px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.5);
    animation:fadeIn .4s ease;
}

@keyframes fadeIn {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}

h2 {
    text-align:center;
    color:#4FC3F7;
}

.section {
    background:#191919;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
}

.label { color:#4FC3F7; font-weight:bold; }

.progress-bar {
    background:#333;
    border-radius:10px;
    height:20px;
    overflow:hidden;
    margin-top:10px;
}
.progress {
    height:100%;
    background:#4FC3F7;
    width:0%;
    transition:1s;
}

.badges {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}
.badge {
    background:#222;
    padding:12px;
    border-radius:10px;
    width:150px;
    text-align:center;
    box-shadow:0 4px 10px rgba(0,0,0,0.4);
}
.badge-icon {
    font-size:40px;
}

.achievement {
    margin-bottom:15px;
    padding:15px;
    background:#222;
    border-radius:10px;
}

.invite-box {
    background:#222;
    padding:15px;
    border-radius:12px;
    margin-top:10px;
}
.invite-link {
    background:#111;
    padding:8px;
    border-radius:8px;
    color:#4FC3F7;
    display:block;
    overflow-wrap:anywhere;
}

.btn-copy {
    background:#4FC3F7;
    color:#000;
    padding:6px 12px;
    border:none;
    border-radius:8px;
    margin-top:8px;
    cursor:pointer;
    font-weight:bold;
}

.table {
    width:100%;
    margin-top:15px;
}
.table th {
    color:#4FC3F7;
    text-align:left;
    padding:8px;
}
.table td {
    padding:8px;
    border-bottom:1px solid #333;
}

/* Footer */
footer {
  background: #0d0d0d;
  color: #aaa;
  text-align: center;
  padding: 20px;
  font-size: 0.9em;
}
footer a {
  color: #4FC3F7;
  text-decoration: none;
}

</style>

</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="container">

<h2>👤 Perfil de <?php echo htmlspecialchars($user["username"]); ?></h2>

<!-- ✅ Información del usuario -->
<div class="section">
    <p><span class="label">Correo:</span> <?php echo $user["email"]; ?></p>
    <p><span class="label">Plan actual:</span> <?php echo $user["plan_id"]; ?></p>
    <p><span class="label">Rol:</span> <?php echo $user["role"]; ?></p>
<a href="cambiar_contraseña.php" class="action-btn">Cambiar Contraseña</a>
</div>

<!-- ✅ Información del usuario -->
    <div class="section">
<h3>Integración con Discord (Simulada)</h3>
        <p>Conecta tu cuenta de Discord para recibir notificaciones en tiempo real sobre el estado de tus servidores.</p>
        <p>Estado actual: <span class="discord-status disconnected">Desconectado</span></p>
        
        <a href="#" class="discord-connect-btn" onclick="alert('Simulación: Serías redirigido a Discord para autorizar la conexión. ¡Conexión exitosa!')">
            🔗 Conectar Discord
        </a>
    </div>
<!-- ✅ Puntos + barra de progreso -->
<div class="section">
    <h3>⭐ Tus puntos</h3>
    <h1 style="color:#4FC3F7;"><?php echo $user["points"]; ?> puntos</h1>
    <p>Próxima recompensa en: <b><?php echo $next_level; ?> puntos</b></p>

    <div class="progress-bar">
        <div class="progress" style="width:<?php echo $progress; ?>%"></div>
    </div>
</div>

<!-- ✅ Insignias -->
<div class="section">
    <h3>🏅 Insignias obtenidas</h3>

    <div class="badges">
        <?php if (mysqli_num_rows($badges) == 0): ?>
            <p>No tienes insignias todavía.</p>
        <?php else: ?>
            <?php while($b = mysqli_fetch_assoc($badges)): ?>
            <div class="badge">
                <div class="badge-icon"><?php echo $b["icon"]; ?></div>
                <div><b><?php echo $b["badge_name"]; ?></b></div>
                <div style="font-size:12px;color:#aaa;"><?php echo $b["badge_desc"]; ?></div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ✅ Logros -->
<div class="section">
    <h3>🎯 Logros</h3>

    <?php while($a = mysqli_fetch_assoc($achievements)): ?>
        <div class="achievement">
            <b><?php echo $a["name"]; ?></b><br>
            <span style="color:#aaa;"><?php echo $a["description"]; ?></span>

            <div class="progress-bar" style="margin-top:8px;">
                <div class="progress" style="width:<?php echo ($a["progress"] / $a["goal"]) * 100; ?>%"></div>
            </div>

            <small><?php echo $a["progress"]; ?>/<?php echo $a["goal"]; ?></small>
        </div>
    <?php endwhile; ?>
</div>

<!-- ✅ Invitación -->
<div class="section">
    <h3>🔗 Tu enlace de invitación</h3>

    <div class="invite-box">
        <span class="invite-link" id="inviteLink"><?php echo $invite_link; ?></span>

        <button class="btn-copy" onclick="copyInvite()">Copiar enlace</button>
    </div>
</div>

<!-- ✅ Historial de puntos -->
<div class="section">
    <h3>📘 Historial de puntos</h3>

    <table class="table">
        <tr>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Puntos</th>
        </tr>

        <?php while($h = mysqli_fetch_assoc($history)): ?>
        <tr>
            <td><?php echo $h["fecha"]; ?></td>
            <td><?php echo $h["descripcion"]; ?></td>
            <td style="color:#4FC3F7;"><?php echo $h["puntos"]; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<p>Canjear puntos: <a href="canjear.php">Canjea aquí</a></p>


</div>

<script>
function copyInvite() {
    let text = document.getElementById("inviteLink").innerText;
    navigator.clipboard.writeText(text);
    alert("✅ Enlace copiado");
}
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>


