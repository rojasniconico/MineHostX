<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// Obtener el plan actual del usuario
$current_res = mysqli_query($conn, "
    SELECT users.plan_id, plans.name 
    FROM users 
    JOIN plans ON users.plan_id = plans.id 
    WHERE users.id = $user_id
");
$current = mysqli_fetch_assoc($current_res);

// Obtener todos los planes ordenados por precio
$plans_res = mysqli_query($conn, "SELECT * FROM plans ORDER BY price ASC");
$plans = mysqli_fetch_all($plans_res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>💼 Planes y Precios - MineHostX</title>

<style>
/* --- ESTILOS BASE --- */
body {
    background: #121212;
    color: #fff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding-top: 60px; /* espacio para navbar fijo */
}

h2 {
    color: #4FC3F7;
    margin-bottom: 5px;
}

.container {
    width: 95%;
    max-width: 1200px;
    margin: 20px auto;
    text-align: center;
}

/* Mensaje de éxito */
.success {
    color: #A5D6A7;
    background: #1a2a1a;
    padding: 15px;
    margin-bottom: 25px;
    border-radius: 8px;
    font-weight: bold;
    border: 1px solid #4CAF50;
}

/* --- TABLA DE PLANES --- */
.plans-grid {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 40px;
}

.plan-card {
    background: #1E1E1E;
    border-radius: 12px;
    width: 300px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.5);
    transition: transform 0.3s, box-shadow 0.3s;
    overflow: hidden;
}

.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.6);
}

/* Cabecera del plan */
.header-plan {
    padding: 25px 20px;
    background: #FF9800;
    color: #000;
}
.header-plan h3 {
    margin: 0;
    font-size: 1.8em;
}

/* Plan actual */
.current-plan {
    border: 3px solid #4FC3F7;
    transform: scale(1.05);
}
.current-plan .header-plan {
    background: #4FC3F7;
    color: #fff;
}

/* Características */
.features {
    padding: 20px;
}
.features p {
    margin: 10px 0;
    padding: 8px 0;
    border-bottom: 1px dashed #272727;
    display: flex;
    justify-content: space-between;
}
.features p:last-child {
    border-bottom: none;
}

.price {
    font-size: 2.5em;
    font-weight: bold;
    color: #A5D6A7;
    margin: 15px 0 25px 0;
}
.price small {
    font-size: 0.5em;
    color: #aaa;
}

/* Botones */
.btn-action {
    background: #4CAF50;
    padding: 12px 30px;
    border-radius: 8px;
    color: #fff;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
    margin: 20px 0 30px 0;
    transition: background 0.2s;
}
.btn-action:hover {
    background: #66BB6A;
}

.disabled {
    background: #555 !important;
    cursor: default !important;
    color: #ccc;
    font-weight: normal;
}

/* Footer */
footer {
    background: #0d0d0d;
    color: #aaa;
    text-align: center;
    padding: 20px;
    margin-top: 50px;
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

<h2>💼 Selecciona el plan que mejor se adapte a tu comunidad</h2>

<p>Tu plan actual: <strong><?= htmlspecialchars($current["name"]) ?></strong></p>

<?php if (isset($_GET["paid"])): ?>
<div class="success">✅ Pago realizado correctamente. Tu plan ha sido actualizado.</div>
<?php endif; ?>

<div class="plans-grid">
    <?php foreach ($plans as $p): ?>
        <?php 
            $is_current = ($p['id'] == $current['plan_id']);
            $card_class = $is_current ? 'plan-card current-plan' : 'plan-card';
        ?>
        
        <div class="<?= $card_class ?>">
            <div class="header-plan">
                <h3><?= htmlspecialchars($p["name"]) ?></h3>
                <p style="margin: 5px 0; font-size: 0.9em; color: rgba(0,0,0,0.8);">
                    <?= htmlspecialchars($p["description"]) ?>
                </p>
            </div>

            <div class="features">
                <div class="price">
                    <?= number_format($p["price"], 0) ?>€
                    <small>/mes</small>
                </div>

                <p>Servidores permitidos: <strong><?= $p["max_servers"] ?></strong></p>
                <p>RAM máxima: <strong><?= $p["max_ram"] ?> GB</strong></p>

                <p>Mods: 
                    <strong style="color: <?= $p["allow_mods"] ? '#4CAF50' : '#F44336' ?>;">
                        <?= $p["allow_mods"] ? "✅ Sí" : "❌ No" ?>
                    </strong>
                </p>

                <p>Plugins: 
                    <strong style="color: <?= $p["allow_plugins"] ? '#4CAF50' : '#F44336' ?>;">
                        <?= $p["allow_plugins"] ? "✅ Sí" : "❌ No" ?>
                    </strong>
                </p>

                <p>Backups: 
                    <strong style="color: <?= $p["allow_backups"] ? '#4CAF50' : '#F44336' ?>;">
                        <?= $p["allow_backups"] ? "✅ Sí" : "❌ No" ?>
                    </strong>
                </p>

                <?php if ($p["name"] === "Enterprise"): ?>
                <p style="border-top:2px solid #333; margin-top:15px;">
                    Soporte Prioritario: <strong style="color:#4FC3F7;">24/7</strong>
                </p>
                <?php endif; ?>
            </div>

            <?php if ($is_current): ?>
                <span class="btn-action disabled">PLAN ACTUAL</span>
            <?php else: ?>
                <a class="btn-action" href="pagos.php?plan_id=<?= $p['id'] ?>">Actualizar / Comprar</a>
            <?php endif; ?>
        </div>

    <?php endforeach; ?>
</div>

<p style="margin-top: 50px;">
    <a href="panel.php" class="btn-action" style="background: #1E1E1E; border: 1px solid #FF9800;">
        ⬅ Volver al panel
    </a>
</p>

</div>

<footer>
    © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.
    <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include_once "../../comun/chatbot.php"; ?>
</body>
</html>
