<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) { 
    header("Location: ../../autenticacion/login.php"); 
    exit();
}

$user_id = $_SESSION["user_id"];

// Cargar usuario
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
$mis_puntos = $user["points"] ?? 0;

// Cargar recompensas
$rewards = mysqli_query($conn, "SELECT * FROM point_rewards ORDER BY cost ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reward_id"])) {
    $rid = intval($_POST["reward_id"]);

    $rw = mysqli_fetch_assoc(mysqli_query(
        $conn, "SELECT * FROM point_rewards WHERE id=$rid"
    ));

    if ($rw) {
        if ($mis_puntos >= $rw["cost"]) {

            // Descontar puntos
            mysqli_query($conn, "UPDATE users SET points = points - {$rw['cost']} WHERE id=$user_id");

            // Registrar en historial
            mysqli_query($conn, "
                INSERT INTO user_points_history (user_id, amount, reason)
                VALUES ($user_id, -{$rw['cost']}, 'Canjeó: {$rw['reward_name']}')
            ");

            // Aplicar recompensa
            if ($rw["reward_type"] === "ram") {

                mysqli_query($conn, "
                    INSERT INTO temporary_rewards (user_id, reward_type, value, expires_at)
                    VALUES ($user_id, 'ram_bonus', '{$rw['value']}', DATE_ADD(NOW(), INTERVAL 30 DAY))
                ");

            } elseif ($rw["reward_type"] === "premium_week") {

                mysqli_query($conn, "
                    INSERT INTO temporary_rewards (user_id, reward_type, value, expires_at)
                    VALUES ($user_id, 'premium_trial', '7', DATE_ADD(NOW(), INTERVAL 7 DAY))
                ");

            } elseif ($rw["reward_type"] === "credits") {

                mysqli_query($conn, "
                    INSERT INTO temporary_rewards (user_id, reward_type, value, expires_at)
                    VALUES ($user_id, 'credits', '{$rw['value']}', DATE_ADD(NOW(), INTERVAL 1 YEAR))
                ");
            }

            header("Location: canjear.php?ok=1");
            exit();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Canjear puntos</title>
<style>
body { background:#121212;color:white;font-family:'Segoe UI'; }
.container { max-width:900px;margin:40px auto;background:#1e1e1e;padding:20px;border-radius:12px; }
.reward { background:#222;padding:15px;border-radius:10px;margin:10px 0;display:flex; justify-content:space-between;align-items:center; }
.btn { background:#4FC3F7;color:#000;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;font-weight:bold; }
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">

<h2>🏅 Canjear Puntos</h2>
<p>Tienes <b style="color:#4FC3F7;"><?= $mis_puntos ?></b> puntos.</p>

<?php if (isset($_GET["ok"])): ?>
    <p style="color:#4FC3F7;">✅ Recompensa canjeada correctamente</p>
<?php endif; ?>

<?php while($rw = mysqli_fetch_assoc($rewards)): ?>
<div class="reward">
    <div>
        <b><?= $rw["reward_name"] ?></b><br>
        <small>Costo: <?= $rw["cost"] ?> pts</small>
    </div>
    <form method="POST">
        <input type="hidden" name="reward_id" value="<?= $rw["id"] ?>">
        <button class="btn" <?php if ($mis_puntos < $rw["cost"]) echo "disabled"; ?>>
            Canjear
        </button>
    </form>
</div>
<?php endwhile; ?>

<p><br><a href="perfil.php" class="btn">⬅ Volver</a></p>

</div>

</body>
</html>

</body>
</html>
