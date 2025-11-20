<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

$query = mysqli_query($conn, "
    SELECT users.*, plans.name AS plan_name, plans.max_servers, plans.max_ram 
    FROM users 
    JOIN plans ON users.plan_id = plans.id 
    WHERE users.id = $user_id
");
$user = mysqli_fetch_assoc($query);

// Lista de servidores
$servers = mysqli_query($conn, "SELECT * FROM servers WHERE user_id = $user_id");
$total_servers = mysqli_num_rows($servers);

// Cálculo de RAM total utilizada
$ram_in_use = 0;
while ($srv_ram = mysqli_fetch_assoc($servers)) {
    $ram_in_use += isset($srv_ram['ram_gb']) ? intval($srv_ram['ram_gb']) : 0;
}
// Reset pointer
mysqli_data_seek($servers, 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Usuario - MineHostX</title>
<style>
/* === ESTILOS GENERALES === */
body {
    background:#121212;
    color:#fff;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin:0;
}

.header-welcome {
    background:#1E1E1E;
    padding:30px 40px;
    border-bottom:2px solid #333;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:65px;
}
.header-welcome h2 { margin:0; color:#4FC3F7; }
.header-welcome a { color:#FF9800; text-decoration:none; }
.header-welcome a:hover { text-decoration:underline; }

.container {
    padding:30px;
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:30px;
}

.card {
    background:#1e1e1e;
    padding:25px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.4);
}
.card h3 {
    margin-top:0;
    border-bottom:1px solid #333;
    padding-bottom:10px;
    color:#FF9800;
}

a { color:#4FC3F7; transition:0.2s; }
a:hover { color:#81D4FA; }

.btn-action {
    background:#4CAF50;
    border:none;
    padding:10px 20px;
    border-radius:8px;
    color:#fff;
    font-weight:bold;
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
}
.btn-action:hover { background:#66BB6A; }

.metrics-grid {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}
.metric-card {
    background:#272727;
    padding:15px;
    border-radius:8px;
    text-align:center;
}
.metric-card span {
    font-size:2em;
    font-weight:bold;
    color:#4FC3F7;
}
.metric-card p {
    margin:0;
    color:#aaa;
}

.server-list-item {
    display:flex;
    justify-content:space-between;
    background:#222;
    padding:10px 15px;
    border-radius:6px;
    margin-bottom:8px;
}
.server-list-item:hover { background:#2a2a2a; }

.server-list-item strong {
    color:#fff;
}

.status-indicator {
    padding:4px 8px;
    border-radius:4px;
    font-size:0.8em;
    color:#fff;
}
.status-running { background:#4CAF50; }
.status-stopped { background:#F44336; }
.status-pending { background:#FFC107; color:#000; }

footer {
    background:#0d0d0d;
    color:#aaa;
    text-align:center;
    padding:20px;
    margin-top:30px;
}
</style>
</head>

<body>
<?php include_once "../../comun/navbar.php"; ?>

<div class="header-welcome">
    <h2>🎮 Panel de Control</h2>
    <p>Hola, <strong><?= htmlspecialchars($user["username"]) ?></strong> |
    <a href="../../autenticacion/cerrar_sesion.php">Cerrar sesión</a></p>
</div>

<div class="container">

    <!-- SECCIÓN IZQUIERDA -->
    <div>
        <h3>📊 Resumen de Métricas</h3>

        <div class="metrics-grid">
            <div class="metric-card">
                <span><?= $total_servers ?> / <?= $user["max_servers"] ?></span>
                <p>Servidores creados</p>
            </div>
            <div class="metric-card">
                <span><?= $ram_in_use ?> / <?= $user["max_ram"] ?> GB</span>
                <p>RAM usada</p>
            </div>
        </div>

        <div class="card">
            <h3>🖥️ Servidores Activos</h3>
            <p>Tienes <strong><?= $total_servers ?></strong> servidores desplegados.</p>

            <a href="crear_servidor.php" class="btn-action">➕ Crear servidor</a>

            <div style="margin-top:20px;">
                <?php if ($total_servers == 0): ?>
                    <p style="text-align:center; color:#888;">Aún no tienes servidores. ¡Crea uno ahora!</p>
                <?php endif; ?>

                <?php while ($srv = mysqli_fetch_assoc($servers)): 
                    $status = $srv["status"] ?? "stopped";
                    $class = [
                        "running" => "status-running",
                        "pending" => "status-pending",
                        "stopped" => "status-stopped"
                    ][$status] ?? "status-stopped";
                ?>
                <div class="server-list-item">
                    <a href="ver_servidores.php?id=<?= $srv["id"] ?>">
                        <strong><?= htmlspecialchars($srv["name"]) ?></strong>
                    </a>
                    <span class="status-indicator <?= $class ?>">
                        <?= ucfirst(htmlspecialchars($status)) ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DERECHA -->
    <div>

        <div class="card">
            <h3>💼 Información del Plan</h3>
            <p><strong>Plan:</strong> <span style="color:#4FC3F7;"><?= $user["plan_name"] ?></span></p>
            <p><strong>RAM Máx.:</strong> <?= $user["max_ram"] ?> GB</p>
            <p><strong>Servidores Máx.:</strong> <?= $user["max_servers"] ?></p>
            <a href="planes.php" class="btn-action" style="background:#FF9800;">⚙️ Administrar Plan</a>
        </div>

        <div class="card">
            <h3>👤 Mi Cuenta</h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($user["email"]) ?></p>
            <p><strong>Usuario:</strong> <?= htmlspecialchars($user["username"]) ?></p>
            <a href="perfil.php">Ajustes de Perfil</a><br>
            <a href="../comunidad/mensajes.php">📬 Mis Mensajes</a>
        </div>

        <div class="card">
            <h3>📢 Soporte y Novedades</h3>
            <p style="color:#aaa;"><strong>Última noticia:</strong> Disponible PaperMC 1.21</p>
            <p style="color:#aaa;"><strong>Mantenimiento:</strong> Mañana 02:00 AM CET</p>
            <a href="../soporte/contacto.php" class="btn-action" style="background:#1976D2;">📞 Contactar Soporte</a>
        </div>

    </div>
</div>

<footer>
    © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
    <br><a href="../../index.php">Volver al inicio</a>
</footer>

<?php include_once "../../comun/chatbot.php"; ?>
</body>
</html>

