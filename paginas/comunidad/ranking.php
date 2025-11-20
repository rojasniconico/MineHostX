<?php
session_start();
require_once "../../db.php";

$user_id = $_SESSION["user_id"] ?? null;

// obtener servers con número de votos
$servers = mysqli_query($conn, "
    SELECT s.id, s.user_id, s.nombre, s.descripcion, s.ip, s.version, s.created_at,
           (SELECT COUNT(*) FROM server_votes WHERE server_id = s.id) AS votes
    FROM public_servers s
    JOIN users u ON u.id = s.user_id
    ORDER BY votes DESC, s.id ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🏆 Ranking de Servidores — MineHostX</title>
<style>
body{background:#0d0d0d;color:#fff;font-family:Segoe UI;margin:0;padding:0}
.container{max-width:1000px;margin:80px auto;padding:20px}
h2{text-align:center;color:#4FC3F7}
.card{background:#141414;margin:18px 0;padding:20px;border-radius:12px;
      display:flex;justify-content:space-between;align-items:center;border:1px solid #222;}
.left{max-width:70%}
.name{font-size:22px;font-weight:bold;color:#4FC3F7}
.meta{font-size:14px;color:#bbb;margin-top:6px;}
.vote-box{text-align:center}
.vote-btn{background:#4FC3F7;color:#000;border:none;padding:10px 16px;
          border-radius:8px;font-weight:bold;cursor:pointer;margin-top:8px}
.vote-btn:hover{background:#82daff}
.tag{padding:4px 10px;border-radius:6px;background:#222;color:#4FC3F7;margin-right:6px;font-size:12px}
</style>
</head>
<body>

<?php include "../../comun/navbar.php"; ?>

<div class="container">
<h2>🏆 Ranking de servidores</h2>

<?php if($servers && mysqli_num_rows($servers)): ?>
  <?php $pos = 1; while($s = mysqli_fetch_assoc($servers)): ?>
    <div class="card">
      <div class="left">
        <div class="name">#<?=$pos?> — <?=htmlspecialchars($s["nombre"])?></div>
        <div class="meta">

          🌐 IP: <b><?=$s["ip"]?></b><br>
          🎮 version: <b><?=htmlspecialchars($s["version"])?></b><br>

          Descripcion: <?=$s["descripcion"]?><br>

          </div>
	
      </div>

      <div class="vote-box">
        <div style="font-size:26px;font-weight:bold;color:#4FC3F7;"><?=$s["votes"]?> ⭐</div>
        
        <?php if($user_id): ?>
          <form method="POST" action="votar.php">
            <input type="hidden" name="server_id" value="<?=$s['id']?>">
            <button class="vote-btn">Votar ⭐</button>
          </form>
        <?php else: ?>
          <div style="font-size:13px;color:#aaa">Inicia sesión para votar</div>
        <?php endif; ?>
      </div>
    </div>
  <?php $pos++; endwhile; ?>
<?php else: ?>
  <p>No hay servidores públicos aún.</p>
<?php endif; ?>

</div>
</body>
</html>
