<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Obtener todos los servidores publicados, incluyendo el conteo de votos
$servers_result = mysqli_query($conn, "
    SELECT ps.*, u.username,
           (SELECT COUNT(*) FROM server_votes WHERE server_id = ps.id) AS votos
    FROM public_servers ps
    JOIN users u ON ps.user_id = u.id
    ORDER BY votos DESC, ps.created_at DESC
");
$servers = mysqli_fetch_all($servers_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🌍 Servidores Públicos - MineHostX Comunidad</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<style>
/* --- Estilos Base --- */
body {
    background:#121212;
    color:#fff;
    font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    padding:0;
    margin:0;
}
.container {
    max-width:1200px;
    margin:90px auto 40px;
    background:#1E1E1E;
    padding:30px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.5);
}

h2 { text-align:center; color:#FF9800; margin-bottom: 10px; }
.subtitle { text-align:center; color:#aaa; margin-bottom: 30px; }

/* --- Botón Publicar --- */
.action-area {
    text-align: center;
    margin-bottom: 40px;
}
.btn-publish {
    background:#4CAF50;
    color:#fff;
    padding:12px 25px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s;
}
.btn-publish:hover { background: #66BB6A; }

/* --- GRID de Servidores --- */
.servers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.server-card {
    background:#191919;
    padding:20px;
    border-radius:12px;
    transition:0.2s;
    border-left: 5px solid #4FC3F7;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.server-card:hover { 
    background:#252525; 
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.4);
}

.server-card h3 {
    margin: 0 0 5px 0;
    color: #4FC3F7;
    font-size: 1.6em;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px dashed #333;
}
.card-meta p {
    margin: 0;
    font-size: 0.9em;
    color: #aaa;
}
.card-meta strong {
    color: #ccc;
}

.server-description {
    margin: 15px 0;
    font-size: 1em;
    color: #ddd;
    min-height: 50px;
}

/* --- Conexión y Votos --- */
.connection-info {
    background: #333;
    padding: 10px;
    border-radius: 8px;
    margin: 15px 0;
    font-size: 0.9em;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.connection-info code {
    background: #111;
    padding: 4px 8px;
    border-radius: 4px;
    color: #FF9800;
    font-weight: bold;
}

.vote-area {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}
.vote-count {
    font-size: 1.2em;
    font-weight: bold;
    color: #FFC107;
}
.btn-vote {
    background:#FF9800;
    color:#000;
    padding:8px 15px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
    transition: background 0.2s;
}
.btn-vote:hover { background: #FFB74D; }

/* Botón Volver */
.back-link {
    display: block;
    margin: 40px auto 20px;
    text-align: center;
    color: #4FC3F7;
    text-decoration: none;
}
.back-link:hover { text-decoration: underline; }
</style>
</head>
<body>
<?php include "../../comun/navbar.php"; ?>

<div class="container">
    <h2>🌎 Lista de Servidores Públicos de la Comunidad</h2>
    <p class="subtitle">¡Únete a la aventura! Explora servidores creados por otros miembros.</p>

    <div class="action-area">
        <a href="publicar_servidor.php" class="btn-publish">📢 Publicar mi servidor y que otros se unan</a>
    </div>
    
    <hr style="border-color: #333; margin-top: 0; margin-bottom: 40px;">

    <h3>⭐ Top Servidores de la Comunidad (<?= count($servers) ?> activos)</h3>

    <?php if (empty($servers)): ?>
        <div style="text-align:center; padding:30px; background:#2A2A2A; border-radius:10px;">
            <p>Aún no hay servidores públicos listados. ¡Sé el primero en publicar el tuyo!</p>
        </div>
    <?php else: ?>
        <div class="servers-grid">
            <?php foreach($servers as $s): ?>
                <div class="server-card">
                    <h3><?= htmlspecialchars($s["nombre"]) ?></h3>
                    
                    <div class="card-meta">
                        <p>Creador: <strong><?= htmlspecialchars($s["username"]) ?></strong></p>
                        <p>Versión: <strong><?= htmlspecialchars($s["version"]) ?></strong></p>
                    </div>

                    <div class="server-description">
                        <p><?= nl2br(htmlspecialchars($s["descripcion"])) ?></p>
                    </div>

                    <div class="connection-info">
                        <span>🔗 Conexión Directa:</span>
                        <code><?= htmlspecialchars($s["ip"]) ?></code>
                    </div>

                    <div class="vote-area">
                        <span class="vote-count">⭐ <?= $s["votos"] ?> votos</span>
                        <form method="POST" action="votar.php">
                            <input type="hidden" name="server_id" value="<?= $s["id"] ?>">
                            <button type="submit" class="btn-vote">¡Votar!</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<a class="back-link" href="index.php">⬅ Volver al inicio de la Comunidad</a>

<?php include "../../comun/chatbot.php"; ?>
</body>
</html>
