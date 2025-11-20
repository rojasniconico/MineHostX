<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}
require_once "../../db.php";

$uid = intval($_SESSION["user_id"]);

// ===============================
// 🔔 SISTEMA DE NOTIFICACIONES
// ===============================

// Creamos la tabla user_notifications si no existe
mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    seen TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 🔄 Insertar notificación si hay evento nuevo activo y aún no la tiene el usuario
$eventCheck = mysqli_query($conn, "
    SELECT id, name, description, ends_at 
    FROM events 
    WHERE active=1 AND ends_at > NOW()
");
if ($eventCheck && mysqli_num_rows($eventCheck) > 0) {
    while ($ev = mysqli_fetch_assoc($eventCheck)) {
        $msg = "Nuevo evento activo: {$ev['name']} - finaliza el " . date("d/m/Y", strtotime($ev['ends_at']));
        $exists = mysqli_query($conn, "SELECT id FROM user_notifications WHERE user_id=$uid AND message='" . mysqli_real_escape_string($conn, $msg) . "'");
        if ($exists && mysqli_num_rows($exists) == 0) {
            mysqli_query($conn, "INSERT INTO user_notifications (user_id, message) VALUES ($uid, '" . mysqli_real_escape_string($conn, $msg) . "')");
        }
    }
}

// Obtener notificaciones no leídas (máx 10)
$resNotif = mysqli_query($conn, "
    SELECT id, message, created_at 
    FROM user_notifications 
    WHERE user_id=$uid AND seen=0 
    ORDER BY created_at DESC 
    LIMIT 10
");
$notificaciones = $resNotif ? mysqli_fetch_all($resNotif, MYSQLI_ASSOC) : [];
$notif_count = count($notificaciones);
?>

<link rel="stylesheet" href="../../archivos/css/panel.css">

<style>
.topbar {
    background: #1E1E1E;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.4);
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    z-index: 1000;
    box-sizing: border-box;
}
.topbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.topbar-left h2 {
    margin: 0;
    font-size: 1.3em;
    color: #4FC3F7;
}
.icon {
    cursor: pointer;
    font-size: 1.5em;
    margin-left: 15px;
    color: #4FC3F7;
    transition: 0.2s;
}
.icon:hover { color: #82DAFF; }

/* 🔔 Panel de notificaciones */
.notif-container {
    position: relative;
    display: inline-block;
}
.notif-bell {
    position: relative;
    cursor: pointer;
    font-size: 1.5em;
}
.notif-count {
    position: absolute;
    top: -6px; right: -8px;
    background: #e74c3c;
    color: #fff;
    font-size: 0.7em;
    font-weight: bold;
    border-radius: 50%;
    padding: 2px 6px;
}
.notif-panel {
    display: none;
    position: absolute;
    top: 30px; right: 0;
    background: #1E1E1E;
    color: #fff;
    width: 300px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.6);
    border-radius: 12px;
    overflow: hidden;
    z-index: 999;
}
.notif-panel.active { display: block; animation: fadeIn .3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

.notif-header {
    background: #4FC3F7;
    color: #000;
    padding: 8px 12px;
    font-weight: bold;
}
.notif-item {
    padding: 10px 12px;
    border-bottom: 1px solid #333;
    font-size: 0.9em;
}
.notif-item:hover { background: #252525; }
.notif-empty { padding: 12px; text-align: center; color: #aaa; font-style: italic; }

/* Menú lateral */
.sidebar {
    position: fixed;
    top: 0; left: -300px;
    width: 250px;
    height: 100%;
    background: #1E1E1E;
    color: #fff;
    padding: 20px;
    box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    transition: 0.3s;
    z-index: 999;
    overflow-y: auto;
    padding-bottom: 40px;
}
.sidebar.active { left: 0; }
.sidebar h3 { color: #4FC3F7; border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 0; }
.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    padding: 10px 0;
    border-bottom: 1px solid #222;
    transition: 0.2s;
}
.sidebar a:hover { color: #4FC3F7; }

.overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 998;
}
.overlay.active { display: block; }

.nav-points {
    background:#FFD54F;
    padding:6px 12px;
    border-radius:10px;
    color:#000;
    font-weight:bold;
    margin-left:15px;
}
/* Añade este estilo a la sección <style> de navbar.php */
.close-btn {
    position: absolute; /* Para posicionarlo en la esquina superior */
    top: 10px; right: 10px;
    font-size: 1.5em;
    color: #FF4D4D; /* Rojo para destacar que es un cierre */
    cursor: pointer;
    font-weight: bold;
    border: none;
    background: none;
    transition: color 0.2s;
}
.close-btn:hover {
    color: #FF9800;
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

<div class="topbar">
    <div class="topbar-left">
        <span class="icon" id="menu-btn">☰</span>
        <h2>MineHostX</h2>
    </div>

    <div style="display:flex;align-items:center;gap:20px;">
        <!-- 🔔 Notificaciones -->
        <div class="notif-container">
            <span class="notif-bell" id="notif-btn">🔔</span>
            <?php if ($notif_count > 0): ?>
                <span class="notif-count"><?= $notif_count ?></span>
            <?php endif; ?>

            <div class="notif-panel" id="notif-panel">
                <div class="notif-header">Notificaciones</div>
                <?php if ($notif_count > 0): ?>
                    <?php foreach($notificaciones as $n): ?>
                        <div class="notif-item"><?= htmlspecialchars($n["message"]) ?></div>
                    <?php endforeach; ?>
                    <div style="text-align:center;padding:8px;">
                        <a href="../../paginas/usuario/notificaciones.php" style="color:#4FC3F7;">Ver todas</a>
                    </div>
                <?php else: ?>
                    <div class="notif-empty">Sin notificaciones nuevas</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ⭐ Puntos del usuario -->
        <?php
        $res_points = mysqli_query($conn, "SELECT points FROM users WHERE id=$uid");
        if ($res_points && $u = mysqli_fetch_assoc($res_points)) {
            echo '<div class="nav-points">⭐ '.$u["points"].' pts</div>';
        }
        ?>
    </div>
</div>

<!-- 📋 Menú lateral -->
<div class="sidebar" id="sidebar">
    <div class="close-btn" id="close-btn">✖</div>
    <h3>Menú</h3>
    <a href="../../paginas/usuario/panel.php">🏠 Panel principal</a>
    <a href="../../paginas/usuario/servidores.php">🖥️ Mis servidores</a>
    <a href="../../paginas/usuario/crear_servidor.php">➕ Crear servidor</a>
    <a href="../../paginas/usuario/planes.php">💼 Mi plan</a>
    <a href="../../paginas/usuario/eventos.php">📆 Eventos</a>
    <a href="../../paginas/usuario/tienda.php">🛍️ Tienda</a>
    <a href="../../paginas/usuario/perfil.php">👤 Perfil</a>
    <a href="../../paginas/comunidad/">🌍 Comunidad</a>
    <a href="../../paginas/usuario/asistente.php">🤖 Asistente</a>
    <a href="../../paginas/soporte/soporte.php">❓ Soporte</a>
    <a href="../../autenticacion/cerrar_sesion.php">🚪 Cerrar sesión</a>

    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
        <h3 style="margin-top:20px;">Admin</h3>
        <a href="../../paginas/admin/panel_admin.php">⚙️ Panel Admin</a>
        <a href="../../paginas/admin/usuarios.php">👥 Usuarios</a>
        <a href="../../paginas/admin/planes.php">💼 Planes</a>
        <a href="../../paginas/admin/servidores.php">🖥️ Servidores</a>
        <a href="../../paginas/admin/control_docker.php">🐋 Control Docker</a>
        <a href="../../paginas/admin/logs.php">📜 Logs del sistema</a>
        <a href="../../paginas/admin/estadisticas.php">📈 Estadísticas</a>
        <a href="../../paginas/admin/gestion_eventos.php">📆 Gestión de eventos</a>
        <a href="../../paginas/admin/notificaciones_admin.php">🔔 Notificaciones</a>
        <a href="../../paginas/admin/chatbot_admin.php">🤖 Chatbot</a>
        <a href="../../paginas/admin/faq.php">❓ FAQ</a>
    <?php endif; ?>
</div>

<div class="overlay" id="overlay"></div>

<script>
const menuBtn = document.getElementById('menu-btn');
const closeBtn = document.getElementById('close-btn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const notifBtn = document.getElementById('notif-btn');
const notifPanel = document.getElementById('notif-panel');

menuBtn.onclick = () => {
    sidebar.classList.add('active');
    overlay.classList.add('active');
};
closeBtn.onclick = overlay.onclick = () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
};
notifBtn.onclick = (e) => {
    e.stopPropagation();
    notifPanel.classList.toggle('active');

    // Marcar como leídas
    if (notifPanel.classList.contains('active')) {
        fetch('../../comun/mark_notifications.php').then(() => {
            const count = document.querySelector('.notif-count');
            if (count) count.remove();
        });
    }
};
document.addEventListener('click', (e) => {
    if (!notifPanel.contains(e.target) && e.target !== notifBtn) {
        notifPanel.classList.remove('active');
    }
});
</script>
