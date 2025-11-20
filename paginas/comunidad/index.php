<?php
session_start();
require_once "../../db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MineHostX Comunidad</title>
<link rel="icon" href="../../archivos/img/logo.png">
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: #111;
  color: #fff;
}

/* Header */
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
  background: #1E1E1E;
  box-shadow: 0 2px 10px rgba(0,0,0,0.4);
  position: sticky;
  top: 0;
  z-index: 1000;
}
.logo {
  color: #4FC3F7;
  font-weight: bold;
  font-size: 1.6em;
  text-decoration: none;
}
nav a {
  color: #fff;
  margin-left: 20px;
  text-decoration: none;
  transition: 0.3s;
}
nav a:hover {
  color: #4FC3F7;
}

/* Hero section */
.hero {
  text-align: center;
  padding: 100px 20px 60px;
  background: linear-gradient(180deg, #0d0d0d 0%, #1e1e1e 100%);
}
.hero h1 {
  font-size: 2.8em;
  color: #4FC3F7;
}
.hero p {
  max-width: 700px;
  margin: 10px auto;
  color: #ccc;
}

/* Cards */
.sections {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 25px;
  padding: 60px 20px;
}
.card {
  background: #191919;
  border-radius: 14px;
  width: 260px;
  padding: 25px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.4);
  transition: transform 0.3s ease;
}
.card:hover { transform: translateY(-6px); }
.card h3 {
  color: #4FC3F7;
  margin-bottom: 10px;
}
.card p {
  color: #aaa;
  font-size: 0.9em;
  margin-bottom: 15px;
}
.card a {
  display: inline-block;
  background: #4FC3F7;
  color: #000;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: bold;
  text-decoration: none;
  transition: 0.3s;
}
.card a:hover {
  background: #82DAFF;
}

/* Ranking */
.ranking {
  background: #1E1E1E;
  padding: 50px 20px;
  text-align: center;
}
.ranking h2 {
  color: #4FC3F7;
}
.ranking-table {
  max-width: 700px;
  margin: 20px auto;
  border-collapse: collapse;
  width: 100%;
}
.ranking-table th, .ranking-table td {
  padding: 12px;
  border-bottom: 1px solid #333;
}
.ranking-table th {
  color: #4FC3F7;
  text-align: left;
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

<header>
  <a href="index.php" class="logo">Comunidad MineHostX</a>
  <nav>
    <a href="foro.php">🗨️ Foro</a>
    <a href="servidores.php">🌍 Servidores públicos</a>
    <a href="ranking.php">🏅 Ranking</a>
    <a href="../usuario/eventos.php">📆 Eventos</a>
    <a href="../usuario/perfil.php">👤 Mi perfil</a>
  </nav>
</header>

<section class="hero">
  <h1>Bienvenido a la Comunidad MineHostX</h1>
  <p>Conecta con otros jugadores, comparte tus servidores y gana reputación.  
  Aquí empieza tu aventura social dentro del ecosistema MineHostX.</p>
</section>

<section class="sections">
  <div class="card">
    <h3>🗨️ Foro</h3>
    <p>Habla con otros usuarios, comparte ideas, plugins, y resuelve dudas técnicas.</p>
    <a href="foro.php">Entrar</a>
  </div>

  <div class="card">
    <h3>🌍 Servidores Públicos</h3>
    <p>Sube tu servidor para que otros puedan unirse, o busca nuevos mundos donde jugar.</p>
    <a href="servidores.php">Explorar</a>
  </div>

  <div class="card">
    <h3>🏅 Ranking</h3>
    <p>Los mejores creadores, servidores más votados y jugadores más activos.</p>
    <a href="ranking.php">Ver ranking</a>
  </div>

  <div class="card">
    <h3>💬 Mensajes</h3>
    <p>Conecta directamente con otros jugadores para pedir ayuda o formar equipos.</p>
    <a href="mensajes.php">Abrir chat</a>
  </div>
</section>

<section class="ranking">
  <h2>🏆 Top jugadores de la semana</h2>
  <table class="ranking-table">
    <tr>
      <th>#</th>
      <th>Usuario</th>
      <th>Puntos</th>
    </tr>
    <?php
    $top = mysqli_query($conn, "SELECT username, points FROM users ORDER BY points DESC LIMIT 5");
    $i = 1;
    while ($r = mysqli_fetch_assoc($top)):
    ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($r["username"]) ?></td>
        <td><?= $r["points"] ?> ⭐</td>
      </tr>
    <?php endwhile; ?>
  </table>
</section>

<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

</body>
</html>
